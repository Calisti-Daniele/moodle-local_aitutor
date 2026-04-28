<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');
require_once($CFG->dirroot . '/local/aitutor/lib.php');

global $PAGE, $OUTPUT, $CFG;

// Solo admin
require_login();
$context = context_system::instance();
require_capability('local/aitutor:manage', $context);

$PAGE->set_url('/local/aitutor/diagnostics.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('diagnostics_title', 'local_aitutor'));
$PAGE->set_heading(get_string('diagnostics_title', 'local_aitutor'));
$PAGE->set_pagelayout('admin');

// Carica AMD
$PAGE->requires->js_call_amd('local_aitutor/diagnostics', 'init');

echo $OUTPUT->header();

// Render pagina diagnostica
$diag   = new \local_aitutor\setup\diagnostics();
$report = $diag->run();

$templatedata = build_template_data($report);
echo $OUTPUT->render_from_template('local_aitutor/diagnostics', $templatedata);

echo $OUTPUT->footer();

/**
 * Prepara i dati per il template.
 */
function build_template_data(array $report): array {
    $checks = [];

    foreach ($report['checks'] as $check) {
        $checks[] = [
            'key'          => $check['key'],
            'label'        => $check['label'],
            'message'      => $check['message'],
            'fix'          => $check['fix'] ?? '',
            'icon'         => $check['icon'],
            'is_ok'        => $check['status'] === 'ok',
            'is_warning'   => $check['status'] === 'warning',
            'is_error'     => $check['status'] === 'error',
            'has_fix'      => !empty($check['fix']),
            'has_autofix'  => !empty($check['actions']),
            'autofix_key'  => $check['actions']['action']       ?? '',
            'autofix_label'=> $check['actions']['action_label'] ?? '',
        ];
    }

    return [
        'is_ready'        => $report['ready'],
        'is_error'        => $report['status'] === 'error',
        'is_warning'      => $report['status'] === 'warning',
        'summary'         => $report['summary'],
        'errors_count'    => $report['errors_count'],
        'warnings_count'  => $report['warnings_count'],
        'checks'          => $checks,
        'provider'        => ucfirst($report['provider']),
        'settings_url'    => (new moodle_url('/admin/settings.php',
            ['section' => 'local_aitutor']))->out(false),
        'str_rerun'       => get_string('diag_rerun',        'local_aitutor'),
        'str_settings'    => get_string('diag_goto_settings','local_aitutor'),
        'str_fix'         => get_string('diag_fix_automatically', 'local_aitutor'),
    ];
}