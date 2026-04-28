<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_aitutor\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_aitutor\ai\context_builder;
use local_aitutor\ai\provider_factory;

/**
 * Web API: send_message
 *
 * Flusso completo:
 * 1. Valida input e permessi
 * 2. Recupera la sessione dell'utente
 * 3. Costruisce il contesto (corsi, voti, scadenze, ecc.)
 * 4. Assembla la history della conversazione
 * 5. Chiama il provider AI
 * 6. Salva messaggio utente + risposta AI nel DB
 * 7. Restituisce la risposta al JavaScript
 * @package local_aitutor
 */
class send_message extends external_api {
    
    // PARAMETRI INPUT
    

    /**
     * Definisce i parametri accettati dalla funzione.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sessionid' => new external_value(
                PARAM_INT,
                'Session ID',
                VALUE_REQUIRED
            ),
            'message' => new external_value(
                PARAM_TEXT,
                'User message',
                VALUE_REQUIRED
            ),
        ]);
    }

    
    // RETURN VALUE
    

    /**
     * Definisce la struttura del valore restituito.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success'    => new external_value(PARAM_BOOL, 'Whether the call succeeded'),
            'content'    => new external_value(PARAM_RAW, 'AI response content', VALUE_OPTIONAL),
            'error'      => new external_value(PARAM_TEXT, 'Error message if any', VALUE_OPTIONAL),
            'tokens_in'  => new external_value(PARAM_INT, 'Input tokens used', VALUE_OPTIONAL),
            'tokens_out' => new external_value(PARAM_INT, 'Output tokens used', VALUE_OPTIONAL),
            'model'      => new external_value(PARAM_TEXT, 'Model used', VALUE_OPTIONAL),
        ]);
    }

    
    // EXECUTE
    

    /**
     * Esegue la chiamata AI e restituisce la risposta.
     *
     * @param int    $sessionid  ID sessione
     * @param string $message    Messaggio dell'utente
     * @return array
     */
    public static function execute(int $sessionid, string $message): array {
        global $USER, $DB;

        // 1. Valida parametri.
        $params = self::validate_parameters(
            self::execute_parameters(),
            ['sessionid' => $sessionid, 'message' => $message]
        );

        // 2. Verifica contesto e capability.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/aitutor:use', $context);

        // 3. Verifica che la sessione appartenga all'utente corrente.
        $session = $DB->get_record(
            'local_aitutor_sessions',
            ['id' => $params['sessionid'], 'userid' => $USER->id],
            '*',
            MUST_EXIST
        );

        // 4. Sanitize messaggio.
        $usermessage = clean_param(trim($params['message']), PARAM_TEXT);

        if (empty($usermessage)) {
            return [
                'success' => false,
                'error'   => get_string('error_empty_message', 'local_aitutor'),
            ];
        }

        if (mb_strlen($usermessage) > 4000) {
            $usermessage = mb_substr($usermessage, 0, 4000);
        }

        try {
            // 5. Costruisce il contesto completo dell'utente.
            $contextbuilder = new context_builder($USER);
            $systemprompt   = $contextbuilder->build_system_prompt();

            // 6. Recupera la history della conversazione.
            $history = local_aitutor_get_message_history(
                $session->id,
                20  // Ultimi 20 messaggi per il contesto
            );

            // Aggiunge il messaggio corrente alla history
            $history[] = [
                'role'    => 'user',
                'content' => $usermessage,
            ];

            // 7. Ottieni il provider AI configurato.
            $provider = provider_factory::get_provider();

            // 8. Opzioni dalla configurazione admin.
            $options = [
                'maxtokens'   => (int)(get_config('local_aitutor', 'maxtokens') ?: 1000),
                'temperature' => (float)(get_config('local_aitutor', 'temperature') ?: 0.7),
            ];

            // 9. Chiama l'AI.
            $airesponse = $provider->chat($history, $systemprompt, $options);

            // 10. Salva il messaggio utente nel DB.
            local_aitutor_save_message(
                $session->id,
                'user',
                $usermessage,
                $airesponse['tokens_in'] ?? 0
            );

            // 11. Salva la risposta AI nel DB.
            local_aitutor_save_message(
                $session->id,
                'assistant',
                $airesponse['content'],
                $airesponse['tokens_out'] ?? 0
            );

            // 12. Restituisce la risposta al JS.
            return [
                'success'    => true,
                'content'    => $airesponse['content'],
                'tokens_in'  => $airesponse['tokens_in'] ?? 0,
                'tokens_out' => $airesponse['tokens_out'] ?? 0,
                'model'      => $airesponse['model'] ?? '',
            ];
        } catch (\moodle_exception $e) {
            // Errori Moodle conosciuti (apikey mancante, rate limit, ecc.)
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            // Errori imprevisti — logga ma non esporre dettagli
            debugging(
                'local_aitutor send_message error: ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );

            return [
                'success' => false,
                'error'   => get_string('error_unavailable', 'local_aitutor'),
            ];
        }
    }
}
