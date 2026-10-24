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
        } else {
            // Küsimus seoti olemasolevaga (identiteedihäsh klappis), seega on palett sama.
            // Kaardistus on vaja katsete gridstate'i ümberkodeerimiseks (recode_response).
            $newitemid = $DB->get_field('qtype_muster_palette', 'id',
                ['questionid' => $data->questionid, 'sortorder' => $data->sortorder]);
            if ($newitemid) {
                $this->set_mapping('qtype_muster_palette', $oldid, $newitemid);
            }
        }
    }

    /**
     * Kodeerib katse vastuses ('gridstate') vanad paleti id-d ümber taastatud id-deks.
     * Sama põhimõte nagu core qtype_match/multichoice recode_response().
     *
     * @param int $questionid
     * @param int $sequencenumber
     * @param array $response
     * @return array
     */
    public function recode_response($questionid, $sequencenumber, array $response) {
        if (!array_key_exists('gridstate', $response) || $response['gridstate'] === '') {
            return $response;
        }
        $state = json_decode($response['gridstate'], true);
        if (!is_array($state)) {
            return $response;
        }
        $newstate = [];
        foreach ($state as $cell => $paletteid) {
            $newid = $this->get_mappingid('qtype_muster_palette', $paletteid);
            $newstate[$cell] = $newid ? (string) $newid : $paletteid;
        }
        // Objektina, et tühi olek jääks kujule {} (JS eeldab objekti).
        $response['gridstate'] = json_encode((object) $newstate);
        return $response;
    }

    /**
     * Lisab paleti backup-andmetest küsimuse andmestikku, et identiteedihäsh vastaks
     * get_question_options() tulemusele ($question->palette). Vt core qtype_match.
     *
     * @param array $backupdata
     * @return \stdClass
     */
    public static function convert_backup_to_questiondata(array $backupdata): \stdClass {
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
        $palette = array_map(
            fn($item) => (object) $item,
            $backupdata['plugin_qtype_muster_question']['palettes']['palette'] ?? [],
        );
        usort($palette, fn($a, $b) => (int) $a->sortorder <=> (int) $b->sortorder);
        $questiondata->palette = $palette;
        return $questiondata;
    }

    /**
     * Väljad, mis jäetakse identiteedihäshist välja (id-d ei kattu saitide vahel).
     *
     * @return array
     */
    protected function define_excluded_identity_hash_fields(): array {
        return [
            '/palette/id',
            '/palette/questionid',
        ];
    }
}
