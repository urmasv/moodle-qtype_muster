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
 * qtype_muster küsimusetüübi klass.
 *
 * Vastutab: küsimuse lisaandmete (ruudustiku mõõtmed, taustapilt,
 * värvide/sümbolite valik) laadimise, salvestamise ja kustutamise eest.
 * Mudel on üles ehitatud qtype_essay ja qtype_multichoice questiontype.php
 * eeskujul.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questiontypebase.php');

class qtype_muster extends question_type {

    /**
     * Kas see küsimusetüüp vajab käsitsi hindamist.
     * (qtype_muster kasutab question.php-s make_behaviour() ülekirjutust,
     * mis sunnib alati qbehaviour_manualgraded kasutamist - see meetod on
     * lisatud dokumentatsiooni/loetavuse huvides.)
     *
     * @return bool
     */
    public function is_manual_graded() {
        return true;
    }

    /**
     * Kas seda küsimusetüüpi saab kasutada juhusliku küsimuse valikus.
     * Loominguline/käsitsi hinnatav küsimus - vaikimisi väljas, nagu essay.
     *
     * @return bool
     */
    public function is_usable_by_random() {
        return false;
    }

    /**
     * Laeb küsimuse lisaandmed (ruudustiku suurus + värvide/piltide valik).
     *
     * @param object $question
     * @return bool
     */
    public function get_question_options($question) {
        global $DB;

        parent::get_question_options($question);

        $question->options = $DB->get_record('qtype_muster_options',
            ['questionid' => $question->id]);

        $question->palette = $DB->get_records('qtype_muster_palette',
            ['questionid' => $question->id], 'sortorder ASC');

        return true;
    }

    /**
     * Salvestab küsimuse lisaandmed (kutsutakse redigeerimisvormi
     * esitamisel).
     *
     * @param object $question andmed edit_muster_form.php-st
     * @return object|bool
     */
    public function save_question_options($question) {
        global $DB;

        $context = $question->context;

        // Ruudustiku mõõtmed.
        $options = $DB->get_record('qtype_muster_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new \stdClass();
            $options->questionid = $question->id;
        }
        $options->gridwidth = $question->gridwidth;
        $options->gridheight = $question->gridheight;
        $options->cellsize = !empty($question->cellsize) ? (int) $question->cellsize : 32;

        if (!empty($options->id)) {
            $DB->update_record('qtype_muster_options', $options);
        } else {
            $DB->insert_record('qtype_muster_options', $options);
        }

        // Taustapilt - ÜKS fail, itemid = küsimuse enda id (nii nagu nt
        // qtype_essay graderinfo puhul). Ei vaja eraldi DB-tabelit ega
        // -välja, kuna faili OLEMASOLU ise (component/filearea/itemid
        // kombinatsioon) on piisav info.
        file_save_draft_area_files(
            $question->backgroundimage,
            $context->id,
            'qtype_muster',
            'background',
            $question->id,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']]
        );

        // Värvide/sümbolite valik.
        $DB->delete_records('qtype_muster_palette', ['questionid' => $question->id]);

        $repeats = $question->paletterepeats ?? 0;
        $sortorder = 0;
        for ($i = 0; $i < $repeats; $i++) {
            $coltype = $question->palette_coltype[$i] ?? '';
            if (!in_array($coltype, ['colour', 'symbol'], true)) {
                continue;
            }

            $colourvalue = null;
            if ($coltype === 'colour') {
                $colourvalue = $question->palette_colourvalue[$i] ?? null;
                if (empty($colourvalue)) {
                    continue;
                }
            }

            $symbolvalue = null;
            if ($coltype === 'symbol') {
                $symbolvalue = trim($question->palette_symbolvalue[$i] ?? '');
                if ($symbolvalue === '') {
                    continue;
                }
            }

            $record = new \stdClass();
            $record->questionid = $question->id;
            $record->sortorder = $sortorder;
            $record->coltype = $coltype;
            $record->colourvalue = $colourvalue;
            $record->symbolvalue = $symbolvalue;
            $record->itemlabel = $question->palette_itemlabel[$i] ?? null;
            $DB->insert_record('qtype_muster_palette', $record);

            $sortorder++;
        }

        return true;
    }

    /**
     * Kustutab küsimuse lisaandmed (küsimuse enda kustutamisel).
     *
     * @param int $questionid
     * @param int $contextid
     */
    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_muster_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_muster_palette', ['questionid' => $questionid]);
        // MÄRKUS: qtype_muster_comments EI kustutata siin, sest need on
        // seotud question_attempts kirjetega (õpilaste tegelikud katsed),
        // mitte küsimuse definitsiooniga endaga - nende eluiga käib koos
        // katsetega (backup/restore ja privacy delete kaudu), mitte küsimuse
        // enda kustutamisega.

        parent::delete_question($questionid, $contextid);
    }

    /**
     * Loeb küsimuse definitsiooni klassi (question.php) sisse.
     *
     * @param object $questiondata
     * @param question_definition $question
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->gridwidth = $questiondata->options->gridwidth;
        $question->gridheight = $questiondata->options->gridheight;
        $question->cellsize = $questiondata->options->cellsize ?? 32;
        $question->palette = $questiondata->palette;
    }
}
