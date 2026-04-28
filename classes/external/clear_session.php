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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
namespace local_aitutor\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

/**
 * Web API: clear_session
 *
 * Cancella tutti i messaggi della sessione corrente
 * e crea una nuova sessione pulita per l'utente.
 */
class clear_session extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sessionid' => new external_value(
                PARAM_INT,
                'Session ID to clear',
                VALUE_REQUIRED
            ),
        ]);
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success'       => new external_value(PARAM_BOOL, 'Whether cleared successfully'),
            'new_sessionid' => new external_value(PARAM_INT,  'New session ID', VALUE_OPTIONAL),
            'error'         => new external_value(PARAM_TEXT, 'Error message',  VALUE_OPTIONAL),
        ]);
    }

    public static function execute(int $sessionid): array {
        global $USER, $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['sessionid' => $sessionid]
        );

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/aitutor:use', $context);

        // Verifica proprietà sessione
        $session = $DB->get_record(
            'local_aitutor_sessions',
            ['id' => $params['sessionid'], 'userid' => $USER->id],
            '*',
            MUST_EXIST
        );

        try {
            // Cancella tutti i messaggi della sessione
            $DB->delete_records(
                'local_aitutor_messages',
                ['sessionid' => $session->id]
            );

            // Cancella la sessione vecchia
            $DB->delete_records(
                'local_aitutor_sessions',
                ['id' => $session->id]
            );

            // Crea una sessione nuova e pulita
            $newsession = local_aitutor_get_or_create_session($USER->id);

            return [
                'success'       => true,
                'new_sessionid' => $newsession->id,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}