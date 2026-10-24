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
            ['questionid' => $question->id], '*', MUST_EXIST);

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
        // XML-impordil ilma taustapildita välja pole.
        if (isset($question->backgroundimage)) {
            file_save_draft_area_files(
                $question->backgroundimage,
                $context->id,
                'qtype_muster',
                'background',
                $question->id,
                ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['web_image']]
            );
        }

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

    /**
     * Viib taustapildi koos küsimusega uude konteksti (vt core qtype_essay graderinfo).
     *
     * @param int $questionid
     * @param int $oldcontextid
     * @param int $newcontextid
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid,
            'qtype_muster', 'background', $questionid);
    }

    /**
     * Kustutab taustapildi koos küsimusega.
     *
     * @param int $questionid
     * @param int $contextid
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_muster', 'background', $questionid);
    }

    /**
     * Ekspordib küsimuse Moodle XML-vormingusse.
     *
     * @param object $question
     * @param qformat_xml $format
     * @param mixed $extra
     * @return string
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $options = $question->options;
        $output = '';
        $output .= "    <gridwidth>" . (int) $options->gridwidth . "</gridwidth>\n";
        $output .= "    <gridheight>" . (int) $options->gridheight . "</gridheight>\n";
        $output .= "    <cellsize>" . (int) $options->cellsize . "</cellsize>\n";

        $output .= "    <palettes>\n";
        foreach ($question->palette as $item) {
            $output .= "      <palette>\n";
            $output .= "        <coltype>" . $format->xml_escape($item->coltype) . "</coltype>\n";
            $output .= "        <colourvalue>" . $format->xml_escape((string) $item->colourvalue) . "</colourvalue>\n";
            $output .= "        <symbolvalue>" . $format->xml_escape((string) $item->symbolvalue) . "</symbolvalue>\n";
            $output .= "        <itemlabel>" . $format->xml_escape((string) $item->itemlabel) . "</itemlabel>\n";
            $output .= "      </palette>\n";
        }
        $output .= "    </palettes>\n";

        // Taustapilt (base64), nagu core qtype_ddimageortext bgimage puhul.
        $fs = get_file_storage();
        $files = $fs->get_area_files($question->contextid, 'qtype_muster', 'background',
            $question->id, 'filename', false);
        if ($files) {
            $output .= "    <background>\n" . $format->write_files($files) . "    </background>\n";
        }

        return $output;
    }

    /**
     * Impordib küsimuse Moodle XML-vormingust. Tagastab vormiandmetega sama kujuga
     * objekti, mida save_question_options() ootab.
     *
     * @param mixed $data
     * @param object $question
     * @param qformat_xml $format
     * @param mixed $extra
     * @return object|bool
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'muster') {
            return false;
        }

        $qo = $format->import_headers($data);
        $qo->qtype = 'muster';

        $qo->gridwidth = (int) $format->getpath($data, ['#', 'gridwidth', 0, '#'], 4);
        $qo->gridheight = (int) $format->getpath($data, ['#', 'gridheight', 0, '#'], 4);
        $qo->cellsize = (int) $format->getpath($data, ['#', 'cellsize', 0, '#'], 32);

        $qo->palette_coltype = [];
        $qo->palette_colourvalue = [];
        $qo->palette_symbolvalue = [];
        $qo->palette_itemlabel = [];
        $items = $format->getpath($data, ['#', 'palettes', 0, '#', 'palette'], []);
        foreach ($items as $i => $item) {
            $qo->palette_coltype[$i] = $format->getpath($item, ['#', 'coltype', 0, '#'], '');
            $qo->palette_colourvalue[$i] = $format->getpath($item, ['#', 'colourvalue', 0, '#'], '');
            $qo->palette_symbolvalue[$i] = $format->getpath($item, ['#', 'symbolvalue', 0, '#'], '');
            $qo->palette_itemlabel[$i] = $format->getpath($item, ['#', 'itemlabel', 0, '#'], '');
        }
        $qo->paletterepeats = count($items);

        $filexml = $format->getpath($data, ['#', 'background', 0, '#', 'file'], []);
        $draftitemid = $format->import_files_as_draft($filexml);
        if ($draftitemid) {
            $qo->backgroundimage = $draftitemid;
        }

        return $qo;
    }
}
