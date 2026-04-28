<?php
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
 */
class test_connection extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'provider' => new external_value(
                PARAM_ALPHA,
                'Provider to test: ollama|openai|anthropic',
                VALUE_REQUIRED
            ),
        ]);
    }

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