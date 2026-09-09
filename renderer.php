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
 * qtype_muster väljundi genereerija.
 *
 * Struktuur (extends qtype_renderer, formulation_and_controls() ülekirjutus)
 * järgib core'i question/type/rendererbase.php ja qtype_essay renderer.php
 * mustrit.
 *
 * KOMMENTAARIDE OSA: see on qtype_muster enda lahendus (mitte core'i
 * question_display_options->manualcomment), kuna see peab töötama ka
 * poolelioleva (esitamata) katse puhul, mida core/mod_quiz ei toeta.
 * Kommentaari lisamise vorm postitatakse AJAX kaudu (vt amd/src/comments.js
 * ja classes/external/add_comment.php), et vältida vormide pesastamist
 * quiz'i enda vormi sisse.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class qtype_muster_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $DB, $PAGE;

        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $gridstate = $question->decode_gridstate($response);

        $inputname = $qa->get_qt_field_name('gridstate');
        $inputid = $inputname . '_' . html_writer::random_id();

        $output = html_writer::tag('div', $question->format_questiontext($qa),
            ['class' => 'qtext']);

        // Palett (valikud, millega ruute täidetakse) + kustutamise tööriist.
        $output .= html_writer::start_div('muster-palette', ['data-role' => 'muster-palette']);
        $eraserattrs = ['class' => 'muster-palette-item muster-eraser', 'data-paletteid' => '', 'type' => 'button'];
        if ($options->readonly) {
            $eraserattrs['disabled'] = 'disabled';
        }
        $output .= html_writer::tag('button', get_string('cleartool', 'qtype_muster'), $eraserattrs);
        foreach ($question->palette as $item) {
            $output .= $this->render_palette_item($item, $question, $options->readonly);
        }
        $output .= html_writer::end_div();

        // Ruudustik. Ruudu suurus (cellsize, õpetaja määratud) on ALATI
        // ruudustiku mõõtmete alus - taustapilt EI mõjuta ruudustiku
        // suurust ega kuju, see on ainult õrn (30% opaakne) taustakiht
        // ruudustiku enda all. Ääre-jooned ja tühjade ruutude välimus on
        // täpselt samad, mis ilma taustapildita versioonis.
        $bg = $this->get_background_image_info($qa, $question);
        $cellsize = !empty($question->cellsize) ? (int) $question->cellsize : 32;
        $gridclass = 'muster-grid';
        $gridattrs = [
            'data-role' => 'muster-grid',
            'data-width' => $question->gridwidth,
            'data-height' => $question->gridheight,
            'data-inputid' => $inputid,
            'data-readonly' => $options->readonly ? '1' : '0',
        ];
        $bgimg = '';
        if ($bg) {
            $gridclass .= ' muster-grid-with-bg';
            $gridattrs['data-hasbg'] = '1';
            $bgimg = html_writer::empty_tag('img', [
                'src' => $bg['url'],
                'alt' => '',
                'class' => 'muster-grid-bgimage',
            ]);
        }
        $output .= html_writer::start_div('muster-grid-outer');
        $output .= html_writer::start_div($gridclass, $gridattrs);
        $output .= $bgimg;
        for ($row = 0; $row < $question->gridheight; $row++) {
            $output .= html_writer::start_div('muster-row');
            for ($col = 0; $col < $question->gridwidth; $col++) {
                $key = $row . '_' . $col;
                $value = $gridstate[$key] ?? null;
                $output .= $this->render_cell($row, $col, $value, $question, $cellsize);
            }
            $output .= html_writer::end_div();
        }
        $output .= html_writer::end_div();

        // Taustapildi näitamise/peitmise lüliti - nähtav nii vastajale kui
        // hindajale, asub kohe pildiga ruudustiku all (vt amd/src/muster.js).
        if ($bg) {
            $output .= html_writer::start_tag('label', ['class' => 'muster-bg-toggle']);
            $output .= html_writer::empty_tag('input', [
                'type' => 'checkbox',
                'class' => 'muster-bg-toggle-checkbox',
                'checked' => 'checked',
            ]);
            $output .= ' ' . get_string('togglebackground', 'qtype_muster');
            $output .= html_writer::end_tag('label');
        }
        $output .= html_writer::end_div();

        // Varjatud sisendväli, mida JS uuendab - see on väli, mida Moodle'i
        // tavapärane quiz autosave / lehevahetuse esitus salvestab uue
        // katse sammuna (vt question.php kommentaar).
        $output .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $inputname,
            'id' => $inputid,
            'value' => $response['gridstate'] ?? '{}',
            'class' => 'muster-gridstate-input',
        ]);

        if ($qa->get_database_id()) {
            $output .= $this->render_comments_section($qa, $options);
        }

        $PAGE->requires->js_call_amd('qtype_muster/muster', 'init', [$inputid]);

        return $output;
    }

    /**
     * Renderdab ühe paletivaliku (värv või sümbol).
     */
    protected function render_palette_item($item, $question, bool $readonly) {
        $attrs = [
            'class' => 'muster-palette-item',
            'data-paletteid' => $item->id,
            'title' => $item->itemlabel ?? '',
            'type' => 'button',
        ];
        if ($readonly) {
            $attrs['disabled'] = 'disabled';
        }

        if ($item->coltype === 'colour') {
            $attrs['style'] = 'background-color: ' . s($item->colourvalue) . ';';
            return html_writer::tag('button', '', $attrs);
        }

        return html_writer::tag('button', s($item->symbolvalue), $attrs);
    }

    /**
     * Renderdab ühe ruudu.
     *
     * @param int $row
     * @param int $col
     * @param mixed $value
     * @param question_definition $question
     * @param int $cellsize ruudu külje pikkus pikslites (õpetaja määratud)
     */
    protected function render_cell($row, $col, $value, $question, int $cellsize = 32) {
        $attrs = [
            'class' => 'muster-cell',
            'data-row' => $row,
            'data-col' => $col,
            'style' => 'width: ' . $cellsize . 'px; height: ' . $cellsize . 'px;',
        ];

        $content = '';
        if ($value !== null) {
            $item = $this->find_palette_item($question, $value);
            if ($item) {
                if ($item->coltype === 'colour') {
                    // Täidetud ruut on ALATI täisopaakne (ka taustapildiga
                    // ruudustikul) - see peab katma taustapilti, mitte
                    // läbi paistma.
                    $attrs['style'] .= ' background-color: ' . s($item->colourvalue) . ';';
                } else if ($item->coltype === 'symbol') {
                    $content = s($item->symbolvalue);
                }
                $attrs['data-paletteid'] = $item->id;
            }
        }

        return html_writer::tag('div', $content, $attrs);
    }

    protected function find_palette_item($question, $paletteid) {
        foreach ($question->palette as $item) {
            if ((string) $item->id === (string) $paletteid) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Leiab küsimuse taustapildi URL-i, kui pilt on üles laetud.
     *
     * Ruudustiku suurus EI sõltu enam pildi mõõtmetest (vt cellsize
     * question.php-s) - seetõttu pole pildi loomulikke mõõtmeid vaja
     * lugeda, ainult URL.
     *
     * KRIITILINE: question_pluginfile() (core, lib/questionlib.php)
     * eeldab, et $args esimene element on kas küsimuse KATSE kasutuse id
     * (qubaid) või sõna 'preview' - mitte lihtsalt küsimuse enda id.
     * Seetõttu ehitame itemid-i mitmeosalisena: "{usageid}/{slot}/{questionid}".
     *
     * @param question_attempt $qa
     * @param question_definition $question
     * @return array{url: string}|null
     */
    protected function get_background_image_info(question_attempt $qa, $question) {
        $fs = get_file_storage();
        $contextid = $question->contextid ?? \context_system::instance()->id;

        $files = $fs->get_area_files($contextid, 'qtype_muster', 'background', $question->id,
            'itemid, filepath, filename', false);
        $file = reset($files);
        if (!$file) {
            return null;
        }

        $itemidpath = $qa->get_usage_id() . '/' . $qa->get_slot() . '/' . $question->id;
        $url = moodle_url::make_pluginfile_url(
            $contextid, 'qtype_muster', 'background', $itemidpath,
            $file->get_filepath(), $file->get_filename()
        )->out();

        return ['url' => $url];
    }

    /**
     * Renderdab kommentaaride ploki: olemasolevad kommentaarid + (kui õigus
     * on olemas) uue kommentaari lisamise vorm. Töötab ka poolelioleva
     * katse puhul, sest ei sõltu $options->manualcomment väärtusest.
     */
    protected function render_comments_section(question_attempt $qa, question_display_options $options) {
        global $DB, $USER, $PAGE;

        $qaid = $qa->get_database_id();
        $context = $this->get_current_context();
        $cancomment = $context && has_capability('qtype/muster:comment', $context);

        $comments = $DB->get_records('qtype_muster_comments',
            ['questionattemptid' => $qaid], 'timecreated ASC');

        $output = html_writer::start_div('muster-comments', ['data-qaid' => $qaid]);
        $output .= html_writer::tag('h5', get_string('comments', 'qtype_muster'));

        $output .= html_writer::start_div('muster-comments-list');
        foreach ($comments as $comment) {
            $output .= $this->render_one_comment($comment);
        }
        $output .= html_writer::end_div();

        if ($cancomment) {
            $output .= html_writer::start_tag('div', ['class' => 'muster-comment-add']);
            $output .= html_writer::tag('textarea', '', [
                'class' => 'muster-comment-text',
                'placeholder' => get_string('commentplaceholder', 'qtype_muster'),
            ]);
            $output .= html_writer::tag('button', get_string('addcomment', 'qtype_muster'), [
                'class' => 'muster-comment-submit btn btn-secondary',
                'type' => 'button',
            ]);
            $output .= html_writer::end_tag('div');

            $PAGE->requires->js_call_amd('qtype_muster/comments', 'init', [$qaid]);
        }

        $output .= html_writer::end_div();

        return $output;
    }

    protected function render_one_comment($comment) {
        global $OUTPUT;

        $user = \core_user::get_user($comment->userid);
        $where = '';
        if ($comment->cellrow !== null) {
            $where = ' ' . html_writer::tag('span',
                get_string('cellcomment', 'qtype_muster', [
                    'row' => $comment->cellrow, 'col' => $comment->cellcol,
                ]), ['class' => 'muster-comment-cell']);
        }

        return html_writer::div(
            html_writer::tag('strong', fullname($user)) . $where . ': ' .
            s($comment->commenttext) .
            html_writer::tag('div', userdate($comment->timecreated), ['class' => 'muster-comment-time']),
            'muster-comment'
        );
    }

    /**
     * NB: lihtsustatud - kontekst tuleks küsida läbi $qa/$question kaudu,
     * mitte globaalselt $PAGE->context'ist, kui renderdamine toimub väljaspool
     * tavapärast lehekonteksti (nt pistikprogrammiliselt). Vaata üle enne
     * kasutuselevõttu, eriti quiz'i review vs preview kontekstides.
     */
    protected function get_current_context() {
        global $PAGE;
        return $PAGE->context ?? null;
    }
}
