<?php
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