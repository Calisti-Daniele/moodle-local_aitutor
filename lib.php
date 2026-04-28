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

defined('MOODLE_INTERNAL') || die();

/**
 * Restituisce la lista delle modalità disponibili.
 *
 * @return string[]
 * @package local_aitutor
 */
function local_aitutor_get_modes(): array {
    return ['free', 'socratic', 'explainer', 'quizprep', 'feedback'];
}

/**
 * Recupera o crea una sessione attiva per l'utente corrente.
 * In local plugin la sessione è globale — non legata a un corso.
 *
 * @param int $userid  ID utente
 * @return stdClass    Record della sessione
 * @package local_aitutor
 */
function local_aitutor_get_or_create_session(int $userid): stdClass {
    global $DB;

    // Cerca sessione esistente (ultima aperta)
    $session = $DB->get_record_sql(
        'SELECT * FROM {local_aitutor_sessions}
          WHERE userid = :uid
          ORDER BY timemodified DESC
          LIMIT 1',
        ['uid' => $userid]
    );

    if (!$session) {
        $session = (object)[
            'userid'       => $userid,
            'timecreated'  => time(),
            'timemodified' => time(),
            'tokencount'   => 0,
        ];
        $session->id = $DB->insert_record('local_aitutor_sessions', $session);
    }

    return $session;
}

/**
 * Recupera la history dei messaggi di una sessione.
 *
 * @param int $sessionid
 * @param int $limit  Numero massimo di messaggi
 * @return array  [['role' => 'user', 'content' => '...'], ...]
 * @package local_aitutor
 */
function local_aitutor_get_message_history(int $sessionid, int $limit = 20): array {
    global $DB;

    $messages = $DB->get_records(
        'local_aitutor_messages',
        ['sessionid' => $sessionid],
        'timecreated ASC',
        'role, message',
        0,
        $limit
    );

    return array_values(array_map(fn($m) => [
        'role'    => $m->role,
        'content' => $m->message,
    ], $messages));
}

/**
 * Salva un messaggio nella sessione e aggiorna il token count.
 *
 * @param int    $sessionid
 * @param string $role      'user' | 'assistant'
 * @param string $content
 * @param int    $tokens
 * @return int  ID del messaggio salvato
 * @package local_aitutor
 */
function local_aitutor_save_message(
    int $sessionid,
    string $role,
    string $content,
    int $tokens = 0
): int {
    global $DB;

    $message = (object)[
        'sessionid'   => $sessionid,
        'role'        => $role,
        'message'     => $content,
        'tokencount'  => $tokens,
        'timecreated' => time(),
    ];
    $msgid = $DB->insert_record('local_aitutor_messages', $message);

    $DB->execute(
        'UPDATE {local_aitutor_sessions}
            SET tokencount   = tokencount + :tokens,
                timemodified = :now
          WHERE id = :id',
        ['tokens' => $tokens, 'now' => time(), 'id' => $sessionid]
    );

    return $msgid;
}

/**
 * Hook chiamato dopo l'installazione o aggiornamento del plugin.
 * Registra automaticamente i servizi web.
 * @package local_aitutor
 */
function local_aitutor_after_install(): void {
    global $CFG;
    require_once($CFG->dirroot . '/lib/upgradelib.php');
    external_update_descriptions('local_aitutor');
}

/**
 * Hook chiamato dopo ogni upgrade del plugin.
 * @package local_aitutor
 */
function local_aitutor_after_upgrade(): void {
    global $CFG;
    require_once($CFG->dirroot . '/lib/upgradelib.php');
    external_update_descriptions('local_aitutor');
}
