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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace qtype_muster\task;

/**
 * Kustutab kommentaarid, mille küsimuse katse (question_attempts) on kustutatud.
 *
 * Küsimusetüübil pole konksu katsete kustutamiseks (core kustutab question_attempts
 * read ise, nt testi katse kustutamisel), seega koristatakse orvuks jäänud read
 * ajastatud ülesandega.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_orphan_comments extends \core\task\scheduled_task {

    /**
     * @return string ülesande nimi
     */
    public function get_name() {
        return get_string('taskcleanuporphancomments', 'qtype_muster');
    }

    /**
     * Käivitab koristuse.
     */
    public function execute() {
        global $DB;

        $sql = "SELECT c.id
                  FROM {qtype_muster_comments} c
             LEFT JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                 WHERE qa.id IS NULL";
        $ids = $DB->get_fieldset_sql($sql);
        if (empty($ids)) {
            return;
        }

        foreach (array_chunk($ids, 1000) as $chunk) {
            [$insql, $inparams] = $DB->get_in_or_equal($chunk);
            $DB->delete_records_select('qtype_muster_comments', "id $insql", $inparams);
        }
        mtrace('qtype_muster: kustutatud orvuks jäänud kommentaare: ' . count($ids));
    }
}
