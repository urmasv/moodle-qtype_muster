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
 * qtype_muster redigeerimisvorm.
 *
 * Struktuur (extends question_edit_form, override definition_inner())
 * järgib Moodle'i formslib API standardmustrit. Korduvad palette-elemendid
 * kasutavad repeat_elements().
 *
 * MÄRKUS: paleti pildivalik (filemanager korduva elemendi sees) eemaldati
 * teadlikult - see osutus Moodle'is habraks (filemanager repeat_elements()
 * sees ei toiminud usaldusväärselt). Palett toetab seetõttu ainult värvi ja
 * kirjamärki/sümbolit. Taustapilt (backgroundimage allpool) on aga ÜKS,
 * mitte-korduv failihaldur, mis ei puutu kokku sama probleemiga.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_muster_edit_form extends question_edit_form {

    /** @var int mitu palette-rida vaikimisi kuvada tühja/uue küsimuse jaoks. */
    const DEFAULT_PALETTE_ITEMS = 4;

    protected function definition_inner($mform) {
        global $DB;

        $maxdimension = $this->get_max_grid_dimension();

        // Ruudustiku mõõtmed.
        $mform->addElement('text', 'gridwidth',
            get_string('gridwidth', 'qtype_muster'), ['size' => 3]);
        $mform->setType('gridwidth', PARAM_INT);
        $mform->setDefault('gridwidth', 4);
        $mform->addRule('gridwidth', null, 'required', null, 'client');

        $mform->addElement('text', 'gridheight',
            get_string('gridheight', 'qtype_muster'), ['size' => 3]);
        $mform->setType('gridheight', PARAM_INT);
        $mform->setDefault('gridheight', 4);
        $mform->addRule('gridheight', null, 'required', null, 'client');

        $mform->addElement('text', 'cellsize',
            get_string('cellsize', 'qtype_muster'), ['size' => 4]);
        $mform->setType('cellsize', PARAM_INT);
        $mform->setDefault('cellsize', 32);
        $mform->addRule('cellsize', null, 'required', null, 'client');

        $mform->addElement('static', 'gridsizehint', '',
            get_string('gridsizehint', 'qtype_muster', $maxdimension));

        // Taustapilt. 
        $mform->addElement('filemanager', 'backgroundimage',
            get_string('backgroundimage', 'qtype_muster'), null,
            $this->get_filemanager_options());

        // Taustapildi näitamise/peitmise lüliti (vt muster.js) on mõeldud
        // vastaja/hindaja jaoks vaates, mitte õpetaja redigeerimisvormis -
        // siin eraldi vihjeteksti ei kuvata.

        // Baasvaliku (preset) laadimine - ainult siis, kui admin on
        // vähemalt ühe baasvaliku loonud (question/type/muster/managepresets.php).
        $presets = $DB->get_records_menu('qtype_muster_presets', null,
            'sortorder ASC, name ASC', 'id, name');
        $presetitemstoinject = null;
        if (!empty($presets)) {
            $mform->addElement('header', 'presetheader', get_string('loadpresetheader', 'qtype_muster'));
            $mform->setExpanded('presetheader', true);

            $mform->addElement('select', 'loadpresetid',
                get_string('loadpreset', 'qtype_muster'), $presets);
            $mform->setType('loadpresetid', PARAM_INT);

            $mform->addElement('submit', 'loadpreset', get_string('loadpresetbutton', 'qtype_muster'));
            $mform->registerNoSubmitButton('loadpreset');

            if (optional_param('loadpreset', '', PARAM_RAW) !== '') {
                $selectedpresetid = optional_param('loadpresetid', 0, PARAM_INT);
                if ($selectedpresetid) {
                    $presetitemstoinject = array_values($DB->get_records(
                        'qtype_muster_preset_items', ['presetid' => $selectedpresetid], 'sortorder ASC'));
                }
            }
        }

        // Värvide/sümbolite valik (korduv element) - üks element = üks rida,
        // ridade järjekorda saab muuta lohistamisega (vt amd/src/reorder.js).
        $mform->addElement('header', 'paletteheader', get_string('palette', 'qtype_muster'));
        $mform->setExpanded('paletteheader', true);

        $rowelements = [];
        $rowelements[] = $mform->createElement('select', 'palette_coltype', '', [
            'colour' => get_string('coltype_colour', 'qtype_muster'),
            'symbol' => get_string('coltype_symbol', 'qtype_muster'),
        ]);
        $rowelements[] = $mform->createElement('text', 'palette_colourvalue', '',
            ['size' => 8, 'placeholder' => '#RRGGBB']);
        $rowelements[] = $mform->createElement('text', 'palette_symbolvalue', '',
            ['size' => 4, 'maxlength' => 10, 'placeholder' => get_string('symbolvalue', 'qtype_muster')]);
        $rowelements[] = $mform->createElement('text', 'palette_itemlabel', '',
            ['size' => 16, 'placeholder' => get_string('itemlabel', 'qtype_muster')]);

        $rowgroup = $mform->createElement('group', 'palette_item_group',
            '', $rowelements, ' ', false);
        $palettearray = [$rowgroup];

        $repeatedoptions = [];
        $repeatedoptions['palette_colourvalue']['type'] = PARAM_RAW;

        $repeatedoptions['palette_symbolvalue']['type'] = PARAM_TEXT;
        $repeatedoptions['palette_itemlabel']['type'] = PARAM_TEXT;

        $existingcount = isset($this->question->palette) ? count($this->question->palette) : 0;
        $repeatsatstart = max($existingcount, self::DEFAULT_PALETTE_ITEMS);

        // Baasvaliku LISAMINE (mitte asendamine): jätame olemasolevad
        // POST-väärtused (mis peegeldavad seda, mida õpetaja juba sisestas)
        // alles ja lisame baasvaliku read nende JÄRELE. Soovimatud read saab
        // õpetaja hiljem lihtsalt tühjaks jätta/kustutada, järjekorda saab
        // pärast lisamist lohistades muuta.
        if ($presetitemstoinject !== null) {
            $existingcoltype = $_POST['palette_coltype'] ?? [];
            $existingcolour = $_POST['palette_colourvalue'] ?? [];
            $existingsymbol = $_POST['palette_symbolvalue'] ?? [];
            $existinglabel = $_POST['palette_itemlabel'] ?? [];

            $nextindex = max(
                optional_param('paletterepeats', 0, PARAM_INT),
                count($existingcoltype)
            );

            foreach ($presetitemstoinject as $item) {
                $existingcoltype[$nextindex] = $item->coltype;
                $existingcolour[$nextindex] = $item->colourvalue ?? '';
                $existingsymbol[$nextindex] = $item->symbolvalue ?? '';
                $existinglabel[$nextindex] = $item->itemlabel ?? '';
                $nextindex++;
            }

            $_POST['palette_coltype'] = $existingcoltype;
            $_POST['palette_colourvalue'] = $existingcolour;
            $_POST['palette_symbolvalue'] = $existingsymbol;
            $_POST['palette_itemlabel'] = $existinglabel;
            $_POST['paletterepeats'] = $nextindex;

            $repeatsatstart = max($repeatsatstart, $nextindex);
        }

        $actualrepeats = $this->repeat_elements($palettearray, $repeatsatstart, $repeatedoptions,
            'paletterepeats', 'palette_add_fields', 1,
            get_string('addmorepaletteitems', 'qtype_muster'), true);

        // Tingimuslik kuvamine: värvi väli ainult 'colour' tüübi puhul,
        // sümboli väli ainult 'symbol' tüübi puhul.
        for ($i = 0; $i < $actualrepeats; $i++) {
            $mform->hideIf("palette_colourvalue[$i]", "palette_coltype[$i]", 'neq', 'colour');
            $mform->hideIf("palette_symbolvalue[$i]", "palette_coltype[$i]", 'neq', 'symbol');
        }

        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_muster/reorder', 'init');
        $PAGE->requires->js_call_amd('qtype_muster/colourpicker', 'init');
    }

    /**
     * Failihalduri seaded taustapildi jaoks (üks pilt, tavapärased
     * pildivormingud).
     *
     * @return array
     */
    protected function get_filemanager_options() {
        return [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['web_image'],
        ];
    }

    /**
     * Täidab vormi olemasoleva küsimuse andmetega (redigeerimisel).
     *
     * @param object $question
     * @return object
     */
    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (!empty($question->options)) {
            $question->gridwidth = $question->options->gridwidth;
            $question->gridheight = $question->options->gridheight;
            $question->cellsize = $question->options->cellsize ?? 32;
        }

        $draftitemid = file_get_submitted_draft_itemid('backgroundimage');
        file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_muster',
            'background', $question->id ?? 0, $this->get_filemanager_options());
        $question->backgroundimage = $draftitemid;

        if (!empty($question->palette)) {
            $i = 0;
            foreach ($question->palette as $item) {
                $question->palette_coltype[$i] = $item->coltype;
                $question->palette_colourvalue[$i] = $item->colourvalue;
                $question->palette_symbolvalue[$i] = $item->symbolvalue ?? '';
                $question->palette_itemlabel[$i] = $item->itemlabel;
                $i++;
            }
        }

        return $question;
    }

    /**
     * Loeb admini seadistatud ruudustiku maksimumsuuruse (vaikimisi 30, kui
     * admin pole midagi määranud).
     *
     * @return int
     */
    protected function get_max_grid_dimension() {
        $configured = get_config('qtype_muster', 'maxgriddimension');
        return $configured ? (int) $configured : 30;
    }

    /**
     * Vormi valideerimine.
     *
     * @param array $data
     * @param array $files
     * @return array veateated väljade kaupa
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $maxdimension = $this->get_max_grid_dimension();
        foreach (['gridwidth', 'gridheight'] as $field) {
            $value = $data[$field] ?? null;
            if (!is_numeric($value) || $value < 1 || $value > $maxdimension) {
                $errors[$field] = get_string('err_gridsize', 'qtype_muster', $maxdimension);
            }
        }

        $cellsize = $data['cellsize'] ?? null;
        if (!is_numeric($cellsize) || $cellsize < 8 || $cellsize > 300) {
            $errors['cellsize'] = get_string('err_cellsize', 'qtype_muster');
        }

        $hasvaliditem = false;
        $repeats = $data['paletterepeats'] ?? 0;
        for ($i = 0; $i < $repeats; $i++) {
            $coltype = $data['palette_coltype'][$i] ?? '';
            if ($coltype === 'colour') {
                $colourvalue = $data['palette_colourvalue'][$i] ?? '';
                if (!empty($colourvalue)) {
                    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colourvalue)) {
                        $errors["palette_colourvalue[$i]"] = get_string('err_colourformat', 'qtype_muster');
                    } else {
                        $hasvaliditem = true;
                    }
                }
            } else if ($coltype === 'symbol' && trim($data['palette_symbolvalue'][$i] ?? '') !== '') {
                $hasvaliditem = true;
            }
        }
        if (!$hasvaliditem) {
            $errors['paletteheader'] = get_string('err_paletteempty', 'qtype_muster');
        }

        return $errors;
    }

    public function qtype() {
        return 'muster';
    }
}
