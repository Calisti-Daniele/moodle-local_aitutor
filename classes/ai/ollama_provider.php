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

/**
 * Ollama Provider for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Provider Ollama — LLM self-hosted, completamente gratuito.
 *
 * Ollama permette di eseguire modelli AI in locale o sul proprio server
 * senza costi di API e senza inviare dati a servizi esterni.
 * Ideale per ambienti con requisiti di privacy stretti o budget limitato.
 *
 * Documentazione: https://ollama.com
 * Modelli disponibili: https://ollama.com/library
 * @package local_aitutor
 */
class ollama_provider implements provider_interface {
    /** @var string @var string Modello LLM da usare per la chat */
    private string $baseurl;
    /** @var string @var string Modello da usare per gli embedding */
    private string $model;
    /** @var string @var int Timeout HTTP in secondi */
    private string $embeddingmodel;
    /** @var int $$timeout */
    private int $timeout;

    /**
     *   construct.
     */
    public function __construct() {
        $configurl = get_config('local_aitutor', 'ollama_url');

        // Debug temporaneo
        debugging('AITUTOR ollama_url from config: [' . $configurl . ']');

        $this->baseurl        = rtrim($configurl ?: 'http://ollama:11434', '/');
        $this->model          = get_config('local_aitutor', 'ollama_model') ?: 'llama3.2';
        $this->embeddingmodel = get_config('local_aitutor', 'ollama_embed_model') ?: 'nomic-embed-text';
        $this->timeout        = (int)(get_config('local_aitutor', 'request_timeout') ?: 60);
    }


    // CHAT


    /**
     * Chat.
     *
     * @param mixed $messages
     * @param mixed $systemprompt
     * @param mixed $options
     *
     * @return array
     */
    public function chat(array $messages, string $systemprompt, array $options = []): array {

        $maxtokens   = (int)($options['maxtokens'] ?? 1000);
        $temperature = (float)($options['temperature'] ?? 0.7);

        // Costruisce la lista messaggi con il system prompt in testa
        $payloadmsgs = [];

        if (!empty($systemprompt)) {
            $payloadmsgs[] = [
                'role'    => 'system',
                'content' => $systemprompt,
            ];
        }

        foreach ($messages as $msg) {
            $payloadmsgs[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model'    => $this->model,
            'messages' => $payloadmsgs,
            'stream'   => false, // Risposta completa, non streaming
            'options'  => [
                'num_predict' => $maxtokens,
                'temperature' => $temperature,
            ],
        ];

        $response = $this->http_post('/api/chat', $payload);

        return [
            'content'    => $response['message']['content'] ?? '',
            'tokens_in'  => $response['prompt_eval_count'] ?? 0,
            'tokens_out' => $response['eval_count'] ?? 0,
            'model'      => $response['model'] ?? $this->model,
        ];
    }


    // EMBEDDING


    /**
     * Embed.
     *
     * @param mixed $text
     *
     * @return array
     */
    public function embed(string $text): array {

        $payload = [
            'model'  => $this->embeddingmodel,
            'input'  => $text,
        ];

        $response = $this->http_post('/api/embed', $payload);

        // Ollama restituisce embeddings come array di array
        $embeddings = $response['embeddings'][0] ?? [];

        if (empty($embeddings)) {
            throw new \moodle_exception(
                'error_embedding',
                'local_aitutor',
                '',
                null,
                'Ollama returned empty embedding'
            );
        }

        return $embeddings;
    }


    // TEST CONNESSIONE


    /**
     * Test connection.
     *
     * @return array
     */
    public function test_connection(): array {
        try {
            $ch = curl_init($this->baseurl . '/api/tags');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            $errno    = curl_errno($ch);
            curl_close($ch);

            if ($errno || $response === false) {
                return [
                    'success' => false,
                    'message' => 'Connection error: ' . $error,
                    'models'  => [],
                ];
            }

            $data   = json_decode($response, true);
            $models = array_column($data['models'] ?? [], 'name');

            if (empty($models)) {
                return [
                    'success' => true,
                    'message' => get_string('ollama_connected_nomodels', 'local_aitutor'),
                    'models'  => [],
                ];
            }

            return [
                'success' => true,
                'message' => get_string(
                    'ollama_connected',
                    'local_aitutor',
                    (object)['count' => count($models)]
                ),
                'models'  => $models,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'models'  => [],
            ];
        }
    }


    // MODELLI DISPONIBILI


    /**
     * Get available models.
     *
     * @return array
     */
    public function get_available_models(): array {
        return [
            // Llama 3.x — Meta
            'llama3.2'       => 'Llama 3.2 (3B) — ' . get_string('model_llama32_desc', 'local_aitutor'),
            'llama3.2:1b'    => 'Llama 3.2 (1B) — ' . get_string('model_llama32_1b_desc', 'local_aitutor'),
            'llama3.1'       => 'Llama 3.1 (8B) — ' . get_string('model_llama31_desc', 'local_aitutor'),
            'llama3.1:70b'   => 'Llama 3.1 (70B) — ' . get_string('model_llama31_70b_desc', 'local_aitutor'),

            // Mistral
            'mistral'        => 'Mistral 7B — ' . get_string('model_mistral_desc', 'local_aitutor'),
            'mistral-nemo'   => 'Mistral Nemo (12B) — ' . get_string('model_mistral_nemo_desc', 'local_aitutor'),

            // Gemma — Google
            'gemma2'         => 'Gemma 2 (9B) — ' . get_string('model_gemma2_desc', 'local_aitutor'),
            'gemma2:2b'      => 'Gemma 2 (2B) — ' . get_string('model_gemma2_2b_desc', 'local_aitutor'),

            // Phi — Microsoft
            'phi3'           => 'Phi-3 Mini (3.8B) — ' . get_string('model_phi3_desc', 'local_aitutor'),
            'phi3:medium'    => 'Phi-3 Medium (14B) — ' . get_string('model_phi3_medium_desc', 'local_aitutor'),

            // Qwen — Alibaba
            'qwen2.5'        => 'Qwen 2.5 (7B) — ' . get_string('model_qwen25_desc', 'local_aitutor'),
            'qwen2.5:14b'    => 'Qwen 2.5 (14B) — ' . get_string('model_qwen25_14b_desc', 'local_aitutor'),

            // DeepSeek
            'deepseek-r1'    => 'DeepSeek R1 (7B) — ' . get_string('model_deepseek_r1_desc', 'local_aitutor'),
        ];
    }

    /**
     * Get embedding models.
     *
     * @return array
     */
    public function get_embedding_models(): array {
        return [
            'nomic-embed-text'  => 'Nomic Embed Text — ' . get_string('embed_nomic_desc', 'local_aitutor'),
            'mxbai-embed-large' => 'MxBai Embed Large — ' . get_string('embed_mxbai_desc', 'local_aitutor'),
            'all-minilm'        => 'All MiniLM — ' . get_string('embed_minilm_desc', 'local_aitutor'),
        ];
    }


    // INFO PROVIDER


    /**
     * Get name.
     *
     * @return string
     */
    public function get_name(): string {
        return 'Ollama (self-hosted)';
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('ollama_description', 'local_aitutor');
    }


    // HTTP HELPERS PRIVATI


    /**
     * Http post.
     *
     * @param mixed $endpoint
     * @param mixed $payload
     *
     * @return array
     */
    private function http_post(string $endpoint, array $payload): array {
        $url  = $this->baseurl . $endpoint;
        $json = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_TIMEOUT        => $this->timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
        ],
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        return $this->parse_response_raw($response, $httpcode, $error, $errno);
    }

    /**
     * Http get.
     *
     * @param mixed $endpoint
     *
     * @return array
     */
    private function http_get(string $endpoint): array {
        $url = $this->baseurl . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        return $this->parse_response_raw($response, $httpcode, $error, $errno);
    }

    /**
     * Parse response raw.
     *
     * @param mixed $response
     * @param mixed $httpcode
     * @param mixed $error
     * @param mixed $errno
     *
     * @return array
     */
    private function parse_response_raw(
        string|bool $response,
        int $httpcode,
        string $error,
        int $errno
    ): array {
        // Errore di connessione
        if ($errno || $response === false) {
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Ollama connection error: ' . $error
            );
        }

        // Errore HTTP
        if ($httpcode >= 400) {
            $decoded = json_decode($response, true);
            $msg     = $decoded['error'] ?? $response;
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Ollama HTTP ' . $httpcode . ': ' . $msg
            );
        }

        // Decodifica JSON
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Invalid JSON from Ollama: ' . json_last_error_msg()
            );
        }

        return $decoded;
    }

    /**
     * Parsa la risposta HTTP e gestisce gli errori.
     *
     * @param string $response  Risposta grezza
     * @param \curl  $curl      Istanza curl usata
     * @return array
     * @throws \moodle_exception
     */
    private function parse_response(string $response, \curl $curl): array {
        $info = $curl->get_info();

        // Errore di connessione (timeout, host non raggiungibile, ecc.)
        if ($curl->get_errno()) {
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Ollama connection error: ' . $curl->error
            );
        }

        // Errore HTTP
        $httpcode = $info['http_code'] ?? 0;
        if ($httpcode >= 400) {
            $error = json_decode($response, true);
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Ollama HTTP ' . $httpcode . ': ' . ($error['error'] ?? $response)
            );
        }

        // Decodifica JSON
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception(
                'error_unavailable',
                'local_aitutor',
                '',
                null,
                'Invalid JSON from Ollama: ' . json_last_error_msg()
            );
        }

        return $decoded;
    }
}
