<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/aitutor/lib.php');

// Globals Moodle — necessario per Intelephense
global $DB, $PAGE, $USER, $OUTPUT, $CFG;

// =========================================================================
// SETUP MOODLE
// =========================================================================

$id        = optional_param('id', 0, PARAM_INT);
$aitutorid = optional_param('a', 0, PARAM_INT);

if ($id) {
    $cm      = get_coursemodule_from_id('aitutor', $id, 0, false, MUST_EXIST);
    $course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $aitutor = $DB->get_record('aitutor', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($aitutorid) {
    $aitutor = $DB->get_record('aitutor', ['id' => $aitutorid], '*', MUST_EXIST);
    $course  = $DB->get_record('course', ['id' => $aitutor->course], '*', MUST_EXIST);
    $cm      = get_coursemodule_from_instance('aitutor', $aitutor->id, $course->id, false, MUST_EXIST);
} else {
    throw new \moodle_exception('invalidcoursemodule');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/aitutor:view', $context);

// =========================================================================
// COMPLETION TRACKING
// =========================================================================
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// =========================================================================
// SESSIONE STUDENTE
// =========================================================================
$session  = aitutor_get_or_create_session($aitutor->id, $USER->id);
$messages = aitutor_get_message_history($session->id, 50);

// =========================================================================
// SETUP PAGINA
// =========================================================================
$PAGE->set_url('/mod/aitutor/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($aitutor->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->js_call_amd('mod_aitutor/chat', 'init', [[
    'cmid'      => $cm->id,
    'sessionid' => $session->id,
    'userid'    => $USER->id,
    'wwwroot'   => $CFG->wwwroot,
]]);

// =========================================================================
// RENDERING
// =========================================================================
echo $OUTPUT->header();

// Activity information — API corretta per Moodle 4.3+
$cminfo            = \cm_info::create($cm);
$completiondetails = \core_completion\cm_completion_details::get_instance(
    $cminfo,
    $USER->id
);
$activitydates = \core\activity_dates::get_dates_for_module($cminfo, $USER->id);
echo $OUTPUT->activity_information($cminfo, $completiondetails, $activitydates);

// Renderer con tipo esplicito per Intelephense
/** @var \mod_aitutor\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_aitutor');
echo $renderer->render_chat($aitutor, $course, $USER, $context, $session, $messages);

echo $OUTPUT->footer();