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
 * qtype_muster restore - küsimuse definitsiooni tasand (ruudustiku mõõtmed,
 * taustapildi kõrguse ülekirjutus + palett: värvid ja sümbolid). Struktuur
 * (restore_path_element + process_*() meetodid) järgib qtype_essay
 * restore_qtype_essay_plugin lähtekoodi mustrit (moodle/moodle GitHub).
 *
 * TAUSTAPILDI FAIL ISE: eraldi process_*() koodi pole vaja, kuna
 * get_qtype_fileareas() (backup_qtype_muster_plugin.class.php) deklareerib
 * 'background' faila mappinguga 'question_created' - core'i
 * restore_qtype_plugin baasklass taastab sellise mustriga failid ise,
 * automaatselt (sama mehhanism, mida kasutavad core'i qtype'id oma
 * definitsiooni-tasandi failide jaoks, nt essay 'graderinfo').
 *
 * MÄRKUS - kontrolli enne kasutuselevõttu:
 * {qtype_muster_comments} taastamine (õpilaste katsete kommentaarid) EI
 * OLE siin kaetud - vt backup_qtype_muster_plugin.class.php ülaosa
 * kommentaari.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_qtype_muster_plugin extends restore_qtype_plugin {

    protected function define_question_plugin_structure() {
        $paths = [];

        $paths[] = new restore_path_element('muster', $this->get_pathfor('/muster'));
        $paths[] = new restore_path_element('muster_palette', $this->get_pathfor('/palettes/palette'));

        return $paths;
    }

    /**
     * Taastab qtype_muster_options rea.
     *
     * @param array $data
     */
    public function process_muster($data) {
        global $DB;

        $data = (object) $data;
        $data->questionid = $this->get_new_parentid('question');

        // Kui see küsimus on juba varem taastatud (kategooria jagamine
        // kursuste vahel), ei looda lisaandmeid uuesti.
        $questioncreated = (bool) $this->get_mappingid('question_created',
            $this->get_old_parentid('question'));
        if ($questioncreated) {
            $DB->insert_record('qtype_muster_options', $data);
        }
    }

    /**
     * Taastab ühe qtype_muster_palette rea.
     *
     * @param array $data
     */
    public function process_muster_palette($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->questionid = $this->get_new_parentid('question');

        $questioncreated = (bool) $this->get_mappingid('question_created',
            $this->get_old_parentid('question'));
        if ($questioncreated) {
            $newitemid = $DB->insert_record('qtype_muster_palette', $data);
            $this->set_mapping('qtype_muster_palette', $oldid, $newitemid);
        }
    }
}
