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
 * Language strings for AI Personal Assistant.
 *
 * @package    local_aitutor
 * @copyright  2026 Daniele Calisti
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin
$string['pluginname']              = 'Assistente AI Personale';

// Capabilities
$string['aitutor:use']             = 'Usa l\'Assistente AI Personale';
$string['aitutor:viewreports']     = 'Visualizza report Assistente AI';
$string['aitutor:manage']          = 'Gestisci impostazioni Assistente AI';

// Widget UI
$string['widget_title']            = 'Assistente AI';
$string['widget_open']             = 'Apri Assistente AI';
$string['widget_close']            = 'Chiudi Assistente AI';
$string['widget_placeholder']      = 'Chiedimi qualcosa sui tuoi corsi...';
$string['widget_send']             = 'Invia';
$string['widget_thinking']         = 'Sto elaborando...';
$string['widget_clear']            = 'Cancella conversazione';
$string['widget_clear_confirm']    = 'Sei sicuro di voler cancellare la conversazione?';
$string['widget_fullscreen']       = 'Apri a schermo intero';
$string['widget_error']            = 'Si è verificato un errore. Riprova.';
$string['widget_welcome']          = 'Ciao {$a->firstname}! Sono il tuo assistente AI personale. Conosco tutti i tuoi corsi, voti e scadenze. Come posso aiutarti?';

// Domande esempio
$string['suggestion_courses']      = 'Quali corsi sto seguendo?';
$string['suggestion_progress']     = 'Qual è il mio progresso generale?';
$string['suggestion_deadlines']    = 'Ho scadenze imminenti?';
$string['suggestion_grades']       = 'Come stanno andando i miei voti?';
$string['suggestion_certificates'] = 'Quali certificati ho ottenuto?';

// Admin settings
$string['settings_provider']              = 'Provider AI';
$string['settings_provider_desc']         = 'Seleziona il provider AI che alimenta l\'assistente.';
$string['settings_ollama_url']            = 'URL Ollama';
$string['settings_ollama_url_desc']       = 'URL base della tua istanza Ollama. Usa http://ollama:11434 se gira in Docker sulla stessa rete.';
$string['settings_ollama_model']          = 'Modello Chat Ollama';
$string['settings_ollama_model_desc']     = 'Il modello LLM da usare per la conversazione.';
$string['settings_ollama_embed_model']    = 'Modello Embedding Ollama';
$string['settings_ollama_embed_model_desc'] = 'Il modello da usare per generare gli embedding.';
$string['settings_openai_apikey']         = 'API Key OpenAI';
$string['settings_openai_apikey_desc']    = 'La tua API key OpenAI da platform.openai.com';
$string['settings_openai_model']          = 'Modello OpenAI';
$string['settings_anthropic_apikey']      = 'API Key Anthropic';
$string['settings_anthropic_apikey_desc'] = 'La tua API key Anthropic da console.anthropic.com';
$string['settings_anthropic_model']       = 'Modello Anthropic';
$string['settings_maxtokens']             = 'Token massimi per risposta';
$string['settings_maxtokens_desc']        = 'Token massimi che l\'AI può usare per risposta. Tra 100 e 4000.';
$string['settings_enabled_roles']         = 'Abilita assistente per';
$string['settings_enabled_roles_desc']    = 'Seleziona quali ruoli possono usare l\'assistente AI.';
$string['settings_test_connection']       = 'Testa connessione';

// Errori
$string['error_noprovider']        = 'Nessun provider AI configurato. Contatta l\'amministratore.';
$string['error_apikey']            = 'API key non valida o mancante.';
$string['error_ratelimit']         = 'Limite richieste raggiunto. Attendi e riprova.';
$string['error_unavailable']       = 'Servizio AI temporaneamente non disponibile.';
$string['error_disabled']          = 'L\'Assistente AI non è abilitato per il tuo account.';

// Privacy
$string['privacy:metadata:local_aitutor_sessions']         = 'Dati delle sessioni dell\'Assistente AI';
$string['privacy:metadata:local_aitutor_messages']         = 'Messaggi scambiati con l\'Assistente AI';
$string['privacy:metadata:local_aitutor_sessions:userid']  = 'L\'utente proprietario della sessione';
$string['privacy:metadata:local_aitutor_messages:message'] = 'Il contenuto del messaggio';
$string['privacy:metadata:local_aitutor_messages:role']    = 'Se inviato dall\'utente o dall\'AI';

// Settings headings
$string['settings_heading_general']          = 'Impostazioni Generali';
$string['settings_heading_general_desc']     = 'Configurazione globale dell\'Assistente AI Personale.';
$string['settings_heading_provider']         = 'Provider AI';
$string['settings_heading_provider_desc']    = 'Seleziona e configura il provider AI che alimenta l\'assistente.';
$string['settings_heading_ollama']           = 'Configurazione Ollama';
$string['settings_heading_ollama_desc']      = 'Ollama esegue modelli AI in locale — gratuito, privato, nessuna API key richiesta. Installa da ollama.com o avvia con Docker.';
$string['settings_heading_openai']           = 'Configurazione OpenAI';
$string['settings_heading_openai_desc']      = 'Usa i modelli GPT di OpenAI. Richiede una API key da platform.openai.com. L\'utilizzo è a pagamento per token.';
$string['settings_heading_anthropic']        = 'Configurazione Anthropic';
$string['settings_heading_anthropic_desc']   = 'Usa i modelli Claude di Anthropic. Richiede una API key da console.anthropic.com. L\'utilizzo è a pagamento per token.';
$string['settings_heading_test']             = 'Test Connessione';
$string['settings_heading_test_desc']        = 'Testa la connessione al provider AI selezionato prima di salvare.';

// General settings
$string['settings_enabled']                  = 'Abilita Assistente AI';
$string['settings_enabled_desc']             = 'Mostra il widget Assistente AI su tutte le pagine Moodle per gli utenti autenticati.';
$string['settings_temperature_desc']         = 'Controlla la creatività delle risposte. Più basso = più preciso. Più alto = più creativo.';

// Provider labels
$string['provider_ollama_label']             = 'Gratuito, self-hosted, nessuna API key';
$string['provider_openai_label']             = 'GPT-4o, GPT-4o Mini — API a pagamento';
$string['provider_anthropic_label']          = 'Claude 3.5 Sonnet/Haiku — API a pagamento';

// Test connection
$string['settings_test_connection_desc']     = 'Clicca per verificare la connessione al provider selezionato. Salva prima le impostazioni.';

// Modelli Ollama
$string['model_llama32_desc']                = 'Veloce ed efficiente. Ottimo per uso quotidiano. Consigliato per la maggior parte dei setup.';
$string['model_llama32_1b_desc']             = 'Ultra-leggero. Ideale per hardware molto limitato.';
$string['model_llama31_desc']                = 'Più grande e capace. Richiede ~8GB RAM.';
$string['model_llama31_70b_desc']            = 'Molto potente. Richiede ~40GB RAM. Solo per server high-end.';
$string['model_mistral_desc']                = 'Ottimo ragionamento, risposte veloci. Ottima alternativa a Llama.';
$string['model_mistral_nemo_desc']           = 'Variante Mistral più grande. Migliore su task complessi.';
$string['model_gemma2_desc']                 = 'Modello open di Google. Ottimo nel seguire istruzioni.';
$string['model_gemma2_2b_desc']              = 'Piccolo modello Google. Ideale per ambienti con risorse limitate.';
$string['model_phi3_desc']                   = 'Modello compatto di Microsoft. Sorprendentemente capace.';
$string['model_phi3_medium_desc']            = 'Variante Phi più grande. Miglior ragionamento, più risorse.';
$string['model_qwen25_desc']                 = 'Modello multilingua di Alibaba. Eccellente per contenuti non in inglese.';
$string['model_qwen25_14b_desc']             = 'Qwen più grande. Ottime capacità multilingua e di ragionamento.';
$string['model_deepseek_r1_desc']            = 'Modello con forte ragionamento. Ottimo per domande analitiche.';

// Embedding models
$string['embed_nomic_desc']                  = '768 dimensioni. Miglior bilanciamento qualità/velocità. Consigliato.';
$string['embed_mxbai_desc']                  = '1024 dimensioni. Qualità superiore, leggermente più lento.';
$string['embed_minilm_desc']                 = '384 dimensioni. Molto veloce, qualità inferiore. Per prototipi.';

// OpenAI models
$string['model_gpt4o_desc']                  = 'Modello OpenAI più capace. Ideale per ragionamenti complessi.';
$string['model_gpt4o_mini_desc']             = 'Veloce ed economico. Consigliato per la maggior parte dei casi.';
$string['model_gpt4turbo_desc']              = 'GPT-4 precedente generazione. Molto capace ma più costoso.';
$string['model_gpt35turbo_desc']             = 'Veloce ed economico. Ottimo per task semplici.';
$string['model_o1mini_desc']                 = 'Focalizzato sul ragionamento. Ottimo per matematica e logica.';
$string['model_o1preview_desc']              = 'Modello di ragionamento più potente di OpenAI.';

// Embedding OpenAI
$string['embed_openai_small_desc']           = '1536 dimensioni. Veloce ed economico. Consigliato.';
$string['embed_openai_large_desc']           = '3072 dimensioni. Qualità superiore. Più costoso.';
$string['embed_openai_ada_desc']             = 'Modello legacy. Usa small/large invece.';

// Anthropic models
$string['model_claude35sonnet_desc']         = 'Miglior modello Claude. Eccellente ragionamento e scrittura.';
$string['model_claude35haiku_desc']          = 'Claude veloce ed economico. Ottimo per la chat.';
$string['model_claude3opus_desc']            = 'Claude 3 più potente. Molto costoso.';
$string['model_claude3sonnet_desc']          = 'Modello Claude 3 bilanciato.';
$string['model_claude3haiku_desc']           = 'Claude 3 più veloce ed economico.';

// Temperature
$string['temperature_precise']               = '0.0 — Preciso (deterministico)';
$string['temperature_focused']               = '0.3 — Focalizzato';
$string['temperature_balanced']              = '0.7 — Bilanciato (consigliato)';
$string['temperature_creative']              = '1.0 — Creativo';
$string['temperature_wild']                  = '1.5 — Molto creativo';

// Misc
$string['error_empty_message']               = 'Il messaggio non può essere vuoto.';

// Ollama connection strings
$string['ollama_connected']                  = 'Connesso con successo. {$a->count} modello/i disponibile/i.';
$string['ollama_connected_nomodels']         = 'Connesso ma nessun modello trovato. Esegui: ollama pull llama3.2';
$string['ollama_connection_failed']          = 'Connessione fallita: {$a->error}';
$string['openai_connected']                  = 'Connesso. {$a->count} modello/i GPT disponibile/i.';
$string['anthropic_connected']               = 'Connesso con successo.';

// Privacy metadata
$string['privacy:metadata:local_aitutor_sessions']            = 'Archivia le sessioni di conversazione dell\'Assistente AI per ogni utente.';
$string['privacy:metadata:local_aitutor_sessions:userid']     = 'L\'ID dell\'utente proprietario della sessione.';
$string['privacy:metadata:local_aitutor_messages']            = 'Archivia i messaggi scambiati con l\'Assistente AI.';
$string['privacy:metadata:local_aitutor_messages:sessionid']  = 'La sessione a cui appartiene il messaggio.';
$string['privacy:metadata:local_aitutor_messages:role']       = 'Se il messaggio è stato inviato dall\'utente o dall\'AI.';
$string['privacy:metadata:local_aitutor_messages:message']    = 'Il contenuto completo del messaggio.';
$string['privacy:metadata:timecreated']                       = 'L\'ora di creazione del record.';
$string['privacy:metadata:timemodified']                      = 'L\'ora dell\'ultima modifica del record.';
$string['privacy:metadata:tokencount']                        = 'Numero di token AI utilizzati.';
$string['privacy:metadata:ai_provider']                       = 'Quando si usano provider AI esterni (OpenAI, Anthropic), i messaggi e il contesto del corso vengono inviati alla loro API. Consulta le loro privacy policy.';
$string['privacy:metadata:ai_provider:message']               = 'Il messaggio dell\'utente inviato al provider AI.';
$string['privacy:metadata:ai_provider:systemprompt']          = 'Il system prompt con il contesto del corso inviato al provider AI.';

// ── Diagnostica ──────────────────────────────────────────────────────────────
$string['diag_php_version']                = 'Versione PHP';
$string['diag_php_version_ok']             = 'PHP {$a->version} — OK';
$string['diag_php_version_error']          = 'PHP {$a->current} trovato, richiesto {$a->required}+.';
$string['diag_php_version_fix']            = 'Aggiorna PHP alla versione {$a->required} o superiore.';

$string['diag_php_extensions']             = 'Estensioni PHP';
$string['diag_php_extensions_ok']          = 'Tutte le estensioni richieste sono installate.';
$string['diag_php_extensions_error']       = 'Estensioni mancanti: {$a->missing}';
$string['diag_php_extensions_fix']         = 'Installa le estensioni mancanti: sudo apt install php-{$a->missing}';

$string['diag_plugin_enabled']             = 'Stato Plugin';
$string['diag_plugin_enabled_ok']          = 'Il plugin è abilitato.';
$string['diag_plugin_enabled_warning']     = 'Il plugin è installato ma non abilitato.';
$string['diag_plugin_enabled_fix']         = 'Vai nelle Impostazioni e abilita l\'Assistente AI.';

$string['diag_capabilities']              = 'Permessi Utenti';
$string['diag_capabilities_ok']           = 'Permessi configurati. {$a->count} utente/i può usare l\'assistente.';
$string['diag_capabilities_warning']      = 'Il ruolo "studente" non ha il permesso di usare l\'assistente.';
$string['diag_capabilities_fix']          = 'Clicca "Correggi automaticamente" oppure vai in Amministrazione → Utenti → Permessi → Ruoli → Studente → local/aitutor:use → Consenti';

$string['diag_services']                  = 'Web Services';
$string['diag_services_ok']               = 'Tutti i {$a->count} web service registrati correttamente.';
$string['diag_services_error']            = 'Servizi mancanti: {$a->missing}';
$string['diag_services_fix']              = 'Clicca "Correggi automaticamente" per ri-registrare i servizi, oppure vai in Amministrazione → Notifiche.';

$string['diag_provider_config']           = 'Configurazione Provider';
$string['diag_provider_config_ok']        = '{$a->provider} è configurato.';
$string['diag_provider_config_fix']       = 'Vai nelle Impostazioni e completa la configurazione del provider.';
$string['diag_provider_no_url']           = 'URL di Ollama non impostato.';
$string['diag_provider_no_model']         = 'Nessun modello chat selezionato.';
$string['diag_provider_no_apikey']        = 'API key mancante.';
$string['diag_provider_invalid_apikey']   = 'Formato API key non valido.';

$string['diag_provider_connection']       = 'Connessione Provider';
$string['diag_provider_connection_ok']    = '{$a->provider} connesso. Modelli: {$a->models}';

$string['diag_http_security']             = 'Sicurezza HTTP Moodle';
$string['diag_http_security_ok']          = 'L\'URL di Ollama è consentita dalle impostazioni di sicurezza.';
$string['diag_http_security_error']       = 'Moodle sta bloccando le richieste a {$a->host}:{$a->port}.';
$string['diag_http_security_fix']         = 'Clicca "Correggi automaticamente" oppure vai in Amministrazione → Sicurezza → Sicurezza HTTP e aggiungi {$a->host} agli host consentiti e {$a->port} alle porte consentite.';

$string['diag_fix_automatically']         = 'Correggi automaticamente';
$string['diag_fix_blocked']               = 'Moodle sta bloccando questo URL. Vai in Amministrazione → Sicurezza → Sicurezza HTTP e aggiungi host e porta alla lista consentiti.';
$string['diag_fix_refused_ollama']        = 'Ollama non è in esecuzione o non è raggiungibile. Assicurati che Ollama sia installato e avviato: systemctl start ollama (Linux) o ollama serve (macOS).';
$string['diag_fix_refused_openai']        = 'Impossibile raggiungere le API OpenAI. Controlla la connessione internet e il firewall.';
$string['diag_fix_refused_anthropic']     = 'Impossibile raggiungere le API Anthropic. Controlla la connessione internet e il firewall.';
$string['diag_fix_apikey']                = 'Verifica che la tua API key sia corretta e non scaduta. Assicurati che il pagamento sia abilitato sul tuo account.';
$string['diag_fix_ratelimit']             = 'Hai superato il limite di richieste API. Attendi qualche minuto e riprova, o aggiorna il tuo piano.';
$string['diag_fix_generic']               = 'Controlla le impostazioni del provider e la connessione internet.';

$string['diag_fix_http_security_ok']      = 'Sicurezza HTTP aggiornata: {$a->host} aggiunto agli host consentiti, porta {$a->port} aggiunta alle porte consentite.';
$string['diag_fix_services_ok']           = 'Web service ri-registrati con successo.';
$string['diag_fix_capabilities_ok']       = 'Permesso "local/aitutor:use" assegnato al ruolo studente.';

$string['diag_summary_ok']                = 'Tutto configurato correttamente. L\'Assistente AI è pronto all\'uso!';
$string['diag_summary_warning']           = '{$a->count} avviso/i trovato/i. L\'assistente funziona ma alcune impostazioni richiedono attenzione.';
$string['diag_summary_error']             = '{$a->count} errore/i trovato/i. Correggili prima di usare l\'assistente.';

// Banner
$string['diag_banner_title_ok']      = 'Assistente AI pronto';
$string['diag_banner_title_warning'] = 'Assistente AI richiede attenzione';
$string['diag_banner_title_error']   = 'Assistente AI non configurato';
$string['diag_banner_action']        = 'Vedi diagnostica e correggi';
$string['diag_banner_details']       = 'Vedi dettagli';

// Pagina diagnostica
$string['diagnostics_title']         = 'Assistente AI — Diagnostica';
$string['diag_status_ready']         = '✅ Tutto funziona';
$string['diag_status_warning']       = '⚠️ Attenzione richiesta';
$string['diag_status_error']         = '❌ Configurazione necessaria';
$string['diag_rerun']                = 'Riesegui diagnostica';
$string['diag_goto_settings']        = 'Vai alle impostazioni';
$string['diag_running']              = 'Diagnostica in corso...';
$string['diag_fix_all']              = 'Correggi tutto automaticamente';
