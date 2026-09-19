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

namespace qtype_muster\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

/**
 * Väline funktsioon: lisab kommentaari (üld- või rakukohase) ühele
 * mustri-küsimuse katsele - sõltumata sellest, kas katse on esitatud
 * või alles pooleli.
 *
 * NB: kasutab core_external\* nimeruumi (Moodle 4.2+ konventsioon), mis
 * sobib sihtplatvormiga Moodle 5.x. Kui pluginat kunagi kasutatakse
 * vanemal Moodle'il, tuleb üle minna vanale \external_api jne.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_comment extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'questionattemptid' => new external_value(PARAM_INT, 'question_attempts.id'),
            'commenttext' => new external_value(PARAM_TEXT, 'Kommentaari tekst'),
            'cellrow' => new external_value(PARAM_INT, 'Rida (kui rakukohane)', VALUE_DEFAULT, null),
            'cellcol' => new external_value(PARAM_INT, 'Veerg (kui rakukohane)', VALUE_DEFAULT, null),
        ]);
    }

    public static function execute(int $questionattemptid, string $commenttext,
            ?int $cellrow = null, ?int $cellcol = null): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'questionattemptid' => $questionattemptid,
            'commenttext' => $commenttext,
            'cellrow' => $cellrow,
            'cellcol' => $cellcol,
        ]);

        // Leiab konteksti, milles see katse toimub (question_usages.contextid),
        // et saaks kontrollida õigust JA valideerida konteksti.
        $sql = "SELECT qu.contextid
                  FROM {question_attempts} qa
                  JOIN {question_usages} qu ON qu.id = qa.questionusageid
                 WHERE qa.id = :qaid";
        $contextid = $DB->get_field_sql($sql, ['qaid' => $params['questionattemptid']], MUST_EXIST);
        $context = \context::instance_by_id($contextid);

        self::validate_context($context);
        require_capability('qtype/muster:comment', $context);

        if (trim($params['commenttext']) === '') {
            throw new \invalid_parameter_exception('Kommentaar ei tohi olla tühi.');
        }

        $record = new \stdClass();
        $record->questionattemptid = $params['questionattemptid'];
        $record->userid = $USER->id;
        $record->cellrow = $params['cellrow'];
        $record->cellcol = $params['cellcol'];
        $record->commenttext = $params['commenttext'];
        $record->commenttextformat = FORMAT_PLAIN;
        $record->timecreated = time();
        $record->id = $DB->insert_record('qtype_muster_comments', $record);

        return [
            'id' => $record->id,
            'fullname' => fullname($USER),
            'commenttext' => $record->commenttext,
            'cellrow' => $record->cellrow,
            'cellcol' => $record->cellcol,
            'timecreated' => userdate($record->timecreated),
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Kommentaari id'),
            'fullname' => new external_value(PARAM_TEXT, 'Autori nimi'),
            'commenttext' => new external_value(PARAM_TEXT, 'Kommentaari tekst'),
            'cellrow' => new external_value(PARAM_INT, 'Rida', VALUE_DEFAULT, null),
            'cellcol' => new external_value(PARAM_INT, 'Veerg', VALUE_DEFAULT, null),
            'timecreated' => new external_value(PARAM_TEXT, 'Ajatempel (loetav)'),
        ]);
    }
}
