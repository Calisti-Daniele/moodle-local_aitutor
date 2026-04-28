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
namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Provider Anthropic — Claude 3.5 Sonnet, Claude 3 Haiku, ecc.
 *
 * Anthropic è l'azienda che ha creato i modelli Claude, noti per
 * l'ottima capacità di ragionamento, la sicurezza e il contesto
 * molto lungo (fino a 200k token). Ottimo per analisi di documenti
 * lunghi e conversazioni complesse.
 *
 * Documentazione API: https://docs.anthropic.com
 * Prezzi: https://www.anthropic.com/pricing
 * API key: https://console.anthropic.com
 */
class anthropic_provider implements provider_interface {

    private string $apikey;
    private string $model;
    private int    $timeout;

    private const API_BASE    = 'https://api.anthropic.com/v1';
    private const API_VERSION = '2023-06-01';

    public function __construct() {
        $this->apikey   = get_config('aitutor', 'anthropic_apikey') ?: '';
        $this->model    = get_config('aitutor', 'anthropic_model') ?: 'claude-3-5-haiku-latest';
        $this->timeout  = (int)(get_config('aitutor', 'request_timeout') ?: 60);
    }

    // =========================================================================
    // CHAT
    // =========================================================================

    public function chat(array $messages, string $systemprompt, array $options = []): array {

        $this->validate_apikey();

        // Anthropic vuole i messaggi senza il ruolo 'system'
        // Il system prompt va in un campo separato
        $payload_messages = array_map(fn($m) => [
            'role'    => $m['role'],
            'content' => $m['content'],
        ], $messages);

        // Anthropic richiede che il primo messaggio sia sempre 'user'
        if (empty($payload_messages) || $payload_messages[0]['role'] !== 'user') {
            array_unshift($payload_messages, [
                'role'    => 'user',
                'content' => 'Hello',
            ]);
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => (int)($options['maxtokens'] ?? 1000),
            'messages'   => $payload_messages,
        ];

        // Temperature non supportata da tutti i modelli Anthropic
        if (isset($options['temperature'])) {
            $payload['temperature'] = (float)$options['temperature'];
        }

        // System prompt nel campo dedicato
        if (!empty($systemprompt)) {
            $payload['system'] = $systemprompt;
        }

        $response = $this->http_post('/messages', $payload);

        return [
            'content'    => $response['content'][0]['text'] ?? '',
            'tokens_in'  => $response['usage']['input_tokens'] ?? 0,
            'tokens_out' => $response['usage']['output_tokens'] ?? 0,
            'model'      => $response['model'] ?? $this->model,
        ];
    }

    // =========================================================================
    // EMBEDDING
    // Anthropic non ha un endpoint embedding nativo.
    // Usiamo un fallback su OpenAI o lanciamo eccezione.
    // =========================================================================

    public function embed(string $text): array {
        throw new \moodle_exception('error_unavailable', 'aitutor', '', null,
            'Anthropic does not provide an embedding API. ' .
            'Please configure a separate embedding provider (Ollama or OpenAI).');
    }

    // =========================================================================
    // TEST CONNESSIONE
    // =========================================================================

    public function test_connection(): array {
        try {
            $this->validate_apikey();

            // Anthropic non ha un endpoint /models pubblico
            // Facciamo una chiamata minimale per verificare la chiave
            $this->chat(
                [['role' => 'user', 'content' => 'Hi']],
                '',
                ['maxtokens' => 5]
            );

            return [
                'success' => true,
                'message' => get_string('anthropic_connected', 'aitutor'),
                'models'  => array_keys($this->get_available_models()),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'models'  => [],
            ];
        }
    }

    // =========================================================================
    // MODELLI DISPONIBILI
    // =========================================================================

    public function get_available_models(): array {
        return [
            // Claude 3.5 family
            'claude-3-5-sonnet-latest' => 'Claude 3.5 Sonnet — ' . get_string('model_claude35sonnet_desc', 'aitutor'),
            'claude-3-5-haiku-latest'  => 'Claude 3.5 Haiku — ' . get_string('model_claude35haiku_desc', 'aitutor'),

            // Claude 3 family
            'claude-3-opus-latest'     => 'Claude 3 Opus — ' . get_string('model_claude3opus_desc', 'aitutor'),
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet — ' . get_string('model_claude3sonnet_desc', 'aitutor'),
            'claude-3-haiku-20240307'  => 'Claude 3 Haiku — ' . get_string('model_claude3haiku_desc', 'aitutor'),
        ];
    }

    public function get_embedding_models(): array {
        // Anthropic non supporta embedding nativi
        return [];
    }

    public function get_name(): string {
        return 'Anthropic (Claude)';
    }

    public function get_description(): string {
        return get_string('anthropic_description', 'aitutor');
    }

    // =========================================================================
    // HTTP HELPERS
    // =========================================================================

    private function http_post(string $endpoint, array $payload): array {
        $curl = new \curl();
        $curl->setopt(['CURLOPT_TIMEOUT' => $this->timeout]);

        $response = $curl->post(
            self::API_BASE . $endpoint,
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apikey,
                'anthropic-version: ' . self::API_VERSION,
            ]]
        );

        return $this->parse_response($response, $curl);
    }

    private function validate_apikey(): void {
        if (empty($this->apikey)) {
            throw new \moodle_exception('error_apikey', 'aitutor');
        }
    }

    private function parse_response(string $response, \curl $curl): array {
        if ($curl->get_errno()) {
            throw new \moodle_exception('error_unavailable', 'aitutor', '', null,
                'Anthropic connection error: ' . $curl->error);
        }

        $info     = $curl->get_info();
        $httpcode = $info['http_code'] ?? 0;
        $decoded  = json_decode($response, true);

        if ($httpcode === 401) {
            throw new \moodle_exception('error_apikey', 'aitutor');
        }

        if ($httpcode === 429) {
            throw new \moodle_exception('error_ratelimit', 'aitutor');
        }

        if ($httpcode >= 400) {
            $msg = $decoded['error']['message'] ?? $response;
            throw new \moodle_exception('error_unavailable', 'aitutor', '', null,
                'Anthropic HTTP ' . $httpcode . ': ' . $msg);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception('error_unavailable', 'aitutor', '', null,
                'Invalid JSON from Anthropic');
        }

        return $decoded;
    }
}