<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_aitutor\ai;

defined('MOODLE_INTERNAL') || die();

/**
 * Interfaccia base per tutti i provider AI.
 *
 * Ogni provider (OpenAI, Anthropic, Ollama) deve implementare
 * questi metodi. In questo modo il resto del plugin non sa
 * quale provider sta usando — basta chiamare i metodi dell'interfaccia.
 */
interface provider_interface {

    /**
     * Invia una conversazione al provider e ottieni la risposta.
     *
     * @param array  $messages  Storia conversazione nel formato:
     *                          [['role' => 'user', 'content' => '...'],
     *                           ['role' => 'assistant', 'content' => '...'], ...]
     * @param string $systemprompt  Prompt di sistema (istruzioni AI)
     * @param array  $options   Opzioni aggiuntive:
     *                          - maxtokens (int)
     *                          - temperature (float)
     * @return array  [
     *                  'content'    => string,  // Risposta AI
     *                  'tokens_in'  => int,     // Token input usati
     *                  'tokens_out' => int,     // Token output usati
     *                  'model'      => string,  // Modello usato
     *                ]
     * @throws \moodle_exception  In caso di errore API
     */
    public function chat(array $messages, string $systemprompt, array $options = []): array;

    /**
     * Genera un embedding vettoriale per un testo.
     * Usato per il RAG (Retrieval Augmented Generation).
     *
     * @param string $text  Testo da vettorializzare
     * @return float[]      Array di float (il vettore)
     * @throws \moodle_exception
     */
    public function embed(string $text): array;

    /**
     * Verifica che il provider sia raggiungibile e configurato.
     * Usato nella pagina admin per validare le impostazioni.
     *
     * @return array [
     *                 'success' => bool,
     *                 'message' => string,
     *                 'models'  => string[],  // Modelli disponibili
     *               ]
     */
    public function test_connection(): array;

    /**
     * Restituisce la lista dei modelli disponibili per questo provider.
     * Usato nei form di configurazione.
     *
     * @return array ['model_id' => 'Nome leggibile — descrizione', ...]
     */
    public function get_available_models(): array;

    /**
     * Restituisce la lista dei modelli embedding disponibili.
     *
     * @return array ['model_id' => 'Nome leggibile — descrizione', ...]
     */
    public function get_embedding_models(): array;

    /**
     * Restituisce il nome leggibile del provider.
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Restituisce la descrizione del provider con istruzioni di setup.
     * Mostrata nella pagina admin per guidare l'amministratore.
     *
     * @return string  HTML
     */
    public function get_description(): string;
}