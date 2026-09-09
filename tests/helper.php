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
 * Testiabiklass qtype_muster jaoks, struktuur qtype_essay_test_helper
 * (tests/helper.php) eeskujul.
 *
 * NB: see on ainult minimaalne kohatäide, mitte täielik testikomplekt -
 * ühiktestid tuleb kirjutada eraldi vastavalt Moodle'i PHPUnit juhendile.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_muster_test_helper extends question_test_helper {

    public function get_test_questions() {
        return ['basic'];
    }

    /**
     * Loob lihtsa 3x3 mustri-küsimuse, üks punane ja üks sinine ruut.
     *
     * @return stdClass
     */
    public function make_muster_question_basic() {
        $q = new \stdClass();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Muster - alusnäide';
        $q->questiontext = 'Täida ruudustik.';
        $q->qtype = 'muster';
        $q->gridwidth = 3;
        $q->gridheight = 3;
        $q->palette = [
            (object) ['id' => 1, 'coltype' => 'colour', 'colourvalue' => '#ff0000', 'itemlabel' => 'Punane'],
            (object) ['id' => 2, 'coltype' => 'colour', 'colourvalue' => '#0000ff', 'itemlabel' => 'Sinine'],
        ];

        return $q;
    }
}
