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
 * Baasvaliku (preset) redigeerimisvorm.
 *
 * Struktuurilt sama korduv-elementide (repeat_elements) muster, mida
 * kasutab edit_muster_form.php enda paleti jaoks - siin lihtsalt ilma
 * küsimuse-spetsiifiliste väljadeta (ruudustiku suurus jms), ainult nimi +
 * värvide/sümbolite loend.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class qtype_muster_edit_preset_form extends moodleform {

    /** @var int mitu rida vaikimisi kuvada, kui pole veel ühtki elementi. */
    const DEFAULT_ITEMS = 4;

    protected function definition() {
        $mform = $this->_form;
        $existingcount = $this->_customdata['existingcount'] ?? 0;

        $mform->addElement('text', 'name', get_string('presetname', 'qtype_muster'), ['size' => 40]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

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
        $itemarray = [$rowgroup];

        $repeatedoptions = [];
        $repeatedoptions['palette_colourvalue']['type'] = PARAM_RAW;
        // MÄRKUS: vt edit_muster_form.php sama koha kommentaari - addRule()
        // grupi sees oleval elemendil tekitab Moodle'is PEAR-põhise vea
        // (MDL-41908). #RRGGBB kuju kontroll on seetõttu ainult
        // serveripoolne (vt validation()).
        $repeatedoptions['palette_symbolvalue']['type'] = PARAM_TEXT;
        $repeatedoptions['palette_itemlabel']['type'] = PARAM_TEXT;

        $repeatsatstart = max($existingcount, self::DEFAULT_ITEMS);

        $actualrepeats = $this->repeat_elements($itemarray, $repeatsatstart, $repeatedoptions,
            'paletterepeats', 'palette_add_fields', 1,
            get_string('addmorepaletteitems', 'qtype_muster'), true);

        for ($i = 0; $i < $actualrepeats; $i++) {
            $mform->hideIf("palette_colourvalue[$i]", "palette_coltype[$i]", 'neq', 'colour');
            $mform->hideIf("palette_symbolvalue[$i]", "palette_coltype[$i]", 'neq', 'symbol');
        }

        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_muster/reorder', 'init');
        $PAGE->requires->js_call_amd('qtype_muster/colourpicker', 'init');

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (trim($data['name'] ?? '') === '') {
            $errors['name'] = get_string('required');
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
            $errors['paletterepeats'] = get_string('err_paletteempty', 'qtype_muster');
        }

        return $errors;
    }
}
