<?php
// This file is part of Moodle - https://moodle.org/
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
use external_multiple_structure;
use external_value;

/**
 * Web API: get_history
 *
 * Recupera la storia dei messaggi di una sessione.
 * Utile per ricaricare la chat dopo un refresh della pagina.
 * @package local_aitutor
 */
class get_history extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sessionid' => new external_value(
                PARAM_INT,
                'Session ID',
                VALUE_REQUIRED
            ),
            'limit' => new external_value(
                PARAM_INT,
                'Max messages to retrieve',
                VALUE_DEFAULT,
                30
            ),
        ]);
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success'  => new external_value(PARAM_BOOL, 'Success'),
            'messages' => new external_multiple_structure(
                new external_single_structure([
                    'role'        => new external_value(PARAM_TEXT, 'user|assistant'),
                    'content'     => new external_value(PARAM_RAW, 'Message content'),
                    'timecreated' => new external_value(PARAM_INT, 'Timestamp'),
                ]),
                'Message list',
                VALUE_OPTIONAL
            ),
            'error' => new external_value(PARAM_TEXT, 'Error message', VALUE_OPTIONAL),
        ]);
    }

    public static function execute(int $sessionid, int $limit = 30): array {
        global $USER, $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['sessionid' => $sessionid, 'limit' => $limit]
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
            $records = $DB->get_records(
                'local_aitutor_messages',
                ['sessionid' => $session->id],
                'timecreated ASC',
                'role, message, timecreated',
                0,
                $params['limit']
            );

            $messages = array_values(array_map(fn($r) => [
                'role'        => $r->role,
                'content'     => format_text($r->message, FORMAT_MOODLE),
                'timecreated' => (int)$r->timecreated,
            ], $records));

            return [
                'success'  => true,
                'messages' => $messages,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'messages' => [],
            ];
        }
    }
}
