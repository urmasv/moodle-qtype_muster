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
 * qtype_muster versiooniandmed.
 *
 * "Muster" on ruudustik-tüüpi küsimus, kus õpilane täidab õpetaja määratud
 * suurusega ruudustikku ükshaaval värvide ja/või kirjamärkidega, valikulise
 * taustapildi peal.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// TODO: uuenda versiooninumbrit iga muudatusega (YYYYMMDDXX formaadis).
$plugin->version   = 2026090806;
// Moodle 5.0.0 väljalaskeversioon on 2025041400 (kontrollitud moodledev.io andmetest).
// Sihtkeskkond on Moodle 5.2, aga alammääraks on siin seatud 5.0, kuna plugin ei kasuta
// (praeguses kavandis) 5.1/5.2-spetsiifilisi API-sid.
$plugin->requires  = 2025041400;
$plugin->component = 'qtype_muster';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1.0';
