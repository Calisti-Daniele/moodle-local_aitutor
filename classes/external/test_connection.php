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
 * Test Connection for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aitutor\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use local_aitutor\ai\provider_factory;

/**
 * Web API: test_connection
 * Testa la connessione al provider AI configurato.
 * Usata dalla pagina admin settings.
 * @package local_aitutor
 */
class test_connection extends external_api {
    /**
     * Execute parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'provider' => new external_value(
                PARAM_ALPHA,
                'Provider to test: ollama|openai|anthropic',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Execute returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Connection successful'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
            'models'  => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Model name'),
                'Available models',
                VALUE_OPTIONAL
            ),
        ]);
    }

    /**
     * Execute.
     *
     * @param mixed $provider
     *
     * @return array
     */
    public static function execute(string $provider): array {
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/aitutor:manage', $context);

        try {
            $providerinstance = provider_factory::get_provider($provider);
            $result           = $providerinstance->test_connection();

            return [
                'success' => $result['success'],
                'message' => $result['message'],
                'models'  => $result['models'] ?? [],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'models'  => [],
            ];
        }
    }
}
