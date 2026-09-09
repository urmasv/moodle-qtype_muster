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
 * Eestikeelsed stringid - see on kasutajaliidese põhikeel selles keskkonnas.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Muster';
$string['pluginname_help'] = 'Õpilane täidab õpetaja määratud ruudustikku ükshaaval värvide ja/või kirjamärkidega.';
$string['pluginname_link'] = 'question/type/muster';
$string['pluginnameadding'] = 'Mustri küsimuse lisamine';
$string['pluginnameediting'] = 'Mustri küsimuse muutmine';
$string['pluginnamesummary'] = 'Õpilane täidab õpetaja määratud suurusega ruudustikku ükshaaval, kasutades õpetaja määratud värve ja/või kirjamärke. Hinnatakse käsitsi (arvestatud/mittearvestatud).';

$string['gridwidth'] = 'Ruudustiku laius (veerge)';
$string['gridheight'] = 'Ruudustiku kõrgus (ridu)';
$string['backgroundimage'] = 'Taustapilt';
$string['cellsize'] = 'Ruudu külje pikkus (px)';
$string['err_cellsize'] = 'Ruudu külje pikkus peab olema täisarv vahemikus 8 kuni 300.';
$string['togglebackground'] = 'Näita taustapilti';
$string['palette'] = 'Värvid / kirjamärgid';
$string['addmorepaletteitems'] = 'Lisa {no} elementi juurde';
$string['colourvalue'] = 'Värv';
$string['itemlabel'] = 'Silt (valikuline)';
$string['coltype'] = 'Tüüp';
$string['coltype_colour'] = 'Värv';
$string['coltype_symbol'] = 'Kirjamärk/sümbol';
$string['symbolvalue'] = 'Kirjamärk/sümbol (nt A, X, ★)';
$string['cleartool'] = 'Tühjenda ruut';
$string['gridsizehint'] = 'Maksimaalne lubatud suurus on {$a} x {$a} (määrab administraator).';
$string['err_gridsize'] = 'Ruudustiku laius ja kõrgus peavad olema täisarvud vahemikus 1 kuni {$a}.';
$string['err_paletteempty'] = 'Määra vähemalt üks värv või kirjamärk.';
$string['err_paletteitemincomplete'] = 'Valiku elemendil {no} puudub värv või kirjamärk.';
$string['err_colourformat'] = 'Värv peab olema kujul #RRGGBB (nt #ff0000).';

$string['maxgriddimension'] = 'Ruudustiku maksimaalne suurus';
$string['maxgriddimension_desc'] = 'Suurim lubatud ruudustiku laius/kõrgus (veergu/rida) mustri küsimuse loomisel. Õpetajad ei saa seda väärtust ületada.';
$string['managepresets'] = 'Halda värvide/sümbolite baasvalikuid';
$string['managepresets_linktext'] = 'Ava baasvalikute haldusleht';
$string['presetname'] = 'Baasvaliku nimi';
$string['presetitemcount'] = 'Elemente';
$string['addpreset'] = 'Lisa uus baasvalik';
$string['editpreset'] = 'Muuda baasvalikut';
$string['nopresetsyet'] = 'Ühtegi baasvalikut pole veel loodud.';
$string['confirmdeletepreset'] = 'Kas kustutada baasvalik "{$a}"? Seda tegevust ei saa tagasi võtta. Küsimused, mis on selle baasvaliku juba enda paletti laadinud, ei muutu.';
$string['presetdeleted'] = 'Baasvalik "{$a}" kustutatud.';
$string['presetsaved'] = 'Baasvalik "{$a}" salvestatud.';
$string['loadpresetheader'] = 'Baasvaliku lisamine';
$string['loadpreset'] = 'Baasvalik';
$string['loadpresetbutton'] = 'Lisa baasvaliku värvid/sümbolid siia';

$string['comments'] = 'Kommentaarid';
$string['addcomment'] = 'Lisa kommentaar';
$string['commentplaceholder'] = 'Kirjuta kommentaar praeguse töö kohta...';
$string['cellcomment'] = 'Kommentaar ruudule ({row}, {col})';
$string['nopermissiontocomment'] = 'Sul pole õigust selle katse juurde kommentaari lisada.';

$string['privacy:metadata:qtype_muster_comments'] = 'Kommentaarid (sh üksikutele ruutudele lisatud märkused), mida õpetaja lisab õpilase mustri-katsele, sh ajal, mil katse on veel pooleli.';
$string['privacy:metadata:qtype_muster_comments:questionattemptid'] = 'Küsimuse katse, millega kommentaar seotud on.';
$string['privacy:metadata:qtype_muster_comments:userid'] = 'Kasutaja (õpetaja), kes kommentaari kirjutas.';
$string['privacy:metadata:qtype_muster_comments:commenttext'] = 'Kommentaari tekst.';
$string['privacy:metadata:qtype_muster_comments:cellrow'] = 'Ruudustiku rida, millele kommentaar viitab (kui on).';
$string['privacy:metadata:qtype_muster_comments:cellcol'] = 'Ruudustiku veerg, millele kommentaar viitab (kui on).';
$string['privacy:metadata:qtype_muster_comments:timecreated'] = 'Kommentaari loomise aeg.';

$string['muster:comment'] = 'Lisa kommentaare mustri küsimuse katsetele (sh poolelioleavtele katsetele)';
