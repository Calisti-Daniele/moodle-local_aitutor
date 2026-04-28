<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Hook callbacks per local_aitutor.
 *
 * Moodle 4.3+ usa il nuovo sistema di hooks basato su classi PHP.
 * Qui registriamo il listener che inietta il widget in ogni pagina.
 *
 * @see https://moodledev.io/docs/4.3/apis/core/hooks
 */

$callbacks = [
    [
        // Hook che si attiva PRIMA che venga generato l'output HTML
        // della pagina — il momento perfetto per iniettare JS e CSS
        'hook'     => \core\hook\output\before_footer_html_generation::class,
        'callback' => \local_aitutor\local\hook_listener::class . '::inject_widget',
        'priority' => 100,
    ],
];