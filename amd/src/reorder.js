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
 * Lohistamisega ridade ümberjärjestamine qtype_muster paleti/baasvaliku
 * redigeerimisvormides (edit_muster_form.php ja edit_preset_form.php).
 *
 * "Add {no} more" nupp (repeat_elements'i noSubmit nupp) põhjustab kogu
 * lehe uuestilaadimise, nii et init() käivitub selle järel niikuinii
 * uuesti värske reana loenduriga.
 *
 * @module     qtype_muster/reorder
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    var draggingRow = null;
    var activeContainer = null;

    /**
     * Kirjutab kõigi ridade sisendväljade name="...[N]" indeksid ümber
     * vastavalt nende hetke DOM-järjekorrale.
     *
     * @param {Array} rows .muster-draggable-row elemendid uues järjekorras
     */
    var reindexRows = function(rows) {
        rows.forEach(function(row, newIndex) {
            row.querySelectorAll('input, select, textarea').forEach(function(field) {
                var name = field.getAttribute('name');
                if (!name) {
                    return;
                }
                var newName = name.replace(/\[\d+\]/, '[' + newIndex + ']');
                if (newName !== name) {
                    field.setAttribute('name', newName);
                }
            });
        });
    };

    /**
     * Pointermove: liigutab lohistatava rea DOM-is kursori Y asukoha
     * järgi. Leiab (praeguses DOM-järjekorras) ESIMESE rea, mille
     * keskpunkt on kursorist allpool, ja paigutab lohistatava rea sellele
     * vahetult ette; kui kõik read on kursorist ülalpool, paigutab lõppu.
     *
     * @param {PointerEvent} e
     */
    var onPointerMove = function(e) {
        if (!draggingRow || !activeContainer) {
            return;
        }
        var rows = Array.prototype.slice.call(
            activeContainer.querySelectorAll('.muster-draggable-row'))
            .filter(function(row) {
                return row !== draggingRow;
            });

        var insertBeforeRow = null;
        for (var i = 0; i < rows.length; i++) {
            var rect = rows[i].getBoundingClientRect();
            var mid = rect.top + (rect.height / 2);
            if (e.clientY < mid) {
                insertBeforeRow = rows[i];
                break;
            }
        }

        if (insertBeforeRow) {
            if (draggingRow.nextElementSibling !== insertBeforeRow) {
                activeContainer.insertBefore(draggingRow, insertBeforeRow);
            }
        } else if (activeContainer.lastElementChild !== draggingRow) {
            activeContainer.appendChild(draggingRow);
        }
    };

    /**
     * Nihutab "Lisa N elementi juurde" nupu (repeat_elements'i enda
     * noSubmit nupp) alati ridade loendi kõige lõppu. See nupp EI kanna
     * ".muster-draggable-row" klassi, mistõttu lohistamine võib ridu
     * sellest mööda liigutada, jättes nupu üksinda ridade vahele.
     *
     * @param {Element} container
     */
    var pushAddMoreButtonToEnd = function(container) {
        var button = container.querySelector('[name="palette_add_fields"]');
        if (!button) {
            return;
        }
        var wrapper = button;
        while (wrapper.parentElement && wrapper.parentElement !== container) {
            wrapper = wrapper.parentElement;
        }
        if (wrapper.parentElement === container) {
            container.appendChild(wrapper);
        }
    };

    /**
     * Pointerup: lõpetab lohistamise ja kirjutab indeksid ümber.
     */
    var onPointerUp = function() {
        if (draggingRow) {
            draggingRow.classList.remove('muster-dragging-row');
            draggingRow.style.opacity = '';
        }
        if (activeContainer) {
            reindexRows(Array.prototype.slice.call(
                activeContainer.querySelectorAll('.muster-draggable-row')));
            pushAddMoreButtonToEnd(activeContainer);
        }
        draggingRow = null;
        activeContainer = null;
        document.removeEventListener('pointermove', onPointerMove);
        document.removeEventListener('pointerup', onPointerUp);
    };

    /**
     * Leiab korraga KÕIGI ridade jaoks tasandi, kus (a) iga rea element
     * sisaldab oma indeksile vastavaid colourvalue/symbolvalue/itemlabel
     * välju JA (b) kõigi ridade element sellel TASANDIL jagab sama otsest
     * vanemat (st on tegelikult üksteise vennad-õed DOM-is).
     *
     * @param {NodeList} markers palette_coltype[N] valikuväljad
     * @return {Object|null} {rows: Array, container: Element}
     */
    var detectRowsAndContainer = function(markers) {
        var chains = [];
        markers.forEach(function(marker) {
            var match = /\[(\d+)\]/.exec(marker.getAttribute('name') || '');
            if (!match) {
                return;
            }
            var chain = [];
            var node = marker;
            while (node && node !== document.body) {
                chain.push(node);
                node = node.parentElement;
            }
            chains.push({suffix: '[' + match[1] + ']', chain: chain});
        });

        if (!chains.length) {
            return null;
        }

        var maxDepth = chains.reduce(function(max, c) {
            return Math.max(max, c.chain.length);
        }, 0);

        for (var depth = 0; depth < maxDepth; depth++) {
            var candidateRows = [];
            var candidateParent = null;
            var allMatch = true;

            for (var c = 0; c < chains.length; c++) {
                var node = chains[c].chain[depth];
                if (!node || !node.parentElement) {
                    allMatch = false;
                    break;
                }
                var suffix = chains[c].suffix;
                var hasColour = node.querySelector('[name="palette_colourvalue' + suffix + '"]');
                var hasSymbol = node.querySelector('[name="palette_symbolvalue' + suffix + '"]');
                var hasLabel = node.querySelector('[name="palette_itemlabel' + suffix + '"]');
                if (!hasColour || !hasSymbol || !hasLabel) {
                    allMatch = false;
                    break;
                }
                if (candidateParent === null) {
                    candidateParent = node.parentElement;
                } else if (candidateParent !== node.parentElement) {
                    allMatch = false;
                    break;
                }
                candidateRows.push(node);
            }

            if (allMatch && candidateRows.length === chains.length) {
                return {rows: candidateRows, container: candidateParent};
            }
        }

        return null;
    };

    /**
     * Joondab paleti read sama taandega, mis on tavapärastel siltidega
     * vormiväljadel (nt "Ruudustiku laius" või baasvaliku "Nimi") -
     * mõõdab TEGELIKU pikslinihke otse ekraanilt, selle asemel et
     * eeldada mingit kindlat Moodle'i/teema CSS-klassi või -struktuuri.
     *
     * @param {Array} rows
     */
    var alignRowsIndent = function(rows) {
        if (!rows.length) {
            return;
        }
        var referenceInput = document.querySelector(
            'input[name="gridwidth"], input[name="name"]');
        if (!referenceInput) {
            return;
        }
        var refRect = referenceInput.getBoundingClientRect();
        var rowRect = rows[0].getBoundingClientRect();
        var diff = Math.round(refRect.left - rowRect.left);
        if (diff > 0) {
            rows.forEach(function(row) {
                row.style.paddingLeft = diff + 'px';
            });
        }
    };

    /**
     * Algatab lohistamise ühe vormi paleti-ridade jaoks.
     */
    var init = function() {
        var markers = document.querySelectorAll('select[name^="palette_coltype"]');
        if (!markers.length) {
            return;
        }

        var found = detectRowsAndContainer(markers);
        if (!found || !found.rows.length) {
            return;
        }

        var rows = found.rows;
        var container = found.container;

        alignRowsIndent(rows);

        rows.forEach(function(row) {
            row.classList.add('muster-draggable-row');

            if (row.querySelector('.muster-drag-handle')) {
                return;
            }

            var handle = document.createElement('span');
            handle.className = 'muster-drag-handle';
            handle.setAttribute('title', 'Lohista, et muuta järjekorda');
            handle.style.cursor = 'grab';
            handle.style.display = 'inline-block';
            handle.style.width = '1.5em';
            handle.style.textAlign = 'center';
            handle.style.marginRight = '0.5em';
            handle.style.userSelect = 'none';
            handle.style.touchAction = 'none';
            handle.textContent = '\u2261';
            row.insertBefore(handle, row.firstChild);

            handle.addEventListener('pointerdown', function(e) {
                e.preventDefault();
                draggingRow = row;
                activeContainer = container;
                row.classList.add('muster-dragging-row');
                row.style.opacity = '0.5';
                document.addEventListener('pointermove', onPointerMove);
                document.addEventListener('pointerup', onPointerUp);
            });
        });
    };

    return {
        init: init
    };
});
