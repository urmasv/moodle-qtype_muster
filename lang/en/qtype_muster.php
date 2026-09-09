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
 * Baaskeelefail (en) - Moodle konventsiooni järgi peab igal pluginal olema
 * vähemalt 'en' keelepakett, isegi kui tegelik kasutajaliides on eesti keeles.
 * Sisu tõlgitud minimaalselt, et vältida "missing string" vigu, kui saidi
 * keeleks on määratud midagi muud kui 'et'.
 *
 * @package    qtype_muster
 * @copyright  2026 Urmas Vessin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Muster';
$string['pluginname_help'] = 'The student fills a teacher-defined grid, one cell at a time, with colours and/or characters.';
$string['pluginname_link'] = 'question/type/muster';
$string['pluginnameadding'] = 'Adding a Muster question';
$string['pluginnameediting'] = 'Editing a Muster question';
$string['pluginnamesummary'] = 'The student fills a grid of a teacher-defined size, cell by cell, using teacher-defined colours and/or characters. Manually graded (pass/fail).';

$string['gridwidth'] = 'Grid width (columns)';
$string['gridheight'] = 'Grid height (rows)';
$string['backgroundimage'] = 'Background image';
$string['cellsize'] = 'Cell side length (px)';
$string['err_cellsize'] = 'Cell side length must be a whole number between 8 and 300.';
$string['togglebackground'] = 'Show background image';
$string['palette'] = 'Colours / characters';
$string['addmorepaletteitems'] = 'Add {no} more palette items';
$string['colourvalue'] = 'Colour';
$string['itemlabel'] = 'Label (optional)';
$string['coltype'] = 'Type';
$string['coltype_colour'] = 'Colour';
$string['coltype_symbol'] = 'Character/symbol';
$string['symbolvalue'] = 'Character/symbol (e.g. A, X, ★)';
$string['cleartool'] = 'Clear cell';
$string['gridsizehint'] = 'The maximum allowed size is {$a} x {$a} (set by the administrator).';
$string['err_gridsize'] = 'Grid width and height must be whole numbers between 1 and {$a}.';
$string['err_paletteempty'] = 'Define at least one colour or character.';
$string['err_paletteitemincomplete'] = 'Palette item {no} is missing a colour or a character.';
$string['err_colourformat'] = 'Colour must be in the form #RRGGBB (e.g. #ff0000).';

$string['maxgriddimension'] = 'Maximum grid size';
$string['maxgriddimension_desc'] = 'The largest grid width/height (columns/rows) allowed when creating a Muster question. Teachers cannot exceed this value.';
$string['managepresets'] = 'Manage colour/symbol base sets';
$string['managepresets_linktext'] = 'Open the base set management page';
$string['presetname'] = 'Base set name';
$string['presetitemcount'] = 'Items';
$string['addpreset'] = 'Add a new base set';
$string['editpreset'] = 'Edit base set';
$string['nopresetsyet'] = 'No base sets have been created yet.';
$string['confirmdeletepreset'] = 'Delete the base set "{$a}"? This cannot be undone. Questions that have already loaded this base set into their own palette are not affected.';
$string['presetdeleted'] = 'Base set "{$a}" deleted.';
$string['presetsaved'] = 'Base set "{$a}" saved.';
$string['loadpresetheader'] = 'Add a base set';
$string['loadpreset'] = 'Base set';
$string['loadpresetbutton'] = 'Add this base set\'s colours/characters here';

$string['comments'] = 'Comments';
$string['addcomment'] = 'Add comment';
$string['commentplaceholder'] = 'Write a comment about the current work...';
$string['cellcomment'] = 'Comment on cell ({row}, {col})';
$string['nopermissiontocomment'] = 'You do not have permission to comment on this attempt.';

$string['privacy:metadata:qtype_muster_comments'] = 'Comments (including per-cell annotations) that a teacher adds to a student\'s Muster attempt, including while it is still in progress.';
$string['privacy:metadata:qtype_muster_comments:questionattemptid'] = 'The question attempt the comment belongs to.';
$string['privacy:metadata:qtype_muster_comments:userid'] = 'The user (teacher) who wrote the comment.';
$string['privacy:metadata:qtype_muster_comments:commenttext'] = 'The text of the comment.';
$string['privacy:metadata:qtype_muster_comments:cellrow'] = 'The grid row the comment refers to, if any.';
$string['privacy:metadata:qtype_muster_comments:cellcol'] = 'The grid column the comment refers to, if any.';
$string['privacy:metadata:qtype_muster_comments:timecreated'] = 'The time the comment was created.';

$string['muster:comment'] = 'Add comments to Muster question attempts (including in-progress attempts)';
