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
 * qtype_muster kommentaaride lisamine AJAX kaudu.
 *
 * Kasutab core/ajax moodulit (Moodle standardne AMD AJAX-kutse). Töötab nii
 * pooleliolevate kui esitatud katsete puhul, kuna server (add_comment.php)
 * ei kontrolli katse olekut, vaid ainult kasutaja õigust
 * (qtype/muster:comment).
 *
 * MÄRKUS: testimata pärismoodle keskkonnas.
 *
 * @module     qtype_muster/comments
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    /**
     * @param {Number} qaid question_attempts.id
     */
    var init = function(qaid) {
        var container = document.querySelector('.muster-comments[data-qaid="' + qaid + '"]');
        if (!container) {
            return;
        }
        var button = container.querySelector('.muster-comment-submit');
        var textarea = container.querySelector('.muster-comment-text');
        var list = container.querySelector('.muster-comments-list');

        if (!button || !textarea || !list) {
            return;
        }

        button.addEventListener('click', function() {
            var text = textarea.value.trim();
            if (text === '') {
                return;
            }
            button.disabled = true;

            Ajax.call([{
                methodname: 'qtype_muster_add_comment',
                args: {
                    questionattemptid: qaid,
                    commenttext: text,
                    cellrow: null,
                    cellcol: null
                }
            }])[0].done(function(result) {
                var div = document.createElement('div');
                div.className = 'muster-comment';
                div.innerHTML = '<strong></strong>: <span></span>' +
                    '<div class="muster-comment-time"></div>';
                div.querySelector('strong').textContent = result.fullname;
                div.querySelector('span').textContent = result.commenttext;
                div.querySelector('.muster-comment-time').textContent = result.timecreated;
                list.appendChild(div);
                textarea.value = '';
            }).always(function() {
                button.disabled = false;
            });
        });
    };

    return {
        init: init
    };
});
