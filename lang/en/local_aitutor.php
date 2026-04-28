<?php
defined('MOODLE_INTERNAL') || die();

// Plugin
$string['pluginname']              = 'AI Personal Assistant';

// Capabilities
$string['aitutor:use']             = 'Use the AI Personal Assistant';
$string['aitutor:viewreports']     = 'View AI Assistant reports';
$string['aitutor:manage']          = 'Manage AI Assistant settings';

// Widget UI
$string['widget_title']            = 'AI Assistant';
$string['widget_open']             = 'Open AI Assistant';
$string['widget_close']            = 'Close AI Assistant';
$string['widget_placeholder']      = 'Ask me anything about your courses...';
$string['widget_send']             = 'Send';
$string['widget_thinking']         = 'Thinking...';
$string['widget_clear']            = 'Clear conversation';
$string['widget_clear_confirm']    = 'Are you sure you want to clear the conversation?';
$string['widget_fullscreen']       = 'Open full screen';
$string['widget_error']            = 'An error occurred. Please try again.';
$string['widget_welcome']          = 'Hi {$a->firstname}! I\'m your personal AI assistant. I know all your courses, grades and deadlines. How can I help you?';

// Domande esempio
$string['suggestion_courses']      = 'Which courses am I enrolled in?';
$string['suggestion_progress']     = 'What\'s my overall progress?';
$string['suggestion_deadlines']    = 'Do I have upcoming deadlines?';
$string['suggestion_grades']       = 'How are my grades?';
$string['suggestion_certificates'] = 'Which certificates have I earned?';

// Admin settings
$string['settings_provider']              = 'AI Provider';
$string['settings_provider_desc']         = 'Select the AI provider to power the assistant.';
$string['settings_ollama_url']            = 'Ollama URL';
$string['settings_ollama_url_desc']       = 'Base URL of your Ollama instance. Use http://ollama:11434 if running in Docker on the same network.';
$string['settings_ollama_model']          = 'Ollama Chat Model';
$string['settings_ollama_model_desc']     = 'The LLM model to use for conversation.';
$string['settings_ollama_embed_model']    = 'Ollama Embedding Model';
$string['settings_ollama_embed_model_desc'] = 'The model to use for generating embeddings.';
$string['settings_openai_apikey']         = 'OpenAI API Key';
$string['settings_openai_apikey_desc']    = 'Your OpenAI API key from platform.openai.com';
$string['settings_openai_model']          = 'OpenAI Model';
$string['settings_anthropic_apikey']      = 'Anthropic API Key';
$string['settings_anthropic_apikey_desc'] = 'Your Anthropic API key from console.anthropic.com';
$string['settings_anthropic_model']       = 'Anthropic Model';
$string['settings_maxtokens']             = 'Max tokens per response';
$string['settings_maxtokens_desc']        = 'Maximum tokens the AI can use per response. Between 100 and 4000.';
$string['settings_enabled_roles']         = 'Enable assistant for';
$string['settings_enabled_roles_desc']    = 'Select which roles can use the AI assistant.';
$string['settings_test_connection']       = 'Test connection';

// Errori
$string['error_noprovider']        = 'No AI provider configured. Please contact your administrator.';
$string['error_apikey']            = 'Invalid or missing API key.';
$string['error_ratelimit']         = 'Rate limit reached. Please wait and try again.';
$string['error_unavailable']       = 'AI service temporarily unavailable.';
$string['error_disabled']          = 'The AI Assistant is not enabled for your account.';

// Privacy
$string['privacy:metadata:local_aitutor_sessions']         = 'AI Assistant session data';
$string['privacy:metadata:local_aitutor_messages']         = 'Messages exchanged with the AI Assistant';
$string['privacy:metadata:local_aitutor_sessions:userid']  = 'The user who owns this session';
$string['privacy:metadata:local_aitutor_messages:message'] = 'The message content';
$string['privacy:metadata:local_aitutor_messages:role']    = 'Whether sent by user or AI';

// Settings headings
$string['settings_heading_general']          = 'General Settings';
$string['settings_heading_general_desc']     = 'Global configuration for the AI Personal Assistant.';
$string['settings_heading_provider']         = 'AI Provider';
$string['settings_heading_provider_desc']    = 'Select and configure the AI provider that powers the assistant.';
$string['settings_heading_ollama']           = 'Ollama Configuration';
$string['settings_heading_ollama_desc']      = 'Ollama runs AI models locally — free, private, no API key required. Install from ollama.com or run via Docker.';
$string['settings_heading_openai']           = 'OpenAI Configuration';
$string['settings_heading_openai_desc']      = 'Use OpenAI\'s GPT models. Requires an API key from platform.openai.com. Usage is billed per token.';
$string['settings_heading_anthropic']        = 'Anthropic Configuration';
$string['settings_heading_anthropic_desc']   = 'Use Anthropic\'s Claude models. Requires an API key from console.anthropic.com. Usage is billed per token.';
$string['settings_heading_test']             = 'Connection Test';
$string['settings_heading_test_desc']        = 'Test the connection to the currently selected AI provider before saving.';

// General settings
$string['settings_enabled']                  = 'Enable AI Assistant';
$string['settings_enabled_desc']             = 'Show the AI Assistant widget on all Moodle pages for logged-in users.';
$string['settings_temperature_desc']         = 'Controls response creativity. Lower = more precise and deterministic. Higher = more creative and varied.';

// Provider labels
$string['provider_ollama_label']             = 'Free, self-hosted, no API key needed';
$string['provider_openai_label']             = 'GPT-4o, GPT-4o Mini — paid API';
$string['provider_anthropic_label']          = 'Claude 3.5 Sonnet/Haiku — paid API';

// Test connection
$string['settings_test_connection_desc']     = 'Click to verify the connection to the selected provider. Make sure to save your settings first.';

// Ollama models
$string['model_llama32_desc']                = 'Fast, efficient. Good for everyday tasks. Recommended for most setups.';
$string['model_llama32_1b_desc']             = 'Ultra-lightweight. Best for very limited hardware.';
$string['model_llama31_desc']                = 'Larger, more capable. Requires ~8GB RAM.';
$string['model_llama31_70b_desc']            = 'Very powerful. Requires ~40GB RAM. For high-end servers only.';
$string['model_mistral_desc']                = 'Excellent reasoning, fast responses. Great alternative to Llama.';
$string['model_mistral_nemo_desc']           = 'Larger Mistral variant. Better at complex tasks.';
$string['model_gemma2_desc']                 = 'Google\'s open model. Strong at instruction following.';
$string['model_gemma2_2b_desc']              = 'Tiny Google model. Ideal for very low-resource environments.';
$string['model_phi3_desc']                   = 'Microsoft\'s compact model. Surprisingly capable for its size.';
$string['model_phi3_medium_desc']            = 'Larger Phi variant. Better reasoning, more resources required.';
$string['model_qwen25_desc']                 = 'Alibaba\'s multilingual model. Excellent for non-English content.';
$string['model_qwen25_14b_desc']             = 'Larger Qwen. Strong multilingual and reasoning capabilities.';
$string['model_deepseek_r1_desc']            = 'Strong reasoning model. Great for analytical questions.';

// Embedding models
$string['embed_nomic_desc']                  = '768 dimensions. Best balance of quality and speed. Recommended.';
$string['embed_mxbai_desc']                  = '1024 dimensions. Higher quality embeddings, slightly slower.';
$string['embed_minilm_desc']                 = '384 dimensions. Very fast, lower quality. For quick prototyping.';

// OpenAI models
$string['model_gpt4o_desc']                  = 'Most capable OpenAI model. Best for complex reasoning.';
$string['model_gpt4o_mini_desc']             = 'Fast and cheap. Recommended for most use cases.';
$string['model_gpt4turbo_desc']              = 'Previous generation GPT-4. Very capable but pricier.';
$string['model_gpt35turbo_desc']             = 'Fast and cheap. Good for simple tasks.';
$string['model_o1mini_desc']                 = 'Reasoning-focused. Great for math and logic problems.';
$string['model_o1preview_desc']              = 'Most powerful reasoning model from OpenAI.';

// Embedding OpenAI
$string['embed_openai_small_desc']           = '1536 dimensions. Fast and cheap. Recommended.';
$string['embed_openai_large_desc']           = '3072 dimensions. Higher quality. More expensive.';
$string['embed_openai_ada_desc']             = 'Legacy model. Use small/large instead.';

// Anthropic models
$string['model_claude35sonnet_desc']         = 'Best Claude model. Excellent reasoning and writing.';
$string['model_claude35haiku_desc']          = 'Fast and affordable Claude. Great for chat.';
$string['model_claude3opus_desc']            = 'Most powerful Claude 3. Very expensive.';
$string['model_claude3sonnet_desc']          = 'Balanced Claude 3 model.';
$string['model_claude3haiku_desc']           = 'Fastest, cheapest Claude 3.';

// Temperature
$string['temperature_precise']               = '0.0 — Precise (deterministic)';
$string['temperature_focused']               = '0.3 — Focused';
$string['temperature_balanced']              = '0.7 — Balanced (recommended)';
$string['temperature_creative']              = '1.0 — Creative';
$string['temperature_wild']                  = '1.5 — Very creative';

// Misc
$string['error_empty_message']               = 'Message cannot be empty.';

// Ollama connection strings
$string['ollama_connected']                  = 'Connected successfully. {$a->count} model(s) available.';
$string['ollama_connected_nomodels']         = 'Connected but no models found. Run: ollama pull llama3.2';
$string['ollama_connection_failed']          = 'Connection failed: {$a->error}';
$string['openai_connected']                  = 'Connected. {$a->count} GPT model(s) available.';
$string['anthropic_connected']              = 'Connected successfully.';

// Privacy metadata
$string['privacy:metadata:local_aitutor_sessions']            = 'Stores AI Assistant conversation sessions for each user.';
$string['privacy:metadata:local_aitutor_sessions:userid']     = 'The ID of the user who owns this session.';
$string['privacy:metadata:local_aitutor_messages']            = 'Stores individual messages exchanged with the AI Assistant.';
$string['privacy:metadata:local_aitutor_messages:sessionid']  = 'The session this message belongs to.';
$string['privacy:metadata:local_aitutor_messages:role']       = 'Whether the message was sent by the user or the AI.';
$string['privacy:metadata:local_aitutor_messages:message']    = 'The full content of the message.';
$string['privacy:metadata:timecreated']                       = 'The time this record was created.';
$string['privacy:metadata:timemodified']                      = 'The time this record was last modified.';
$string['privacy:metadata:tokencount']                        = 'Number of AI tokens used.';
$string['privacy:metadata:ai_provider']                       = 'When using external AI providers (OpenAI, Anthropic), messages and course context are sent to their API. Please review their privacy policies.';
$string['privacy:metadata:ai_provider:message']               = 'The user message sent to the AI provider.';
$string['privacy:metadata:ai_provider:systemprompt']          = 'The system prompt including course context sent to the AI provider.';

// ── Diagnostica ──────────────────────────────────────────────────────────────
$string['diag_php_version']                = 'PHP Version';
$string['diag_php_version_ok']             = 'PHP {$a->version} — OK';
$string['diag_php_version_error']          = 'PHP {$a->current} found, {$a->required}+ required.';
$string['diag_php_version_fix']            = 'Upgrade PHP to version {$a->required} or higher.';

$string['diag_php_extensions']             = 'PHP Extensions';
$string['diag_php_extensions_ok']          = 'All required extensions are installed.';
$string['diag_php_extensions_error']       = 'Missing extensions: {$a->missing}';
$string['diag_php_extensions_fix']         = 'Install missing extensions: sudo apt install php-{$a->missing}';

$string['diag_plugin_enabled']             = 'Plugin Status';
$string['diag_plugin_enabled_ok']          = 'Plugin is enabled.';
$string['diag_plugin_enabled_warning']     = 'Plugin is installed but not enabled.';
$string['diag_plugin_enabled_fix']         = 'Go to Settings and enable the AI Assistant.';

$string['diag_capabilities']              = 'User Permissions';
$string['diag_capabilities_ok']           = 'Permissions configured. {$a->count} user(s) can use the assistant.';
$string['diag_capabilities_warning']      = 'The "student" role does not have permission to use the assistant.';
$string['diag_capabilities_fix']          = 'Click "Fix automatically" or go to Site Administration → Users → Permissions → Define roles → Student → local/aitutor:use → Allow';

$string['diag_services']                  = 'Web Services';
$string['diag_services_ok']               = 'All {$a->count} web services registered correctly.';
$string['diag_services_error']            = 'Missing services: {$a->missing}';
$string['diag_services_fix']              = 'Click "Fix automatically" to re-register services, or go to Site Administration → Notifications.';

$string['diag_provider_config']           = 'Provider Configuration';
$string['diag_provider_config_ok']        = '{$a->provider} is configured.';
$string['diag_provider_config_fix']       = 'Go to Settings and complete the provider configuration.';
$string['diag_provider_no_url']           = 'Ollama URL is not set.';
$string['diag_provider_no_model']         = 'No chat model selected.';
$string['diag_provider_no_apikey']        = 'API key is missing.';
$string['diag_provider_invalid_apikey']   = 'API key format is invalid.';

$string['diag_provider_connection']       = 'Provider Connection';
$string['diag_provider_connection_ok']    = '{$a->provider} connected. Models: {$a->models}';

$string['diag_http_security']             = 'Moodle HTTP Security';
$string['diag_http_security_ok']          = 'Ollama URL is allowed by Moodle security settings.';
$string['diag_http_security_error']       = 'Moodle is blocking requests to {$a->host}:{$a->port}.';
$string['diag_http_security_fix']         = 'Click "Fix automatically" or go to Site Administration → Security → HTTP Security and add {$a->host} to Allowed hosts and {$a->port} to Allowed ports.';

$string['diag_fix_automatically']         = 'Fix automatically';
$string['diag_fix_blocked']               = 'Moodle is blocking this URL. Go to Site Administration → Security → HTTP Security and whitelist the host and port.';
$string['diag_fix_refused_ollama']        = 'Ollama is not running or not reachable. Make sure Ollama is installed and running: systemctl start ollama (Linux) or ollama serve (macOS).';
$string['diag_fix_refused_openai']        = 'Cannot reach OpenAI API. Check your internet connection and firewall settings.';
$string['diag_fix_refused_anthropic']     = 'Cannot reach Anthropic API. Check your internet connection and firewall settings.';
$string['diag_fix_apikey']                = 'Check your API key is correct and has not expired. Make sure billing is enabled on your account.';
$string['diag_fix_ratelimit']             = 'You have exceeded your API rate limit. Wait a few minutes and try again, or upgrade your plan.';
$string['diag_fix_generic']               = 'Check your provider settings and internet connection.';

$string['diag_fix_http_security_ok']      = 'HTTP Security updated: {$a->host} added to allowed hosts, port {$a->port} added to allowed ports.';
$string['diag_fix_services_ok']           = 'Web services re-registered successfully.';
$string['diag_fix_capabilities_ok']       = 'Permission "local/aitutor:use" assigned to student role.';

$string['diag_summary_ok']                = 'Everything is configured correctly. The AI Assistant is ready to use!';
$string['diag_summary_warning']           = '{$a->count} warning(s) found. The assistant works but some settings need attention.';
$string['diag_summary_error']             = '{$a->count} error(s) found. Please fix them before using the assistant.';

// Banner
$string['diag_banner_title_ok']      = 'AI Assistant is ready';
$string['diag_banner_title_warning'] = 'AI Assistant needs attention';
$string['diag_banner_title_error']   = 'AI Assistant is not configured';
$string['diag_banner_action']        = 'View diagnostics & fix';
$string['diag_banner_details']       = 'View details';

// Pagina diagnostica
$string['diagnostics_title']         = 'AI Assistant — Diagnostics';
$string['diag_status_ready']         = '✅ Everything is working';
$string['diag_status_warning']       = '⚠️ Attention required';
$string['diag_status_error']         = '❌ Configuration needed';
$string['diag_rerun']                = 'Re-run diagnostics';
$string['diag_goto_settings']        = 'Go to settings';
$string['diag_running']              = 'Running diagnostics...';
$string['diag_fix_all']              = 'Fix all automatically';