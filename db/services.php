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

/**
 * External service definitions for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [

    'local_aitutor_send_message' => [
        'classname'   => 'local_aitutor\external\send_message',
        'methodname'  => 'execute',
        'description' => 'Send a message to the AI assistant and get a response.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
        'capabilities'  => 'local/aitutor:use',
    ],

    'local_aitutor_clear_session' => [
        'classname'   => 'local_aitutor\external\clear_session',
        'methodname'  => 'execute',
        'description' => 'Clear the current AI assistant conversation session.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
        'capabilities'  => 'local/aitutor:use',
    ],

    'local_aitutor_get_history' => [
        'classname'   => 'local_aitutor\external\get_history',
        'methodname'  => 'execute',
        'description' => 'Get the message history for the current session.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'capabilities'  => 'local/aitutor:use',
    ],

    'local_aitutor_test_connection' => [
        'classname'   => 'local_aitutor\external\test_connection',
        'methodname'  => 'execute',
        'description' => 'Test the connection to the configured AI provider.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
        'capabilities'  => 'local/aitutor:manage',
    ],

    // ─── Diagnostica automatica ───────────────────────────────
    'local_aitutor_run_diagnostics' => [
        'classname'     => 'local_aitutor\external\run_diagnostics',
        'methodname'    => 'execute',
        'description'   => 'Run automatic diagnostics and optionally apply fixes.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/aitutor:manage',
    ],
];
