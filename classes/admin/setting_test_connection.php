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
 * Setting test Connection for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aitutor\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Custom admin setting — Bottone test connessione provider AI.
 *
 * Renderizza un bottone che, al click, chiama il provider
 * configurato e mostra l'esito inline nella pagina admin.
 * @package local_aitutor
 */
class setting_test_connection extends \admin_setting {
    /**
     *   construct.
     *
     * @param mixed $name
     * @param mixed $visiblename
     * @param mixed $description
     */
    public function __construct(string $name, string $visiblename, string $description) {
        parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Get setting.
     *
     * @return bool
     */
    public function get_setting(): bool {
        return true;
    }

    /**
     * Write setting.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function write_setting($data): string {
        return '';
    }

    /**
     * Output html.
     *
     * @return string
     */
    public function output_html($data, $query = ''): string {
        global $PAGE, $OUTPUT;

        // Carica il modulo AMD per il test
        $PAGE->requires->js_call_amd(
            'local_aitutor/admin_test',
            'init'
        );

        $html  = \html_writer::start_div('local-aitutor-test-wrap');

        // Bottone test
        $html .= \html_writer::tag(
            'button',
            get_string('settings_test_connection', 'local_aitutor'),
            [
            'type'  => 'button',
            'class' => 'btn btn-secondary',
            'id'    => 'aitutor-test-btn',
            ]
        );

        // Spinner (nascosto di default)
        $html .= \html_writer::span(
            '',
            'spinner-border spinner-border-sm ml-2 d-none',
            ['id' => 'aitutor-test-spinner', 'aria-hidden' => 'true']
        );

        // Risultato (nascosto di default)
        $html .= \html_writer::div(
            '',
            'mt-2 d-none',
            ['id' => 'aitutor-test-result']
        );

        $html .= \html_writer::end_div();

        return format_admin_setting(
            $this,
            $this->visiblename,
            $html,
            $this->description
        );
    }
}
