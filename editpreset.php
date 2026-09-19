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
 * qtype_muster - ühe baasvaliku (preset) lisamine/muutmine.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/edit_preset_form.php');

require_login();
$context = context_system::instance();
require_capability('moodle/question:config', $context);

$id = optional_param('id', 0, PARAM_INT);
$manageurl = new moodle_url('/question/type/muster/managepresets.php');
$pageurl = new moodle_url('/question/type/muster/editpreset.php', ['id' => $id]);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$preset = null;
$items = [];
if ($id) {
    $preset = $DB->get_record('qtype_muster_presets', ['id' => $id], '*', MUST_EXIST);
    $items = array_values($DB->get_records('qtype_muster_preset_items',
        ['presetid' => $id], 'sortorder ASC'));
}

$title = $id ? get_string('editpreset', 'qtype_muster') : get_string('addpreset', 'qtype_muster');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$mform = new qtype_muster_edit_preset_form($pageurl, ['existingcount' => count($items)]);

if ($mform->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $mform->get_data()) {
    if (empty($data->id)) {
        $preset = new stdClass();
        $preset->name = $data->name;
        $preset->sortorder = 0;
        $preset->timecreated = time();
        $preset->timemodified = time();
        $preset->id = $DB->insert_record('qtype_muster_presets', $preset);
    } else {
        $preset = $DB->get_record('qtype_muster_presets', ['id' => $data->id], '*', MUST_EXIST);
        $preset->name = $data->name;
        $preset->timemodified = time();
        $DB->update_record('qtype_muster_presets', $preset);
    }

    $DB->delete_records('qtype_muster_preset_items', ['presetid' => $preset->id]);

    $repeats = $data->paletterepeats ?? 0;
    $sortorder = 0;
    for ($i = 0; $i < $repeats; $i++) {
        $coltype = $data->palette_coltype[$i] ?? '';
        if (!in_array($coltype, ['colour', 'symbol'], true)) {
            continue;
        }

        $colourvalue = null;
        if ($coltype === 'colour') {
            $colourvalue = $data->palette_colourvalue[$i] ?? null;
            if (empty($colourvalue)) {
                continue;
            }
        }

        $symbolvalue = null;
        if ($coltype === 'symbol') {
            $symbolvalue = trim($data->palette_symbolvalue[$i] ?? '');
            if ($symbolvalue === '') {
                continue;
            }
        }

        $item = new stdClass();
        $item->presetid = $preset->id;
        $item->sortorder = $sortorder;
        $item->coltype = $coltype;
        $item->colourvalue = $colourvalue;
        $item->symbolvalue = $symbolvalue;
        $item->itemlabel = $data->palette_itemlabel[$i] ?? null;
        $DB->insert_record('qtype_muster_preset_items', $item);

        $sortorder++;
    }

    redirect($manageurl, get_string('presetsaved', 'qtype_muster', $preset->name));
}

// Vormi eeltäitmine olemasoleva baasvaliku andmetega.
if ($preset) {
    $toform = new stdClass();
    $toform->id = $preset->id;
    $toform->name = $preset->name;
    $i = 0;
    foreach ($items as $item) {
        $toform->palette_coltype[$i] = $item->coltype;
        $toform->palette_colourvalue[$i] = $item->colourvalue;
        $toform->palette_symbolvalue[$i] = $item->symbolvalue;
        $toform->palette_itemlabel[$i] = $item->itemlabel;
        $i++;
    }
    $mform->set_data($toform);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform->display();
echo $OUTPUT->footer();
