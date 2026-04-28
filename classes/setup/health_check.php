<?php
namespace local_aitutor\setup;

defined('MOODLE_INTERNAL') || die();

/**
 * Health Check — Versione veloce della diagnostica per il banner.
 * Usa solo i check rapidi senza pingare il provider AI.
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