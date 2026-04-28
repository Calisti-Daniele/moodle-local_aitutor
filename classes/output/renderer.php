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

namespace mod_aitutor\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer principale di mod_aitutor.
 * Si occupa di preparare i dati e renderizzare i template Mustache.
 * @package local_aitutor
 */
class renderer extends \plugin_renderer_base {
    /**
     * Renderizza la pagina chat principale.
     *
     * @param \stdClass  $aitutor   Istanza del modulo
     * @param \stdClass  $course    Corso
     * @param \stdClass  $user      Utente corrente
     * @param \context   $context   Contesto modulo  ← cambia qui nel PHPDoc
     * @param \stdClass  $session   Sessione corrente
     * @param array      $messages  Storia messaggi
     * @return string HTML renderizzato
     */
    public function render_chat(
        \stdClass $aitutor,
        \stdClass $course,
        \stdClass $user,
        \context_module $context,
        \stdClass $session,
        array $messages
    ): string {

        // Prepara i messaggi per il template
        $renderedmessages = array_map(fn($msg) => [
            'role'       => $msg['role'],
            'content'    => format_text($msg['content'], FORMAT_MOODLE),
            'is_user'    => $msg['role'] === 'user',
            'is_assistant' => $msg['role'] === 'assistant',
            'avatar'     => $msg['role'] === 'user'
                            ? $this->user_picture_url($user)
                            : null,
        ], $messages);

        // Messaggio di benvenuto se la chat è vuota
        $welcomemessage = empty($messages)
            ? get_string('chat_welcome', 'aitutor', (object)['firstname' => $user->firstname])
            : null;

        // Dati per il template
        $data = [
            // Identità
            'aitutor_id'      => $aitutor->id,
            'session_id'      => $session->id,
            'cmid'            => $context->instanceid,

            // UI
            'activity_name'   => format_string($aitutor->name),
            'mode'            => $aitutor->mode,
            'mode_label'      => get_string('mode_' . $aitutor->mode, 'aitutor'),

            // Chat
            'messages'        => array_values($renderedmessages),
            'has_messages'    => !empty($renderedmessages),
            'welcome_message' => $welcomemessage,

            // Stringhe UI
            'str_placeholder' => get_string('chat_placeholder', 'aitutor'),
            'str_send'        => get_string('chat_send', 'aitutor'),
            'str_thinking'    => get_string('chat_thinking', 'aitutor'),
            'str_clear'       => get_string('chat_clear', 'aitutor'),
            'str_clear_confirm' => get_string('chat_clear_confirm', 'aitutor'),
            'str_error'       => get_string('chat_error', 'aitutor'),

            // Capabilities
            'can_clear'       => has_capability('mod/aitutor:view', $context),
            'can_viewreports' => has_capability('mod/aitutor:viewreports', $context),

            // Token info (visibile solo ai docenti)
            'show_token_info' => has_capability('mod/aitutor:viewreports', $context),
            'token_count'     => $session->tokencount ?? 0,

            // Sessinfo per JS
            'sesskey'         => sesskey(),
        ];

        return $this->render_from_template('mod_aitutor/chat', $data);
    }

    /**
     * Restituisce l'URL della foto profilo dell'utente.
     */
    private function user_picture_url(\stdClass $user): string {
        global $PAGE;
        $userpicture = new \user_picture($user);
        $userpicture->size = 35;
        return $userpicture->get_url($PAGE)->out(false);
    }
}
