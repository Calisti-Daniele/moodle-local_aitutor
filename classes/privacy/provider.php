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

namespace local_aitutor\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider per local_aitutor.
 *
 * Implementa le interfacce richieste da Moodle per la conformità GDPR:
 * - Dichiarazione dei dati personali trattati
 * - Export dei dati dell'utente
 * - Cancellazione dei dati dell'utente
 *
 * Dati trattati:
 * - local_aitutor_sessions: sessioni di conversazione (userid)
 * - local_aitutor_messages: messaggi inviati e ricevuti
 * @package local_aitutor
 */
class provider implements
    // Dichiara quali dati personali tratta il plugin
    \core_privacy\local\metadata\provider,

    // Permette export dei dati utente
    \core_privacy\local\request\plugin\provider,

    // Permette operazioni su liste di utenti (per admin)
    \core_privacy\local\request\core_userlist_provider {
    
    // METADATA — Dichiara i dati trattati
    

    /**
     * Restituisce la lista dei dati personali trattati dal plugin.
     * Moodle usa questo per generare la privacy policy.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {

        // Tabella sessioni
        $collection->add_database_table(
            'local_aitutor_sessions',
            [
                'userid'       => 'privacy:metadata:local_aitutor_sessions:userid',
                'timecreated'  => 'privacy:metadata:timecreated',
                'timemodified' => 'privacy:metadata:timemodified',
                'tokencount'   => 'privacy:metadata:tokencount',
            ],
            'privacy:metadata:local_aitutor_sessions'
        );

        // Tabella messaggi
        $collection->add_database_table(
            'local_aitutor_messages',
            [
                'sessionid'   => 'privacy:metadata:local_aitutor_messages:sessionid',
                'role'        => 'privacy:metadata:local_aitutor_messages:role',
                'message'     => 'privacy:metadata:local_aitutor_messages:message',
                'tokencount'  => 'privacy:metadata:tokencount',
                'timecreated' => 'privacy:metadata:timecreated',
            ],
            'privacy:metadata:local_aitutor_messages'
        );

        // Dati inviati a provider AI esterni
        $collection->add_external_location_link(
            'ai_provider',
            [
                'message'     => 'privacy:metadata:ai_provider:message',
                'systemprompt' => 'privacy:metadata:ai_provider:systemprompt',
            ],
            'privacy:metadata:ai_provider'
        );

        return $collection;
    }

    
    // CONTEXTLIST — Trova i contesti con dati dell'utente
    

    /**
     * Restituisce i contesti che contengono dati dell'utente.
     * Per local plugin usiamo sempre CONTEXT_SYSTEM.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :contextlevel
                   AND EXISTS (
                       SELECT 1
                         FROM {local_aitutor_sessions} s
                        WHERE s.userid = :userid
                   )";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'userid'       => $userid,
        ]);

        return $contextlist;
    }

    
    // USERLIST — Lista utenti con dati in un contesto
    

    /**
     * Aggiunge gli utenti che hanno dati nel contesto dato.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = "SELECT userid FROM {local_aitutor_sessions}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    
    // EXPORT — Esporta i dati dell'utente
    

    /**
     * Esporta tutti i dati personali dell'utente.
     * Chiamato quando l'utente richiede l'export dei suoi dati (GDPR Art. 20).
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }

            // Recupera tutte le sessioni dell'utente
            $sessions = $DB->get_records(
                'local_aitutor_sessions',
                ['userid' => $user->id],
                'timecreated ASC'
            );

            if (empty($sessions)) {
                continue;
            }

            $exportdata = [];

            foreach ($sessions as $session) {
                // Recupera i messaggi di questa sessione
                $messages = $DB->get_records(
                    'local_aitutor_messages',
                    ['sessionid' => $session->id],
                    'timecreated ASC'
                );

                $exportmessages = array_values(array_map(fn($m) => [
                    'role'        => $m->role,
                    'message'     => $m->message,
                    'tokens'      => $m->tokencount,
                    'timecreated' => \core_privacy\local\request\transform::datetime(
                        $m->timecreated
                    ),
                ], $messages));

                $exportdata[] = [
                    'session_id'    => $session->id,
                    'started'       => \core_privacy\local\request\transform::datetime(
                        $session->timecreated
                    ),
                    'last_activity' => \core_privacy\local\request\transform::datetime(
                        $session->timemodified
                    ),
                    'total_tokens'  => $session->tokencount,
                    'messages'      => $exportmessages,
                ];
            }

            // Scrivi i dati nel file di export
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_aitutor')],
                (object)[
                    'sessions' => $exportdata,
                    'total_sessions' => count($exportdata),
                ]
            );
        }
    }

    
    // DELETE — Cancella i dati dell'utente
    

    /**
     * Cancella tutti i dati in un contesto.
     * Chiamato raramente — preferire delete_data_for_user.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(
        \context $context
    ): void {
        global $DB;

        if (!$context instanceof \context_system) {
            return;
        }

        // Recupera tutte le sessioni
        $sessions = $DB->get_records(
            'local_aitutor_sessions',
            [],
            '',
            'id'
        );

        if (empty($sessions)) {
            return;
        }

        $sessionids = array_keys($sessions);

        // Cancella messaggi
        [$insql, $params] = $DB->get_in_or_equal($sessionids);
        $DB->delete_records_select(
            'local_aitutor_messages',
            "sessionid {$insql}",
            $params
        );

        // Cancella sessioni
        $DB->delete_records('local_aitutor_sessions');
    }

    /**
     * Cancella i dati di un utente specifico.
     * Chiamato quando l'utente esercita il diritto all'oblio (GDPR Art. 17).
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(
        approved_contextlist $contextlist
    ): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }

            self::delete_user_data($user->id, $DB);
        }
    }

    /**
     * Cancella i dati di una lista di utenti in un contesto.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(
        approved_userlist $userlist
    ): void {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            self::delete_user_data($userid, $DB);
        }
    }

    
    // HELPER PRIVATI
    

    /**
     * Cancella tutti i dati AI di un utente specifico.
     *
     * @param int      $userid
     * @param \moodle_database $DB
     */
    private static function delete_user_data(
        int $userid,
        \moodle_database $DB
    ): void {
        // Recupera le sessioni dell'utente
        $sessions = $DB->get_records(
            'local_aitutor_sessions',
            ['userid' => $userid],
            '',
            'id'
        );

        if (empty($sessions)) {
            return;
        }

        $sessionids = array_keys($sessions);

        // Cancella i messaggi
        [$insql, $params] = $DB->get_in_or_equal($sessionids);
        $DB->delete_records_select(
            'local_aitutor_messages',
            "sessionid {$insql}",
            $params
        );

        // Cancella le sessioni
        $DB->delete_records(
            'local_aitutor_sessions',
            ['userid' => $userid]
        );
    }
}
