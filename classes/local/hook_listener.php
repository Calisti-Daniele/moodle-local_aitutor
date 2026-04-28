<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_aitutor\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/aitutor/lib.php');
/**
 * Hook listener per local_aitutor.
 *
 * Viene chiamato automaticamente da Moodle su ogni pagina.
 * Decide se iniettare il widget e lo renderizza.
 */
class hook_listener {

    /**
     * Inietta il widget AI floating in ogni pagina.
     * Chiamato dal hook before_footer_html_generation.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function inject_widget(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        global $USER, $PAGE, $OUTPUT;

        // Non iniettare se:
        // 1. Utente non loggato
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // 2. Plugin disabilitato dall'admin
        if (!get_config('local_aitutor', 'enabled')) {
            return;
        }


        // 3. Utente non ha la capability
        $context = \context_system::instance();
        if (!has_capability('local/aitutor:use', $context)) {
            return;
        }

        // 4. Pagine di login/logout/setup — non iniettare
        $excluded = [
            'login', 'login/index', 'login/logout',
            'admin/index', 'admin/upgrade',
        ];
        $currenturl = $PAGE->url->get_path();
        foreach ($excluded as $path) {
            if (str_contains($currenturl, $path)) {
                return;
            }
        }

        // Recupera o crea la sessione per questo utente
        $session = local_aitutor_get_or_create_session($USER->id);

        // Recupera gli ultimi messaggi (per ripristinare la chat)
        $messages = local_aitutor_get_message_history($session->id, 30);

        // Prepara i messaggi per il template
        $renderedmessages = array_map(fn($msg) => [
            'role'         => $msg['role'],
            'content'      => format_text($msg['content'], FORMAT_MOODLE),
            'is_user'      => $msg['role'] === 'user',
            'is_assistant' => $msg['role'] === 'assistant',
        ], $messages);

        // Suggerimenti domande iniziali
        $suggestions = [
            get_string('suggestion_courses',      'local_aitutor'),
            get_string('suggestion_progress',     'local_aitutor'),
            get_string('suggestion_deadlines',    'local_aitutor'),
            get_string('suggestion_grades',       'local_aitutor'),
            get_string('suggestion_certificates', 'local_aitutor'),
        ];

        // Dati per il template
        $data = [
            'sessionid'       => $session->id,
            'userid'          => $USER->id,
            'sesskey'         => sesskey(),
            'wwwroot'         => (new \moodle_url('/'))->out(false),

            // Messaggi
            'messages'        => array_values($renderedmessages),
            'has_messages'    => !empty($renderedmessages),

            // Messaggio di benvenuto
            'welcome_message' => empty($messages)
                ? get_string('widget_welcome', 'local_aitutor',
                    (object)['firstname' => $USER->firstname])
                : null,

            // Suggerimenti
            'suggestions'     => empty($messages)
                ? array_map(fn($s) => ['text' => $s], $suggestions)
                : [],

            // Stringhe UI
            'str_title'       => get_string('widget_title',       'local_aitutor'),
            'str_placeholder' => get_string('widget_placeholder', 'local_aitutor'),
            'str_send'        => get_string('widget_send',        'local_aitutor'),
            'str_thinking'    => get_string('widget_thinking',    'local_aitutor'),
            'str_clear'       => get_string('widget_clear',       'local_aitutor'),
            'str_clear_confirm' => get_string('widget_clear_confirm', 'local_aitutor'),
            'str_fullscreen'  => get_string('widget_fullscreen',  'local_aitutor'),
            'str_open'        => get_string('widget_open',        'local_aitutor'),
            'str_close'       => get_string('widget_close',       'local_aitutor'),
            'str_error'       => get_string('widget_error',       'local_aitutor'),
        ];

        // Renderizza il template
        $html = $OUTPUT->render_from_template('local_aitutor/widget', $data);

        // Inietta nella pagina
        $hook->add_html($html);

        // Carica il modulo AMD
        $PAGE->requires->js_call_amd('local_aitutor/widget', 'init', [[
            'sessionid' => $session->id,
            'userid'    => $USER->id,
            'wwwroot'   => (new \moodle_url('/'))->out(false),
            'sesskey'   => sesskey(),
        ]]);
    }
}