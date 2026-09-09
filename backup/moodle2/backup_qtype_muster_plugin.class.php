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
 * qtype_muster backup - küsimuse definitsiooni tasand (ruudustiku mõõtmed,
 * taustapilt, palett: värvid ja sümbolid). Struktuur on otse kontrollitud
 * qtype_essay backup_qtype_essay_plugin lähtekoodist (moodle/moodle GitHub):
 *   $essay->set_source_table('qtype_essay_options',
 *       array('questionid' => backup::VAR_PARENTID));
 *
 * TAUSTAPILDI FAIL: itemid = küsimuse enda id (sama muster mis
 * qtype_essay 'graderinfo' failialal), mistõttu piisab
 * get_qtype_fileareas()'ist mappinguga 'question_created' - core'i
 * question-backup mehhanism haldab sellise itemid-mustriga failide
 * varundamise/taastamise ise, ilma et peaks eraldi process_*() meetodit
 * kirjutama (erinevalt paleti ridadest, mis kasutasid oma tabeli rea id-d
 * ja vajasid seetõttu eraldi mappingut - vt see funktsioon eemaldati
 * koos pilditoe eemaldamisega paletist).
 *
 * MÄRKUS: paleti enda värvid/sümbolid on lihttekst, mistõttu neil pole
 * eraldi failide annotate_files() vajadust.
 *
 * TÄHTIS LAHTINE KÜSIMUS (kontrolli enne kasutuselevõttu!):
 * See fail katab AINULT küsimuse definitsiooni (options + palette +
 * taustapilt), mis on seotud küsimuste panga küsimuse endaga. See EI KATA
 * {qtype_muster_comments} tabelit, kuna need read on seotud konkreetsete
 * ÕPILASTE KATSETEGA (question_attempts), mitte küsimuse definitsiooniga -
 * nende varundamine/taastamine käib teistsuguse mehhanismi kaudu
 * (backup_qtype_plugin per-attempt custom-fields hook, nt
 * add_question_attempts_custom_fields() vms - täpne API tuleb üle
 * kontrollida mõne teise, per-attempt-lisaandmetega qtype näitel, nt
 * qtype_pmatch või qtype_coderunner, enne kui seda siia lisada). Kuni see
 * on lahendatud, LÄHEVAD KOMMENTAARID KADUMA kursuse varundamisel/
 * taastamisel.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class backup_qtype_muster_plugin extends backup_qtype_plugin {

    protected function define_question_plugin_structure() {
        $plugin = $this->get_plugin_element(null, '/questiontype', 'muster');

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        // qtype_muster_options - üks rida küsimuse kohta.
        $options = new backup_nested_element('muster', ['id'],
            ['gridwidth', 'gridheight', 'cellsize']);
        $pluginwrapper->add_child($options);
        $options->set_source_table('qtype_muster_options', ['questionid' => backup::VAR_PARENTID]);

        // qtype_muster_palette - mitu rida küsimuse kohta.
        $palettes = new backup_nested_element('palettes');
        $palette = new backup_nested_element('palette', ['id'],
            ['sortorder', 'coltype', 'colourvalue', 'symbolvalue', 'itemlabel']);
        $pluginwrapper->add_child($palettes);
        $palettes->add_child($palette);
        $palette->set_source_table('qtype_muster_palette', ['questionid' => backup::VAR_PARENTID]);
        $palette->annotate_ids('qtype_muster_palette', 'id');

        return $plugin;
    }

    /**
     * Failialad, mida see qtype kasutab (küsimuse definitsiooni tasandil).
     * Väärtus on mappingu nimi, mida kasutada itemid'i taastamiseks -
     * 'question_created' tähendab, et itemid ISE ongi küsimuse id.
     *
     * @return array
     */
    public static function get_qtype_fileareas() {
        return [
            'background' => 'question_created',
        ];
    }
}
