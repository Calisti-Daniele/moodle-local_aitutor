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

namespace local_aitutor\setup;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Health Check — Versione veloce della diagnostica per il banner.
 * Usa solo i check rapidi senza pingare il provider AI.
 * @package local_aitutor
 */
class health_check {
    /**
     * Restituisce true se il plugin è completamente configurato.
     */
    public static function is_ready(): bool {
        $diag   = new diagnostics();
        $report = $diag->run(quickcheck: true);
        return $report['ready'];
    }

    /**
     * Restituisce il numero di errori critici.
     */
    public static function error_count(): int {
        $diag   = new diagnostics();
        $report = $diag->run(quickcheck: true);
        return $report['errors_count'];
    }
}
