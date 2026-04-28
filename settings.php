<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

global $ADMIN, $CFG;

/** @var bool $hassiteconfig */
if ($hassiteconfig) {

    // =========================================================================
    // PAGINA IMPOSTAZIONI PRINCIPALE
    // =========================================================================
    $settings = new admin_settingpage(
        'local_aitutor',
        get_string('pluginname', 'local_aitutor')
    );

    $ADMIN->add('localplugins', $settings);

    // =========================================================================
    // BANNER DIAGNOSTICA
    // Mostrato in cima alla pagina se il plugin non è configurato correttamente
    // =========================================================================
    if ($hassiteconfig) {

        // Esegui quick check per il banner
        $diagcheck = new \local_aitutor\setup\diagnostics();
        $report    = $diagcheck->run(quickcheck: true);

        $diagurl   = new moodle_url('/local/aitutor/diagnostics.php');

        if (!$report['ready']) {

            // Banner errore/warning
            $bannerclass = $report['status'] === 'error'
                ? 'alert-danger'
                : 'alert-warning';

            $bannericon = $report['status'] === 'error' ? '❌' : '⚠️';

            $bannerhtml  = '<div class="alert ' . $bannerclass . ' d-flex
                                align-items-center justify-content-between mb-4">';
            $bannerhtml .= '<div>';
            $bannerhtml .= '<strong>' . $bannericon . ' ';
            $bannerhtml .= get_string('diag_banner_title_' . $report['status'],
                'local_aitutor') . '</strong><br>';
            $bannerhtml .= '<small>' . $report['summary'] . '</small>';
            $bannerhtml .= '</div>';
            $bannerhtml .= '<a href="' . $diagurl->out(false) . '"
                            class="btn btn-sm btn-' .
                            ($report['status'] === 'error'
                                ? 'danger' : 'warning') . ' ml-3">';
            $bannerhtml .= '🔍 ' . get_string('diag_banner_action', 'local_aitutor');
            $bannerhtml .= '</a>';
            $bannerhtml .= '</div>';

            $settings->add(new admin_setting_heading(
                'local_aitutor/banner_diagnostics',
                '',
                $bannerhtml
            ));

        } else {

            // Banner verde — tutto OK
            $bannerhtml  = '<div class="alert alert-success mb-4">';
            $bannerhtml .= '✅ <strong>';
            $bannerhtml .= get_string('diag_banner_title_ok', 'local_aitutor');
            $bannerhtml .= '</strong> — ';
            $bannerhtml .= get_string('diag_summary_ok', 'local_aitutor');
            $bannerhtml .= ' <a href="' . $diagurl->out(false) . '" class="ml-2">';
            $bannerhtml .= get_string('diag_banner_details', 'local_aitutor');
            $bannerhtml .= '</a>';
            $bannerhtml .= '</div>';

            $settings->add(new admin_setting_heading(
                'local_aitutor/banner_diagnostics',
                '',
                $bannerhtml
            ));
        }
    }
    // =========================================================================
    // SEZIONE 1 — Generale
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_general',
        get_string('settings_heading_general', 'local_aitutor'),
        get_string('settings_heading_general_desc', 'local_aitutor')
    ));

    // Abilita/disabilita il plugin globalmente
    $settings->add(new admin_setting_configcheckbox(
        'local_aitutor/enabled',
        get_string('settings_enabled', 'local_aitutor'),
        get_string('settings_enabled_desc', 'local_aitutor'),
        1  // Default: abilitato
    ));

    // Token massimi per risposta
    $settings->add(new admin_setting_configtext(
        'local_aitutor/maxtokens',
        get_string('settings_maxtokens', 'local_aitutor'),
        get_string('settings_maxtokens_desc', 'local_aitutor'),
        '1000',
        PARAM_INT
    ));

    // Temperature
    $settings->add(new admin_setting_configselect(
        'local_aitutor/temperature',
        get_string('settings_temperature', 'local_aitutor'),
        get_string('settings_temperature_desc', 'local_aitutor'),
        '0.7',
        [
            '0.0' => get_string('temperature_precise',  'local_aitutor'),
            '0.3' => get_string('temperature_focused',  'local_aitutor'),
            '0.7' => get_string('temperature_balanced', 'local_aitutor'),
            '1.0' => get_string('temperature_creative', 'local_aitutor'),
            '1.5' => get_string('temperature_wild',     'local_aitutor'),
        ]
    ));

    // =========================================================================
    // SEZIONE 2 — Provider AI
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_provider',
        get_string('settings_heading_provider', 'local_aitutor'),
        get_string('settings_heading_provider_desc', 'local_aitutor')
    ));

    // Selezione provider
    $settings->add(new admin_setting_configselect(
        'local_aitutor/provider',
        get_string('settings_provider', 'local_aitutor'),
        get_string('settings_provider_desc', 'local_aitutor'),
        'ollama',  // Default: Ollama (gratuito, self-hosted)
        [
            'ollama'    => '🦙 Ollama — ' . get_string('provider_ollama_label', 'local_aitutor'),
            'openai'    => '🤖 OpenAI — ' . get_string('provider_openai_label', 'local_aitutor'),
            'anthropic' => '🧠 Anthropic — ' . get_string('provider_anthropic_label', 'local_aitutor'),
        ]
    ));

    // =========================================================================
    // SEZIONE 3 — Ollama
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_ollama',
        '🦙 ' . get_string('settings_heading_ollama', 'local_aitutor'),
        get_string('settings_heading_ollama_desc', 'local_aitutor')
    ));

    // URL Ollama
    $settings->add(new admin_setting_configtext(
        'local_aitutor/ollama_url',
        get_string('settings_ollama_url', 'local_aitutor'),
        get_string('settings_ollama_url_desc', 'local_aitutor'),
        'http://ollama:11434',  // Default per Docker
        PARAM_URL
    ));

    // Modello chat Ollama
    $settings->add(new admin_setting_configselect(
        'local_aitutor/ollama_model',
        get_string('settings_ollama_model', 'local_aitutor'),
        get_string('settings_ollama_model_desc', 'local_aitutor'),
        'llama3.2',
        [
            // Llama — Meta
            'llama3.2'      => 'Llama 3.2 3B — ' .
                get_string('model_llama32_desc',     'local_aitutor'),
            'llama3.2:1b'   => 'Llama 3.2 1B — ' .
                get_string('model_llama32_1b_desc',  'local_aitutor'),
            'llama3.1'      => 'Llama 3.1 8B — ' .
                get_string('model_llama31_desc',     'local_aitutor'),
            'llama3.1:70b'  => 'Llama 3.1 70B — ' .
                get_string('model_llama31_70b_desc', 'local_aitutor'),

            // Mistral
            'mistral'       => 'Mistral 7B — ' .
                get_string('model_mistral_desc',     'local_aitutor'),
            'mistral-nemo'  => 'Mistral Nemo 12B — ' .
                get_string('model_mistral_nemo_desc','local_aitutor'),

            // Gemma — Google
            'gemma2'        => 'Gemma 2 9B — ' .
                get_string('model_gemma2_desc',      'local_aitutor'),
            'gemma2:2b'     => 'Gemma 2 2B — ' .
                get_string('model_gemma2_2b_desc',   'local_aitutor'),

            // Phi — Microsoft
            'phi3'          => 'Phi-3 Mini 3.8B — ' .
                get_string('model_phi3_desc',        'local_aitutor'),
            'phi3:medium'   => 'Phi-3 Medium 14B — ' .
                get_string('model_phi3_medium_desc', 'local_aitutor'),

            // Qwen — Alibaba
            'qwen2.5'       => 'Qwen 2.5 7B — ' .
                get_string('model_qwen25_desc',      'local_aitutor'),
            'qwen2.5:14b'   => 'Qwen 2.5 14B — ' .
                get_string('model_qwen25_14b_desc',  'local_aitutor'),

            // DeepSeek
            'deepseek-r1'   => 'DeepSeek R1 7B — ' .
                get_string('model_deepseek_r1_desc', 'local_aitutor'),
        ]
    ));

    // Modello embedding Ollama
    $settings->add(new admin_setting_configselect(
        'local_aitutor/ollama_embed_model',
        get_string('settings_ollama_embed_model', 'local_aitutor'),
        get_string('settings_ollama_embed_model_desc', 'local_aitutor'),
        'nomic-embed-text',
        [
            'nomic-embed-text'  => 'Nomic Embed Text — ' .
                get_string('embed_nomic_desc',  'local_aitutor'),
            'mxbai-embed-large' => 'MxBai Embed Large — ' .
                get_string('embed_mxbai_desc',  'local_aitutor'),
            'all-minilm'        => 'All MiniLM — ' .
                get_string('embed_minilm_desc', 'local_aitutor'),
        ]
    ));

    // =========================================================================
    // SEZIONE 4 — OpenAI
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_openai',
        '🤖 ' . get_string('settings_heading_openai', 'local_aitutor'),
        get_string('settings_heading_openai_desc', 'local_aitutor')
    ));

    // API Key OpenAI
    $settings->add(new admin_setting_configpasswordunmask(
        'local_aitutor/openai_apikey',
        get_string('settings_openai_apikey', 'local_aitutor'),
        get_string('settings_openai_apikey_desc', 'local_aitutor'),
        ''
    ));

    // Modello OpenAI
    $settings->add(new admin_setting_configselect(
        'local_aitutor/openai_model',
        get_string('settings_openai_model', 'local_aitutor'),
        get_string('settings_openai_model_desc', 'local_aitutor'),
        'gpt-4o-mini',
        [
            'gpt-4o'        => 'GPT-4o — ' .
                get_string('model_gpt4o_desc',      'local_aitutor'),
            'gpt-4o-mini'   => 'GPT-4o Mini — ' .
                get_string('model_gpt4o_mini_desc', 'local_aitutor'),
            'gpt-4-turbo'   => 'GPT-4 Turbo — ' .
                get_string('model_gpt4turbo_desc',  'local_aitutor'),
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo — ' .
                get_string('model_gpt35turbo_desc', 'local_aitutor'),
            'o1-mini'       => 'o1 Mini — ' .
                get_string('model_o1mini_desc',     'local_aitutor'),
            'o1-preview'    => 'o1 Preview — ' .
                get_string('model_o1preview_desc',  'local_aitutor'),
        ]
    ));

    // =========================================================================
    // SEZIONE 5 — Anthropic
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_anthropic',
        '🧠 ' . get_string('settings_heading_anthropic', 'local_aitutor'),
        get_string('settings_heading_anthropic_desc', 'local_aitutor')
    ));

    // API Key Anthropic
    $settings->add(new admin_setting_configpasswordunmask(
        'local_aitutor/anthropic_apikey',
        get_string('settings_anthropic_apikey', 'local_aitutor'),
        get_string('settings_anthropic_apikey_desc', 'local_aitutor'),
        ''
    ));

    // Modello Anthropic
    $settings->add(new admin_setting_configselect(
        'local_aitutor/anthropic_model',
        get_string('settings_anthropic_model', 'local_aitutor'),
        get_string('settings_anthropic_model_desc', 'local_aitutor'),
        'claude-3-5-haiku-latest',
        [
            'claude-3-5-sonnet-latest' => 'Claude 3.5 Sonnet — ' .
                get_string('model_claude35sonnet_desc', 'local_aitutor'),
            'claude-3-5-haiku-latest'  => 'Claude 3.5 Haiku — ' .
                get_string('model_claude35haiku_desc',  'local_aitutor'),
            'claude-3-opus-latest'     => 'Claude 3 Opus — ' .
                get_string('model_claude3opus_desc',    'local_aitutor'),
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet — ' .
                get_string('model_claude3sonnet_desc',  'local_aitutor'),
            'claude-3-haiku-20240307'  => 'Claude 3 Haiku — ' .
                get_string('model_claude3haiku_desc',   'local_aitutor'),
        ]
    ));

    // =========================================================================
    // SEZIONE 6 — Test connessione
    // =========================================================================
    $settings->add(new admin_setting_heading(
        'local_aitutor/heading_test',
        get_string('settings_heading_test', 'local_aitutor'),
        get_string('settings_heading_test_desc', 'local_aitutor')
    ));

    // Bottone test connessione (custom setting)
    $settings->add(new \local_aitutor\admin\setting_test_connection(
        'local_aitutor/test_connection',
        get_string('settings_test_connection', 'local_aitutor'),
        get_string('settings_test_connection_desc', 'local_aitutor')
    ));
}