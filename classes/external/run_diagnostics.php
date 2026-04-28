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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
namespace local_aitutor\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/aitutor/lib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use local_aitutor\setup\diagnostics;

/**
 * Web API: run_diagnostics
 * Esegue la diagnostica completa e restituisce il report.
 */
class run_diagnostics extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'quickcheck' => new external_value(
                PARAM_BOOL,
                'Skip slow checks (AI ping)',
                VALUE_DEFAULT,
                false
            ),
            'fix' => new external_value(
                PARAM_ALPHANUMEXT,  // ← accetta lettere, numeri, underscore, trattini
                'Auto-fix action',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status'         => new external_value(PARAM_TEXT, 'Global status'),
            'ready'          => new external_value(PARAM_BOOL, 'Plugin ready'),
            'summary'        => new external_value(PARAM_TEXT, 'Summary message'),
            'errors_count'   => new external_value(PARAM_INT,  'Error count'),
            'warnings_count' => new external_value(PARAM_INT,  'Warning count'),
            'checks'         => new external_multiple_structure(
                new external_single_structure([
                    'key'     => new external_value(PARAM_TEXT, 'Check key'),
                    'label'   => new external_value(PARAM_TEXT, 'Check label'),
                    'status'  => new external_value(PARAM_TEXT, 'Check status'),
                    'message' => new external_value(PARAM_TEXT, 'Check message'),
                    'fix'     => new external_value(PARAM_TEXT, 'Fix instruction',
                        VALUE_OPTIONAL),
                    'icon'    => new external_value(PARAM_TEXT, 'Status icon'),
                ])
            ),
        ]);
    }

    public static function execute(
        bool   $quickcheck = false,
        string $fix        = ''
    ): array {
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/aitutor:manage', $context);

        $diag = new diagnostics();

        // Esegui un fix automatico se richiesto
        if (!empty($fix)) {
            $fixmethod = 'fix_' . $fix;
            if (method_exists($diag, $fixmethod)) {
                $diag->$fixmethod();
            }
        }

        // Esegui la diagnostica
        $report = $diag->run($quickcheck);

        // Normalizza i checks per l'external API
        $checks = [];
        foreach ($report['checks'] as $check) {
            $checks[] = [
                'key'     => $check['key'],
                'label'   => $check['label'],
                'status'  => $check['status'],
                'message' => $check['message'],
                'fix'     => $check['fix'] ?? '',
                'icon'    => $check['icon'],
            ];
        }

        return [
            'status'         => $report['status'],
            'ready'          => $report['ready'],
            'summary'        => $report['summary'],
            'errors_count'   => $report['errors_count'],
            'warnings_count' => $report['warnings_count'],
            'checks'         => $checks,
        ];
    }
}