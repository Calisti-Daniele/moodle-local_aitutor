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

namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Provider OpenAI — GPT-4o, GPT-4, GPT-3.5 Turbo.
 *
 * OpenAI offre i modelli GPT più diffusi al mondo.
 * Richiede una API key ottenibile su https://platform.openai.com
 * I costi variano per modello — GPT-4o mini è il più economico,
 * GPT-4o il più capace.
 *
 * Documentazione API: https://platform.openai.com/docs
 * Prezzi: https://openai.com/pricing
 * @package local_aitutor
 */
class openai_provider implements provider_interface {
    private string $apikey;
    private string $model;
    private string $embeddingmodel;
    private int $timeout;

    private const API_BASE = 'https://api.openai.com/v1';

    public function __construct() {
        $this->apikey         = get_config('aitutor', 'openai_apikey') ?: '';
        $this->model          = get_config('aitutor', 'openai_model') ?: 'gpt-4o-mini';
        $this->embeddingmodel = get_config('aitutor', 'openai_embed_model') ?: 'text-embedding-3-small';
        $this->timeout        = (int)(get_config('aitutor', 'request_timeout') ?: 60);
    }

    
    // CHAT
    

    public function chat(array $messages, string $systemprompt, array $options = []): array {

        $this->validate_apikey();

        $payload_messages = [];

        if (!empty($systemprompt)) {
            $payload_messages[] = ['role' => 'system', 'content' => $systemprompt];
        }

        foreach ($messages as $msg) {
            $payload_messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $payload_messages,
            'max_tokens'  => (int)($options['maxtokens'] ?? 1000),
            'temperature' => (float)($options['temperature'] ?? 0.7),
        ];

        $response = $this->http_post('/chat/completions', $payload);

        return [
            'content'    => $response['choices'][0]['message']['content'] ?? '',
            'tokens_in'  => $response['usage']['prompt_tokens'] ?? 0,
            'tokens_out' => $response['usage']['completion_tokens'] ?? 0,
            'model'      => $response['model'] ?? $this->model,
        ];
    }

    
    // EMBEDDING
    

    public function embed(string $text): array {

        $this->validate_apikey();

        $payload  = ['model' => $this->embeddingmodel, 'input' => $text];
        $response = $this->http_post('/embeddings', $payload);

        return $response['data'][0]['embedding'] ?? [];
    }

    
    // TEST CONNESSIONE
    

    public function test_connection(): array {
        try {
            $this->validate_apikey();
            $response = $this->http_get('/models');
            $models   = array_column($response['data'] ?? [], 'id');

            // Filtra solo i modelli GPT
            $gptmodels = array_filter($models, fn($m) => str_starts_with($m, 'gpt-'));

            return [
                'success' => true,
                'message' => get_string(
                    'openai_connected',
                    'aitutor',
                    (object)['count' => count($gptmodels)]
                ),
                'models'  => array_values($gptmodels),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'models'  => [],
            ];
        }
    }

    
    // MODELLI DISPONIBILI
    

    public function get_available_models(): array {
        return [
            // GPT-4o family
            'gpt-4o'          => 'GPT-4o — ' . get_string('model_gpt4o_desc', 'aitutor'),
            'gpt-4o-mini'     => 'GPT-4o Mini — ' . get_string('model_gpt4o_mini_desc', 'aitutor'),

            // GPT-4 Turbo
            'gpt-4-turbo'     => 'GPT-4 Turbo — ' . get_string('model_gpt4turbo_desc', 'aitutor'),

            // GPT-3.5
            'gpt-3.5-turbo'   => 'GPT-3.5 Turbo — ' . get_string('model_gpt35turbo_desc', 'aitutor'),

            // o1 family (reasoning)
            'o1-mini'         => 'o1 Mini — ' . get_string('model_o1mini_desc', 'aitutor'),
            'o1-preview'      => 'o1 Preview — ' . get_string('model_o1preview_desc', 'aitutor'),
        ];
    }

    public function get_embedding_models(): array {
        return [
            'text-embedding-3-small' => 'text-embedding-3-small — ' . get_string('embed_openai_small_desc', 'aitutor'),
            'text-embedding-3-large' => 'text-embedding-3-large — ' . get_string('embed_openai_large_desc', 'aitutor'),
            'text-embedding-ada-002' => 'text-embedding-ada-002 — ' . get_string('embed_openai_ada_desc', 'aitutor'),
        ];
    }

    public function get_name(): string {
        return 'OpenAI';
    }

    public function get_description(): string {
        return get_string('openai_description', 'aitutor');
    }

    
    // HTTP HELPERS
    

    private function http_post(string $endpoint, array $payload): array {
        $curl = new \curl();
        $curl->setopt(['CURLOPT_TIMEOUT' => $this->timeout]);

        $response = $curl->post(
            self::API_BASE . $endpoint,
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apikey,
            ]]
        );

        return $this->parse_response($response, $curl);
    }

    private function http_get(string $endpoint): array {
        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_TIMEOUT'    => 15,
            'CURLOPT_HTTPHEADER' => ['Authorization: Bearer ' . $this->apikey],
        ]);

        $response = $curl->get(self::API_BASE . $endpoint);

        return $this->parse_response($response, $curl);
    }

    private function validate_apikey(): void {
        if (empty($this->apikey)) {
            throw new \moodle_exception('error_apikey', 'aitutor');
        }
    }

    private function parse_response(string $response, \curl $curl): array {
        if ($curl->get_errno()) {
            throw new \moodle_exception(
                'error_unavailable',
                'aitutor',
                '',
                null,
                'OpenAI connection error: ' . $curl->error
            );
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
            throw new \moodle_exception(
                'error_unavailable',
                'aitutor',
                '',
                null,
                'OpenAI HTTP ' . $httpcode . ': ' . $msg
            );
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception(
                'error_unavailable',
                'aitutor',
                '',
                null,
                'Invalid JSON from OpenAI'
            );
        }

        return $decoded;
    }
}
