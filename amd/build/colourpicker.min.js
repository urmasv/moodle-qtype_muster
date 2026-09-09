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
 * Lisab #RRGGBB tekstivälja SISSE (ülekattega, nagu ikoon otsinguväljal)
 * natiivse brauseri värvivalija (<input type="color">), et õpetaja/admin
 * ei peaks värvikoodi peast teadma. Numbrite (#RRGGBB) järgi sisestamine
 * jääb endiselt võimalikuks - see on täiendus, mitte asendus.
 *
 * TEINE VERSIOON: esimene katse lisas värvivalija UUE ELEMENDINA rea
 * (palette_item_group) flex-voogu, mis rikkus rea paigutuse - element
 * murdus kas rea alla või kohale, sõltuvalt lisamiskohast, kuna rea
 * flex-konteineril polnud selle jaoks ruumi ette nähtud. See versioon
 * ei lisa rea flex-voogu ÜHTEGI uut elementi: mähib tekstivälja väikesse
 * "position: relative" ümbrisesse ja paigutab värvivalija selle SISSE
 * "position: absolute" abil, ülekattes tekstivälja enda vasaku servaga -
 * ümbriku enda suurus vastab endiselt lihtsalt tekstivälja suurusele,
 * nii et rea flex-paigutus ei muutu üldse.
 *
 * MÄRKUS: testimata pärismoodle keskkonnas.
 *
 * @module     qtype_muster/colourpicker
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    var init = function() {
        document.querySelectorAll('input[name^="palette_colourvalue"]').forEach(function(textInput) {
            if (textInput.dataset.musterColourEnhanced === '1') {
                return;
            }
            textInput.dataset.musterColourEnhanced = '1';

            var match = /\[(\d+)\]/.exec(textInput.getAttribute('name') || '');
            var coltypeSelect = match ?
                document.querySelector('select[name="palette_coltype[' + match[1] + ']"]') : null;

            // Mähime tekstivälja ümbrisesse - see EI LISA rea flex-voogu
            // uut elementi, kuna ümbriku suurus vastab lihtsalt
            // tekstivälja enda suurusele (display: inline-block).
            var wrapper = document.createElement('span');
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.verticalAlign = 'middle';
            textInput.parentNode.insertBefore(wrapper, textInput);
            wrapper.appendChild(textInput);

            // Ruumi tegemiseks värvivalijale nihutame teksti veidi paremale.
            textInput.style.paddingLeft = '1.8em';

            var picker = document.createElement('input');
            picker.type = 'color';
            picker.className = 'muster-colour-picker';
            picker.setAttribute('title', 'Vali värv');
            picker.style.position = 'absolute';
            picker.style.left = '3px';
            picker.style.top = '50%';
            picker.style.transform = 'translateY(-50%)';
            picker.style.width = '1.3em';
            picker.style.height = '1.3em';
            picker.style.padding = '0';
            picker.style.border = 'none';
            picker.style.background = 'transparent';
            picker.style.cursor = 'pointer';

            var syncPickerFromText = function() {
                var value = textInput.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    picker.value = value;
                }
            };
            syncPickerFromText();

            var syncVisibility = function() {
                if (coltypeSelect) {
                    var show = coltypeSelect.value === 'colour';
                    picker.style.display = show ? '' : 'none';
                    textInput.style.paddingLeft = show ? '1.8em' : '';
                }
            };
            syncVisibility();

            picker.addEventListener('input', function() {
                textInput.value = picker.value;
                textInput.dispatchEvent(new Event('input', {bubbles: true}));
                textInput.dispatchEvent(new Event('change', {bubbles: true}));
            });
            textInput.addEventListener('input', syncPickerFromText);
            textInput.addEventListener('change', syncPickerFromText);
            if (coltypeSelect) {
                coltypeSelect.addEventListener('change', syncVisibility);
            }

            wrapper.appendChild(picker);
        });
    };

    return {
        init: init
    };
});
