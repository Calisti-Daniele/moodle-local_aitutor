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
 * Provider Factory for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Factory per istanziare il provider corretto.
 *
 * Usare sempre questa classe invece di istanziare i provider
 * direttamente — gestisce il fallback e la configurazione globale.
 * @package local_aitutor
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

        return match ($providername) {
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
