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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * qtype_muster uuendussammud.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_qtype_muster_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026090603) {
        $table = new xmldb_table('qtype_muster_palette');
        $field = new xmldb_field('symbolvalue', XMLDB_TYPE_CHAR, '20', null, false, false, null, 'colourvalue');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026090603, 'qtype', 'muster');
    }

    if ($oldversion < 2026090700) {
        $table = new xmldb_table('qtype_muster_presets');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($table);
        }

        $table = new xmldb_table('qtype_muster_preset_items');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('presetid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0);
            $table->add_field('coltype', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'colour');
            $table->add_field('colourvalue', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('symbolvalue', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('itemlabel', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('presetid', XMLDB_KEY_FOREIGN, ['presetid'], 'qtype_muster_presets', ['id']);
            $table->add_index('presetid_sortorder', XMLDB_INDEX_NOTUNIQUE, ['presetid', 'sortorder']);
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026090700, 'qtype', 'muster');
    }

    if ($oldversion < 2026090800) {
        $table = new xmldb_table('qtype_muster_options');
        $field = new xmldb_field('gridimageheight', XMLDB_TYPE_INTEGER, '6', null, false, false, null, 'gridheight');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026090800, 'qtype', 'muster');
    }

    if ($oldversion < 2026090801) {
        // gridimageheight eemaldati - õpetaja käsitsi kõrguse ülekirjutust
        // ei olnud tegelikult küsitud/vajatud. Taustapildi kõrgus arvutatakse
        // alati automaatselt pildi enda proportsioonist (vt renderer.php).
        $table = new xmldb_table('qtype_muster_options');
        $field = new xmldb_field('gridimageheight', XMLDB_TYPE_INTEGER, '6', null, false, false, null, 'gridheight');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026090801, 'qtype', 'muster');
    }

    if ($oldversion < 2026090803) {
        // cellsize: ruudu külje pikkus pikslites. Ruudustiku suurus
        // (nii taustapildiga kui ilma) tuletatakse sellest, mitte
        // taustapildi enda mõõtmetest.
        $table = new xmldb_table('qtype_muster_options');
        $field = new xmldb_field('cellsize', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 32, 'gridheight');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026090803, 'qtype', 'muster');
    }

    return true;
}
