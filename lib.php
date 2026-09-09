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
 * qtype_muster lib.php - vastutab taustapildi failijuurdepääsu eest.
 *
 * Struktuur järgib sama mustrit, mida kasutavad teised qtype'id oma
 * definitsiooni-tasandi failide jaoks (kontrollitud nt qtype_varnumericset
 * lib.php-st moodle/moodle-ökosüsteemis): kohalik funktsioon delegeerib
 * tegeliku loa kontrolli ja faili väljastamise core'i question_pluginfile()-le.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Väljastab qtype_muster failid (praegu ainult taustapildi failiala).
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function qtype_muster_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
        array $options = []) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');

    if ($filearea !== 'background') {
        return false;
    }

    question_pluginfile($course, $context, 'qtype_muster', $filearea, $args, $forcedownload, $options);
}
