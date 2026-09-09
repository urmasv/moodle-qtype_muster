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
 * qtype_muster admini seaded.
 *
 * See fail laetakse automaatselt Moodle core'i poolt lehele
 * Site administration > Plugins > Question types > Muster
 * (leht 'qtypesettingmuster' - $settings muutuja on juba admin_settingpage
 * instants, mida siin ainult täidetakse; struktuur kontrollitud
 * qtype_stack/settings.php lähtekoodist).
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'qtype_muster/maxgriddimension',
        get_string('maxgriddimension', 'qtype_muster'),
        get_string('maxgriddimension_desc', 'qtype_muster'),
        30,
        PARAM_INT
    ));

    // "Halda baasvalikuid" on eraldi leht (mitte lihtne väli), kuna tegu on
    // struktureeritud korduvate kirjetega (nimega baasvalikud + nende
    // värvid/sümbolid) - see ei mahu admin_setting_* raamistikku, seega
    // lisame lihtsalt lingi. Leht ise kontrollib õigust otse
    // (moodle/question:config), mitte $ADMIN puu kaudu.
    $manageurl = new moodle_url('/question/type/muster/managepresets.php');
    $settings->add(new admin_setting_description(
        'qtype_muster/managepresetslink',
        get_string('managepresets', 'qtype_muster'),
        html_writer::link($manageurl, get_string('managepresets_linktext', 'qtype_muster'))
    ));
}
