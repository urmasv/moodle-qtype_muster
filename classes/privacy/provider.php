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

namespace qtype_muster\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider qtype_muster jaoks.
 *
 * MÄRKUS (kontrolli enne tootmises usaldamist):
 * Õpilase vastuse enda andmed (ruudustiku täitmise sammud) käivad läbi
 * core'i küsimuste mootori tavapärase question_attempt_step_data mehhanismi,
 * mida haldab juba question/classes/privacy/provider.php - seda EI DUBLEERITA
 * siin.
 *
 * See fail käsitleb ainult qtype_muster enda lisatabelit
 * {qtype_muster_comments}, mis salvestab õpetaja kommentaarid/märgistused
 * (sh poolelioleva katse kohta).
 *
 * Kontekst tuvastatakse ahelas:
 *   qtype_muster_comments.questionattemptid -> question_attempts.questionusageid
 *   -> question_usages.contextid
 * See eeldab, et question_usages.contextid on tegelikult täidetud vastava
 * mooduli (nt mod_quiz) kontekstiga - see tuleks üle kontrollida päris
 * andmebaasi struktuuri vastu enne, kui seda GDPR-päringute jaoks
 * usaldada.
 *
 * Kommentaari AUTOR (õpetaja, userid-väli) on siin täielikult kaetud.
 * Kommentaari SUBJEKT (õpilane, kelle tööd kommenteeriti) ei ole otseselt
 * selle tabeli võõrvõti - kui on vaja, et ka õpilane saaks neid kommentaare
 * enda andmetena eksportida, tuleb lisada liitpäring läbi vastava
 * tegevusmooduli (nt mod_quiz_attempts.userid), mida siin praegu TEHTUD EI OLE.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Kirjeldab, millist isikuandmet see plugin (lisaks core küsimuste
     * mootorile) salvestab.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'qtype_muster_comments',
            [
                'questionattemptid' => 'privacy:metadata:qtype_muster_comments:questionattemptid',
                'userid' => 'privacy:metadata:qtype_muster_comments:userid',
                'commenttext' => 'privacy:metadata:qtype_muster_comments:commenttext',
                'cellrow' => 'privacy:metadata:qtype_muster_comments:cellrow',
                'cellcol' => 'privacy:metadata:qtype_muster_comments:cellcol',
                'timecreated' => 'privacy:metadata:qtype_muster_comments:timecreated',
            ],
            'privacy:metadata:qtype_muster_comments'
        );

        return $collection;
    }

    /**
     * Leiab kontekstid, kus antud kasutajal (kommentaari autoril) on andmeid.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT qu.contextid
                  FROM {qtype_muster_comments} c
                  JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                  JOIN {question_usages} qu ON qu.id = qa.questionusageid
                 WHERE c.userid = :userid";
        $contextlist->add_from_sql($sql, ['userid' => $userid]);

        return $contextlist;
    }

    /**
     * Leiab kõik kasutajad, kellel on antud kontekstis andmeid.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $sql = "SELECT c.userid
                  FROM {qtype_muster_comments} c
                  JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                  JOIN {question_usages} qu ON qu.id = qa.questionusageid
                 WHERE qu.contextid = :contextid";
        $userlist->add_from_sql('userid', $sql, ['contextid' => $context->id]);
    }

    /**
     * Ekspordib kasutaja andmed lubatud kontekstide jaoks.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            $sql = "SELECT c.id, c.commenttext, c.cellrow, c.cellcol, c.timecreated
                      FROM {qtype_muster_comments} c
                      JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                      JOIN {question_usages} qu ON qu.id = qa.questionusageid
                     WHERE qu.contextid = :contextid AND c.userid = :userid
                  ORDER BY c.timecreated ASC";
            $comments = $DB->get_records_sql($sql, [
                'contextid' => $context->id,
                'userid' => $user->id,
            ]);

            if (empty($comments)) {
                continue;
            }

            $data = array_map(function($c) {
                return (object) [
                    'commenttext' => $c->commenttext,
                    'cell' => ($c->cellrow !== null) ? "({$c->cellrow}, {$c->cellcol})" : null,
                    'timecreated' => \core_privacy\local\request\transform::datetime($c->timecreated),
                ];
            }, array_values($comments));

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'qtype_muster'), get_string('comments', 'qtype_muster')],
                (object) ['comments' => $data]
            );
        }
    }

    /**
     * Kustutab kõikide kasutajate andmed antud kontekstis.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $sql = "SELECT c.id
                  FROM {qtype_muster_comments} c
                  JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                  JOIN {question_usages} qu ON qu.id = qa.questionusageid
                 WHERE qu.contextid = :contextid";
        $ids = $DB->get_fieldset_sql($sql, ['contextid' => $context->id]);

        if (!empty($ids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($ids);
            $DB->delete_records_select('qtype_muster_comments', "id $insql", $inparams);
        }
    }

    /**
     * Kustutab ühe kasutaja andmed lubatud kontekstide jaoks.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            $sql = "SELECT c.id
                      FROM {qtype_muster_comments} c
                      JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                      JOIN {question_usages} qu ON qu.id = qa.questionusageid
                     WHERE qu.contextid = :contextid AND c.userid = :userid";
            $ids = $DB->get_fieldset_sql($sql, ['contextid' => $context->id, 'userid' => $userid]);

            if (!empty($ids)) {
                list($insql, $inparams) = $DB->get_in_or_equal($ids);
                $DB->delete_records_select('qtype_muster_comments', "id $insql", $inparams);
            }
        }
    }

    /**
     * Kustutab mitme kasutaja andmed antud kontekstis.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();

        if (empty($userids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $sql = "SELECT c.id
                  FROM {qtype_muster_comments} c
                  JOIN {question_attempts} qa ON qa.id = c.questionattemptid
                  JOIN {question_usages} qu ON qu.id = qa.questionusageid
                 WHERE qu.contextid = :contextid AND c.userid $insql";
        $inparams['contextid'] = $context->id;
        $ids = $DB->get_fieldset_sql($sql, $inparams);

        if (!empty($ids)) {
            list($idinsql, $idinparams) = $DB->get_in_or_equal($ids);
            $DB->delete_records_select('qtype_muster_comments', "id $idinsql", $idinparams);
        }
    }
}
