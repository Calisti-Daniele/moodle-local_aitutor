<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Funzione di upgrade del plugin.
 * Viene chiamata automaticamente da Moodle ad ogni aggiornamento
 * della versione in version.php.
 *
 * @param int $oldversion  Versione precedente del plugin
 * @return bool
 */
function xmldb_local_aitutor_upgrade(int $oldversion): bool {
    global $CFG;

    // Ri-registra sempre i servizi web ad ogni upgrade
    require_once($CFG->dirroot . '/lib/upgradelib.php');
    external_update_descriptions('local_aitutor');

    return true;
}