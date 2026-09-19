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
 * qtype_muster küsimuse definitsiooni klass.
 *
 * Struktuur (extends question_with_responses, make_behaviour() sunnib
 * qbehaviour_manualgraded) on otse kontrollitud qtype_essay lähtekoodist
 * (question/type/essay/question.php, moodle/moodle GitHub repo):
 *   class qtype_essay_question extends question_with_responses {
 *       public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
 *           question_engine::load_behaviour_class('manualgraded');
 *           return new qbehaviour_manualgraded($qa, $preferredbehaviour);
 *       }
 *   }
 * qtype_muster järgib sama mustrit, kuna hindamine on samuti puhtalt
 * käsitsi (arvestatud/mittearvestatud), mitte automaatne.
 *
 * VASTUSE SALVESTAMINE: kogu ruudustiku olek salvestatakse ühe qt_var'ina
 * ('gridstate', JSON: {"rida_veerg": paletteid, ...} ainult täidetud ruutude
 * kohta). See väli uueneb Moodle quiz'i tavapärase autosave/lehevahetuse
 * kaudu (samamoodi nagu essay küsimuse tekstiväli) - eraldi AJAX-liidest
 * ruudustiku täitmise enda jaoks EI ehitata, kuna Moodle'i olemasolev
 * autosave mehhanism katab selle vajaduse.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_muster_question extends question_with_responses {

    /** @var int */
    public $gridwidth;

    /** @var int */
    public $gridheight;

    /** @var int ruudu külje pikkus pikslites - õpetaja määratud. */
    public $cellsize;

    /** @var array qtype_muster_palette read (id => stdClass). */
    public $palette;

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        question_engine::load_behaviour_class('manualgraded');
        return new qbehaviour_manualgraded($qa, $preferredbehaviour);
    }

    public function get_expected_data() {
        return ['gridstate' => PARAM_RAW];
    }

    public function get_correct_response() {
        // Loominguline töö - "õiget" vastust core'i mõttes pole, hindab õpetaja.
        return null;
    }

    public function summarise_response(array $response) {
        $state = $this->decode_gridstate($response);
        return get_string('pluginname', 'qtype_muster') . ': ' .
            count($state) . '/' . ($this->gridwidth * $this->gridheight) . ' ruutu täidetud';
    }

    public function is_complete_response(array $response) {
        $state = $this->decode_gridstate($response);
        return count($state) > 0;
    }

    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key(
            $prevresponse, $newresponse, 'gridstate');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('err_paletteempty', 'qtype_muster');
    }

    /**
     * Dekodeerib gridstate JSON-i massiiviks (rida_veerg => paletteid).
     *
     * @param array $response
     * @return array
     */
    public function decode_gridstate(array $response) {
        if (empty($response['gridstate'])) {
            return [];
        }
        $decoded = json_decode($response['gridstate'], true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Lubab juurdepääsu taustapildi failile.
     *
     * Struktuur (component/filearea kontroll, seejärel parent:: fallback)
     * kontrollitud qtype_gapselect (questionbase.php) ja qtype_coderunner
     * (question.php) lähtekoodist - vt ka renderer.php varasemat sama
     * mustrit paleti piltide jaoks.
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param string $component
     * @param string $filearea
     * @param array $args
     * @param bool $forcedownload
     * @return bool
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component === 'qtype_muster' && $filearea === 'background') {
            // Taustapilt pole tundlik - kuvatakse kõigile, kes küsimust näevad.
            return true;
        }
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }
}
