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

namespace local_aitutor\setup;

defined('MOODLE_INTERNAL') || die();

/**
 * Standalone diagnostics page for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Diagnostics — Motore di diagnostica automatica.
 *
 * Controlla automaticamente tutto quello che serve per far
 * funzionare il plugin e restituisce un report dettagliato
 * con lo stato di ogni componente e le istruzioni per risolvere
 * eventuali problemi.
 *
 * Usato da:
 * - Il banner nelle impostazioni admin
 * - Il wizard di setup
 * - L'endpoint AJAX di health check
 * @package local_aitutor
 */
class diagnostics {
    /** @var array Risultati dei check */
    private array $results = [];

    /** @var string Provider configurato */
    private string $provider;

    /** @var string Stati possibili per ogni check */
    const STATUS_OK      = 'ok';
    /** @var string Stati possibili per ogni check */
    const STATUS_WARNING = 'warning';
    /** @var string Stati possibili per ogni check */
    const STATUS_ERROR   = 'error';
    /** @var string Stati possibili per ogni check */
    const STATUS_INFO    = 'info';

    /**
     *   construct.
     */
    public function __construct() {
        $this->provider = get_config('local_aitutor', 'provider') ?: 'ollama';
    }


    // ENTRY POINT — Esegui tutti i check


    /**
     * Esegue tutti i check e restituisce il report completo.
     *
     * @param bool $quickcheck  Se true, salta i check lenti (es. ping AI)
     * @return array  Report completo con stato globale e dettagli
     */
    public function run(bool $quickcheck = false): array {
        $this->results = [];

        // Check di sistema.
        $this->check_php_version();
        $this->check_php_extensions();
        $this->check_plugin_enabled();
        $this->check_capabilities();
        $this->check_services_registered();

        // Check provider.
        $this->check_provider_configured();

        if (!$quickcheck) {
            $this->check_provider_connection();
        }

        // Check Moodle security (solo Ollama).
        if ($this->provider === 'ollama') {
            $this->check_moodle_http_security();
        }

        // Calcola stato globale.
        $globalstatus = $this->calculate_global_status();

        return [
            'status'          => $globalstatus,
            'checks'          => $this->results,
            'provider'        => $this->provider,
            'ready'           => $globalstatus === self::STATUS_OK,
            'errors_count'    => $this->count_by_status(self::STATUS_ERROR),
            'warnings_count'  => $this->count_by_status(self::STATUS_WARNING),
            'summary'         => $this->build_summary($globalstatus),
        ];
    }


    // CHECK 1 — Versione PHP


    /**
     * Check php version.
     */
    private function check_php_version(): void {
        $required = '8.1.0';
        $current  = PHP_VERSION;
        $ok       = version_compare($current, $required, '>=');

        $this->add_result(
            'php_version',
            get_string('diag_php_version', 'local_aitutor'),
            $ok ? self::STATUS_OK : self::STATUS_ERROR,
            $ok
            ? get_string(
                'diag_php_version_ok',
                'local_aitutor',
                (object)['version' => $current]
            )
            : get_string(
                'diag_php_version_error',
                'local_aitutor',
                (object)['current' => $current, 'required' => $required]
            ),
            $ok ? null : get_string(
                'diag_php_version_fix',
                'local_aitutor',
                (object)['required' => $required]
            )
        );
    }


    // CHECK 2 — Estensioni PHP


    /**
     * Check php extensions.
     */
    private function check_php_extensions(): void {
        $required = ['curl', 'json', 'mbstring', 'openssl'];
        $missing  = array_filter($required, fn($ext) => !extension_loaded($ext));

        if (empty($missing)) {
            $this->add_result(
                'php_extensions',
                get_string('diag_php_extensions', 'local_aitutor'),
                self::STATUS_OK,
                get_string('diag_php_extensions_ok', 'local_aitutor')
            );
        } else {
            $this->add_result(
                'php_extensions',
                get_string('diag_php_extensions', 'local_aitutor'),
                self::STATUS_ERROR,
                get_string(
                    'diag_php_extensions_error',
                    'local_aitutor',
                    (object)['missing' => implode(', ', $missing)]
                ),
                get_string(
                    'diag_php_extensions_fix',
                    'local_aitutor',
                    (object)['missing' => implode(' ', $missing)]
                )
            );
        }
    }


    // CHECK 3 — Plugin abilitato


    /**
     * Check plugin enabled.
     */
    private function check_plugin_enabled(): void {
        $enabled = (bool)get_config('local_aitutor', 'enabled');

        $this->add_result(
            'plugin_enabled',
            get_string('diag_plugin_enabled', 'local_aitutor'),
            $enabled ? self::STATUS_OK : self::STATUS_WARNING,
            $enabled
            ? get_string('diag_plugin_enabled_ok', 'local_aitutor')
            : get_string('diag_plugin_enabled_warning', 'local_aitutor'),
            $enabled ? null
            : get_string('diag_plugin_enabled_fix', 'local_aitutor')
        );
    }


    // CHECK 4 — Capabilities


    /**
     * Check capabilities.
     */
    private function check_capabilities(): void {
        global $DB;

        // Controlla se il ruolo student ha la capability
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $hasdefault  = false;
        $count       = 0;

        if ($studentrole) {
            $hasdefault = $DB->record_exists('role_capabilities', [
                'roleid'     => $studentrole->id,
                'capability' => 'local/aitutor:use',
                'permission' => CAP_ALLOW,
            ]);
        }

        // Conta gli utenti con la capability in modo sicuro
        try {
            $sql = "SELECT COUNT(DISTINCT ra.userid)
                    FROM {role_assignments} ra
                    JOIN {role_capabilities} rc ON rc.roleid = ra.roleid
                    WHERE rc.capability = :capability
                    AND rc.permission = :permission";

            $count = $DB->count_records_sql($sql, [
                'capability' => 'local/aitutor:use',
                'permission' => CAP_ALLOW,
            ]);
        } catch (\Exception $e) {
            $count = 0;
        }

        if ($hasdefault) {
            $this->add_result(
                'capabilities',
                get_string('diag_capabilities', 'local_aitutor'),
                self::STATUS_OK,
                get_string(
                    'diag_capabilities_ok',
                    'local_aitutor',
                    (object)['count' => $count]
                )
            );
        } else {
            $this->add_result(
                'capabilities',
                get_string('diag_capabilities', 'local_aitutor'),
                self::STATUS_WARNING,
                get_string('diag_capabilities_warning', 'local_aitutor'),
                get_string('diag_capabilities_fix', 'local_aitutor')
            );
        }
    }


    // CHECK 5 — Servizi web registrati


    /**
     * Check services registered.
     */
    private function check_services_registered(): void {
        global $DB;

        $expected = [
        'local_aitutor_send_message',
        'local_aitutor_clear_session',
        'local_aitutor_get_history',
        'local_aitutor_test_connection',
        ];

        $found   = [];
        $missing = [];

        foreach ($expected as $fname) {
            if ($DB->record_exists('external_functions', ['name' => $fname])) {
                $found[] = $fname;
            } else {
                $missing[] = $fname;
            }
        }

        if (empty($missing)) {
            $this->add_result(
                'services',
                get_string('diag_services', 'local_aitutor'),
                self::STATUS_OK,
                get_string(
                    'diag_services_ok',
                    'local_aitutor',
                    (object)['count' => count($found)]
                )
            );
        } else {
            $this->add_result(
                'services',
                get_string('diag_services', 'local_aitutor'),
                self::STATUS_ERROR,
                get_string(
                    'diag_services_error',
                    'local_aitutor',
                    (object)['missing' => implode(', ', $missing)]
                ),
                get_string('diag_services_fix', 'local_aitutor')
            );
        }
    }


    // CHECK 6 — Provider configurato


    /**
     * Check provider configured.
     */
    private function check_provider_configured(): void {
        $provider = $this->provider;
        $issues   = [];

        switch ($provider) {
            case 'ollama':
                $url = get_config('local_aitutor', 'ollama_url');
                if (empty($url)) {
                    $issues[] = get_string('diag_provider_no_url', 'local_aitutor');
                }
                $model = get_config('local_aitutor', 'ollama_model');
                if (empty($model)) {
                    $issues[] = get_string('diag_provider_no_model', 'local_aitutor');
                }
                break;

            case 'openai':
                $key = get_config('local_aitutor', 'openai_apikey');
                if (empty($key)) {
                    $issues[] = get_string('diag_provider_no_apikey', 'local_aitutor');
                } else if (!str_starts_with($key, 'sk-')) {
                    $issues[] = get_string('diag_provider_invalid_apikey', 'local_aitutor');
                }
                break;

            case 'anthropic':
                $key = get_config('local_aitutor', 'anthropic_apikey');
                if (empty($key)) {
                    $issues[] = get_string('diag_provider_no_apikey', 'local_aitutor');
                } else if (!str_starts_with($key, 'sk-ant-')) {
                    $issues[] = get_string('diag_provider_invalid_apikey', 'local_aitutor');
                }
                break;
        }

        if (empty($issues)) {
            $this->add_result(
                'provider_config',
                get_string('diag_provider_config', 'local_aitutor'),
                self::STATUS_OK,
                get_string(
                    'diag_provider_config_ok',
                    'local_aitutor',
                    (object)['provider' => ucfirst($provider)]
                )
            );
        } else {
            $this->add_result(
                'provider_config',
                get_string('diag_provider_config', 'local_aitutor'),
                self::STATUS_ERROR,
                implode(' ', $issues),
                get_string('diag_provider_config_fix', 'local_aitutor')
            );
        }
    }


    // CHECK 7 — Connessione al provider


    /**
     * Check provider connection.
     */
    private function check_provider_connection(): void {
        try {
            $factory  = new \local_aitutor\ai\provider_factory();
            $provider = $factory::get_provider($this->provider);
            $result   = $provider->test_connection();

            if ($result['success']) {
                $models = $result['models'] ?? [];
                $this->add_result(
                    'provider_connection',
                    get_string('diag_provider_connection', 'local_aitutor'),
                    self::STATUS_OK,
                    get_string(
                        'diag_provider_connection_ok',
                        'local_aitutor',
                        (object)[
                            'provider' => ucfirst($this->provider),
                            'models'   => implode(', ', array_slice($models, 0, 3)),
                        ]
                    )
                );
            } else {
                $this->add_result(
                    'provider_connection',
                    get_string('diag_provider_connection', 'local_aitutor'),
                    self::STATUS_ERROR,
                    $result['message'],
                    $this->get_connection_fix($result['message'])
                );
            }
        } catch (\Throwable $e) {
            $this->add_result(
                'provider_connection',
                get_string('diag_provider_connection', 'local_aitutor'),
                self::STATUS_ERROR,
                $e->getMessage(),
                $this->get_connection_fix($e->getMessage())
            );
        }
    }


    // CHECK 8 — Moodle HTTP Security (solo Ollama)


    /**
     * Check moodle http security.
     */
    private function check_moodle_http_security(): void {
        $ollamaurl = get_config('local_aitutor', 'ollama_url')
                ?: 'http://ollama:11434';
        $parsed    = parse_url($ollamaurl);
        $host      = $parsed['host'] ?? 'localhost';
        $port      = $parsed['port'] ?? 11434;

        // Test con curl nativo — bypassa il security check
        $ch = curl_init($ollamaurl . '/api/tags');
        curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        curl_close($ch);

        $reachable = !$errno && $httpcode === 200;

        if ($reachable) {
            $this->add_result(
                'http_security',
                get_string('diag_http_security', 'local_aitutor'),
                self::STATUS_OK,
                get_string('diag_http_security_ok', 'local_aitutor')
            );
            return;
        }

        // Ollama non raggiungibile — controlla se è colpa di Moodle security
        // Verifica le config di sicurezza Moodle
        $allowedports = get_config(null, 'curlsecurityallowedport') ?? '';
        $portallowed  = str_contains($allowedports, (string)$port);

        if (!$portallowed) {
            $this->add_result(
                'http_security',
                get_string('diag_http_security', 'local_aitutor'),
                self::STATUS_ERROR,
                get_string(
                    'diag_http_security_error',
                    'local_aitutor',
                    (object)['host' => $host, 'port' => $port]
                ),
                get_string(
                    'diag_http_security_fix',
                    'local_aitutor',
                    (object)['host' => $host, 'port' => $port]
                ),
                [
                    'action'       => 'fix_http_security',
                    'action_label' => get_string(
                        'diag_fix_automatically',
                        'local_aitutor'
                    ),
                ]
            );
        } else {
            // Porta consentita ma Ollama non risponde
            $this->add_result(
                'http_security',
                get_string('diag_http_security', 'local_aitutor'),
                self::STATUS_WARNING,
                get_string('diag_http_security_ok', 'local_aitutor')
            );
        }
    }


    // FIX AUTOMATICI


    /**
     * Tenta di risolvere automaticamente il problema HTTP security.
     * Aggiunge Ollama alla allowlist di Moodle.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function fix_http_security(): array {
        try {
            $ollamaurl = get_config('local_aitutor', 'ollama_url')
                         ?: 'http://ollama:11434';
            $parsed    = parse_url($ollamaurl);
            $host      = $parsed['host'] ?? 'localhost';
            $port      = (string)($parsed['port'] ?? 11434);

            // Aggiungi alla allowlist
            $currenthosts = get_config(null, 'curlsecurityblockedhosts') ?? '';
            $currentports = get_config(null, 'curlsecurityallowedport') ?? '';

            // Rimuovi l'host dai bloccati se presente
            $hosts = array_filter(
                explode("\n", $currenthosts),
                fn($h) => trim($h) !== $host
            );
            set_config('curlsecurityblockedhosts', implode("\n", $hosts));

            // Aggiungi la porta agli allowed
            $ports = array_unique(array_filter(
                array_merge(
                    explode(' ', $currentports),
                    [$port]
                )
            ));
            set_config('curlsecurityallowedport', implode(' ', $ports));

            return [
                'success' => true,
                'message' => get_string(
                    'diag_fix_http_security_ok',
                    'local_aitutor',
                    (object)['host' => $host, 'port' => $port]
                ),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Forza la re-registrazione dei servizi web.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function fix_services(): array {
        global $CFG;

        try {
            require_once($CFG->dirroot . '/lib/upgradelib.php');
            external_update_descriptions('local_aitutor');

            return [
                'success' => true,
                'message' => get_string('diag_fix_services_ok', 'local_aitutor'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Assegna la capability al ruolo student.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function fix_capabilities(): array {
        global $DB;

        try {
            $studentrole = $DB->get_record(
                'role',
                ['shortname' => 'student'],
                '*',
                MUST_EXIST
            );
            $context     = \context_system::instance();

            assign_capability(
                'local/aitutor:use',
                CAP_ALLOW,
                $studentrole->id,
                $context->id,
                true
            );

            return [
                'success' => true,
                'message' => get_string('diag_fix_capabilities_ok', 'local_aitutor'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    // HELPERS


    /**
     * Add result.
     *
     * @param mixed $key
     * @param mixed $label
     * @param mixed $status
     * @param mixed $message
     * @param mixed $fix
     * @param mixed $actions
     */
    private function add_result(
        string $key,
        string $label,
        string $status,
        string $message,
        ?string $fix = null,
        array $actions = []
    ): void {
        $this->results[$key] = [
        'key'     => $key,
        'label'   => $label,
        'status'  => $status,
        'message' => $message,
        'fix'     => $fix,
        'actions' => $actions,
        'icon'    => $this->status_icon($status),
        ];
    }

    /**
     * Status icon.
     *
     * @param mixed $status
     *
     * @return string
     */
    private function status_icon(string $status): string {
        return match ($status) {
            self::STATUS_OK      => '✅',
            self::STATUS_WARNING => '⚠️',
            self::STATUS_ERROR   => '❌',
            self::STATUS_INFO    => 'ℹ️',
            default              => '•',
        };
    }

    /**
     * Calculate global status.
     *
     * @return string
     */
    private function calculate_global_status(): string {
        $statuses = array_column($this->results, 'status');

        if (in_array(self::STATUS_ERROR, $statuses)) {
            return self::STATUS_ERROR;
        }

        if (in_array(self::STATUS_WARNING, $statuses)) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

    /**
     * Count by status.
     *
     * @param mixed $status
     *
     * @return int
     */
    private function count_by_status(string $status): int {
        return count(array_filter(
            $this->results,
            fn($r) => $r['status'] === $status
        ));
    }

    /**
     * Build summary.
     *
     * @param mixed $globalstatus
     *
     * @return string
     */
    private function build_summary(string $globalstatus): string {
        return match ($globalstatus) {
            self::STATUS_OK => get_string(
                'diag_summary_ok',
                'local_aitutor'
            ),
            self::STATUS_WARNING => get_string(
                'diag_summary_warning',
                'local_aitutor',
                (object)['count' => $this->count_by_status(self::STATUS_WARNING)]
            ),
            self::STATUS_ERROR => get_string(
                'diag_summary_error',
                'local_aitutor',
                (object)['count' => $this->count_by_status(self::STATUS_ERROR)]
            ),
            default => '',
        };
    }

    /**
     * Get connection fix.
     *
     * @param mixed $errormessage
     *
     * @return string
     */
    private function get_connection_fix(string $errormessage): string {
        $msg = strtolower($errormessage);

        if (str_contains($msg, 'blocked') || str_contains($msg, 'bloccata')) {
            return get_string('diag_fix_blocked', 'local_aitutor');
        }

        if (str_contains($msg, 'refused') || str_contains($msg, 'connect')) {
            return get_string(
                'diag_fix_refused_' . $this->provider,
                'local_aitutor'
            );
        }

        if (str_contains($msg, 'api key') || str_contains($msg, 'unauthorized')) {
            return get_string('diag_fix_apikey', 'local_aitutor');
        }

        if (str_contains($msg, 'rate limit')) {
            return get_string('diag_fix_ratelimit', 'local_aitutor');
        }

        return get_string('diag_fix_generic', 'local_aitutor');
    }
}
