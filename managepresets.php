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
 * qtype_muster - baasvalikute (paleti mallide) haldusleht.
 *
 * Ligipääsu kontroll käib otse (require_login + require_capability), MITTE
 * admin_externalpage_setup() kaudu, kuna seda lehte pole $ADMIN puusse
 * eraldi sõlmena registreeritud (vt settings.php kommentaar) - see on
 * lihtsam ja usaldusväärsem lahendus kui admin-puu sõlme lisamine millegi
 * jaoks, mis pole tavaline admin_setting.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/question:config', $context);

$deleteid = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$pageurl = new moodle_url('/question/type/muster/managepresets.php');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managepresets', 'qtype_muster'));
$PAGE->set_heading(get_string('managepresets', 'qtype_muster'));

if ($deleteid) {
    $preset = $DB->get_record('qtype_muster_presets', ['id' => $deleteid], '*', MUST_EXIST);

    if ($confirm && confirm_sesskey()) {
        $DB->delete_records('qtype_muster_preset_items', ['presetid' => $deleteid]);
        $DB->delete_records('qtype_muster_presets', ['id' => $deleteid]);
        redirect($pageurl, get_string('presetdeleted', 'qtype_muster', $preset->name));
    }

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('confirmdeletepreset', 'qtype_muster', $preset->name),
        new moodle_url($pageurl, ['delete' => $deleteid, 'confirm' => 1, 'sesskey' => sesskey()]),
        $pageurl
    );
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();

$presets = $DB->get_records('qtype_muster_presets', null, 'sortorder ASC, name ASC');

$table = new html_table();
$table->head = [
    get_string('presetname', 'qtype_muster'),
    get_string('presetitemcount', 'qtype_muster'),
    get_string('edit'),
    get_string('delete'),
];

foreach ($presets as $preset) {
    $itemcount = $DB->count_records('qtype_muster_preset_items', ['presetid' => $preset->id]);
    $editurl = new moodle_url('/question/type/muster/editpreset.php', ['id' => $preset->id]);
    $deleteurl = new moodle_url('/question/type/muster/managepresets.php', ['delete' => $preset->id]);

    $table->data[] = [
        s($preset->name),
        $itemcount,
        html_writer::link($editurl, get_string('edit')),
        html_writer::link($deleteurl, get_string('delete')),
    ];
}

if (empty($presets)) {
    echo $OUTPUT->notification(get_string('nopresetsyet', 'qtype_muster'), 'info');
} else {
    echo html_writer::table($table);
}

$addurl = new moodle_url('/question/type/muster/editpreset.php', ['id' => 0]);
echo $OUTPUT->single_button($addurl, get_string('addpreset', 'qtype_muster'), 'get');

echo $OUTPUT->footer();
