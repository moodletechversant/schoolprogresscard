<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     local_progresscard
 * @category    upgrade
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 require_once(__DIR__.'/upgradelib.php');
 
 function xmldb_local_progresscard_upgrade($oldversion) {
     global $DB;
     $dbman = $DB->get_manager();

     if ($oldversion < 2024021611) {
         $table = new xmldb_table('type_of_exam');
         $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
         $table->add_field('typeofexam', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        
         $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
         if (!$dbman->table_exists($table)) {
             $dbman->create_table($table);
         }
         
        // Create table custom_quiz
        $table2 = new xmldb_table('custom_quiz');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('quiz_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_field('type_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }
    }

    upgrade_plugin_savepoint(true,2024021611,'local','progress_card'); 

 return true;
}