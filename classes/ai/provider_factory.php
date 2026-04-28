<?php
namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Factory per istanziare il provider corretto.
 *
 * Usare sempre questa classe invece di istanziare i provider
 * direttamente — gestisce il fallback e la configurazione globale.
 */
class provider_factory {

    /**
     * Restituisce il provider configurato per questa istanza.
     *
     * @param string|null $override  Provider specifico ('openai'|'anthropic'|'ollama')
     *                               Se null usa il provider globale dell'admin
     * @return provider_interface
     * @throws \moodle_exception  Se nessun provider è configurato
     */
    public static function get_provider(?string $override = null): provider_interface {

        // Determina quale provider usare
        $providername = $override ?: get_config('aitutor', 'provider') ?: 'ollama';

        return match($providername) {
            'openai'    => new openai_provider(),
            'anthropic' => new anthropic_provider(),
            'ollama'    => new ollama_provider(),
            default     => throw new \moodle_exception('error_noprovider', 'aitutor'),
        };
    }

    /**
     * Restituisce tutti i provider disponibili.
     * Usato nelle pagine admin per mostrare le opzioni.
     *
     * @return provider_interface[]  ['openai' => OpenaiProvider, ...]
     */
    public static function get_all_providers(): array {
        return [
            'ollama'    => new ollama_provider(),
            'openai'    => new openai_provider(),
            'anthropic' => new anthropic_provider(),
        ];
    }

    /**
     * Restituisce le opzioni provider per i form select.
     *
     * @return array ['ollama' => 'Ollama (self-hosted)', ...]
     */
    public static function get_provider_options(): array {
        $options = [];
        foreach (self::get_all_providers() as $key => $provider) {
            $options[$key] = $provider->get_name();
        }
        return $options;
    }
}