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

/**
 * Core external functions and service definitions.
 *
 * @package local
 * @subpackage artena
 * @copyright 2016 Adapt IT Australasia
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

	// artena additions
    'artena_ping' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'ping',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Verify connection.',
        'type'        => 'read',
        //'capabilities'=> 'moodle/user:create',
    ),

    'artena_get_categories' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'get_categories',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Retrieve course categories.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:visibility',
    ),

    'artena_create_course' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'create_course',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Create courses.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:create,moodle/course:update',
    ),

    'artena_change_course_id' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'change_course_id',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Change course ID Number.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:update',
    ),

    'artena_remove_course' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'remove_course',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Remove course.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:delete',
    ),

    'artena_create_group' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'create_group',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Create groups.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:managegroups',
    ),

    'artena_remove_group' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'remove_group',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Remove groups.',
        'type'        => 'write',
        'capabilities'=> 'moodle/course:managegroups',
    ),

    'artena_create_user' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'create_user',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Create users.',
        'type'        => 'write',
        'capabilities'=> 'moodle/user:create',
    ),

    'artena_remove_user' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'remove_user',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Remove users.',
        'type'        => 'write',
        'capabilities'=> 'moodle/user:delete',
    ),

    'artena_create_enrol' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'create_enrol',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Enrol users in courses.',
        'type'        => 'write',
        'capabilities'=> 'moodle/role:assign',
    ),

    'artena_remove_enrol' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'remove_enrol',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Unenrol users from courses.',
        'type'        => 'write',
        'capabilities'=> 'moodle/role:assign',
    ),

    'artena_get_grades' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'get_grades',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Get course results.',
        'type'        => 'write',
        'capabilities'=> 'enrol/manual:manage',
    ),
/*
    'artena_get_attendance' => array(
        'classname'   => 'local_artena_external',
        'methodname'  => 'get_attendance',
        'classpath'   => 'local/artena/externallib.php',
        'description' => 'Get attendance.',
        'type'        => 'write',
        'capabilities'=> 'mod/attendance:view',
    ),
*/    
);

$services = array(
   'Artena web service'  => array(
        'functions' => array (
            'artena_ping',
            'artena_get_categories',
            'artena_create_course',
            'artena_change_course_id',
            'artena_remove_course',
            'artena_create_group',
            'artena_remove_group',
            'artena_create_user',
            'artena_remove_user',
            'artena_create_enrol',
            'artena_remove_enrol',
            'artena_get_grades',
            //'artena_get_attendance',
            ),
        'enabled' => 1,
        'restrictedusers' => 0,
        'shortname' => 'AWS'
    ),
);
