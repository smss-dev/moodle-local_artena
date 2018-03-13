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
 * Moodle Artena web service plugin
 *
 * @package local
 * @subpackage artena
 * @copyright 2016 Adapt IT Australasia
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/externallib.php");

class local_artena_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function ping_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'ping' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                         )
                    )
                )
            )
        );
    }

    /**
     * @param array $ping  An empty object
     * @return array An array of arrays
     */
    public static function ping($ping) {
        global $CFG, $DB;
        $result = array();
        self::log_for_artena('ping','GOT HERE',1);
        $result[] = array('result'=>'success');
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function ping_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'result' => new external_value(PARAM_TEXT, 'ping response (success)'),
                )
            )
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function get_categories_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'categories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                         )
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    public static function get_categories() {
        global $CFG, $DB;

        $result = array();
        self::log_for_artena('get_categories','BEGIN',1);
        try {
            if (false === ($categories = $DB->get_records('course_categories'))) {
                self::log_for_artena('get_categories','found no categories!');
                $result[] = array();
            } else {
                foreach ($categories as $category_record) {
                    self::log_for_artena('get_categories','found category:'.$category_record->name);
                    $result[] = array ('id' => $category_record->id, 'name' => $category_record->name, 'visible' => $category_record->visible, 'path' => $category_record->path, 'depth' => $category_record->depth);
                }
            }
        }
        catch (moodle_exception $e) {
            self::log_for_artena('get_categories', 'moodle EXCEPTION! ' . $e->getMessage());
            $result[] = array('id'=>-1, 'name'=>'', 'visible'=>0, 'path'=>'', 'depth'=>-1, 'message'=>$e->getMessage());
        }
        catch (Exception $e) {
            self::log_for_artena('get_categories', 'EXCEPTION! ' . $e->getMessage());
            $result[] = array('id'=>-1, 'name'=>'', 'visible'=>0, 'path'=>'', 'depth'=>-1, 'message'=>$e->getMessage());
        }

        return $result;
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'moodle category id'),
                    'name' => new external_value(PARAM_TEXT, 'category name'),
                    'visible' => new external_value(PARAM_INT, 'indication of category visibility'),
                    'path' => new external_value(PARAM_TEXT, 'category tree structure'),
                    'depth' => new external_value(PARAM_INT, 'category depth in tree'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }



    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_course_parameters() {
        $courseconfig = get_config('moodlecourse'); //needed for many default values
        return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_INT, 'timestamp when the course starts', VALUE_OPTIONAL),
                            'enddate' => new external_value(PARAM_INT, 'timestamp when the course finishes', VALUE_OPTIONAL),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL),

                            // moodle supplies
                            'summaryformat' => new external_value(PARAM_INT, 'the summary text Moodle format', VALUE_DEFAULT, FORMAT_MOODLE),
                            'format' => new external_value(PARAM_ALPHANUMEXT, 'course format: weeks, topics, social, site,..', VALUE_DEFAULT, $courseconfig->format),
                            'showgrades' => new external_value(PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_DEFAULT, $courseconfig->showgrades),
                            'newsitems' => new external_value(PARAM_INT, 'number of recent items appearing on the course page', VALUE_DEFAULT, $courseconfig->newsitems),
                            'numsections' => new external_value(PARAM_INT, 'number of weeks/topics', VALUE_DEFAULT, $courseconfig->numsections),
                            'maxbytes' => new external_value(PARAM_INT, 'largest size of file that can be uploaded into the course', VALUE_DEFAULT, $courseconfig->maxbytes),
                            'showreports' => new external_value(PARAM_INT, 'are activity report shown (yes = 1, no =0)', VALUE_DEFAULT, $courseconfig->showreports),
                            'hiddensections' => new external_value(PARAM_INT, 'How the hidden sections in the course are displayed to students', VALUE_DEFAULT, $courseconfig->hiddensections),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible', VALUE_DEFAULT, $courseconfig->groupmode),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no', VALUE_DEFAULT, $courseconfig->groupmodeforce),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id', VALUE_DEFAULT, 0),

                            'overwrite_names' => new external_value(PARAM_INT, 'ARTENA FIELD (1: overwrite, 0:ignore)', VALUE_OPTIONAL),
                            'link_courses' => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),

                            // TBD supplies
                            //'enablecompletion' => new external_value(PARAM_INT, 'Enabled, control via completion and activity settings. Disabled, not shown in activity settings.', VALUE_OPTIONAL),
                            //'completionstartonenrol' => new external_value(PARAM_INT, '1: begin tracking a student\'s progress in course completion after course enrolment. 0: does not', VALUE_OPTIONAL),
                            //'completionnotify' => new external_value(PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL),
                            //'lang' => new external_value(PARAM_ALPHANUMEXT, 'forced course language', VALUE_OPTIONAL),
                            //'forcetheme' => new external_value(PARAM_ALPHANUMEXT, 'name of the force theme', VALUE_OPTIONAL),
                        )
                    ), 'courses to create'
                )
            )
        );
    }

    /**
     * Create  courses
     * @param array $courses
     * @return array courses (id and shortname only)
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public static function create_course($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        self::log_for_artena('create_course', 'BEGIN', 1);
        $params = self::validate_parameters(self::create_course_parameters(),
                        array('courses' => $courses));

        $availablethemes = core_component::get_plugin_list('theme');
        $availablelangs = get_string_manager()->get_list_of_translations();

        foreach ($params['courses'] as $course) {
            try {
                self::log_for_artena('create_course', print_r($course,1));
                $transaction = $DB->start_delegated_transaction();

                // Ensure the context for this category exists
                $context = context_coursecat::instance($course['categoryid']);
                if (false === $context) {   // unknown context
                    throw new Exception('Context does not exist for category: '.$course['categoryid']);
                }
                self::log_for_artena('create_course', print_r($context,1));

                // Check if this is a create or update request
                if ($course['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $course['fullname'], 'shortname' => $course['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $course['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $course['idnumber']));
                }

                if (false === $existing_course) {   // create
                    self::log_for_artena('create_course', 'create course');

                    require_capability('moodle/course:create', $context);

                    // Make sure lang is valid
                    if (array_key_exists('lang', $course) and empty($availablelangs[$course['lang']])) {
                        throw new Exception(get_string('errorinvalidparam', 'webservice', 'lang'));
                    }

                    // Make sure theme is valid
                    if (array_key_exists('forcetheme', $course)) {
                        if (!empty($CFG->allowcoursethemes)) {
                            if (empty($availablethemes[$course['forcetheme']])) {
                                throw new Exception(get_string('errorinvalidparam', 'webservice', 'forcetheme'));
                            } else {
                                $course['theme'] = $course['forcetheme'];
                            }
                        }
                    }

                    //force visibility if ws user doesn't have the permission to set it
                    $category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
                    if (!has_capability('moodle/course:visibility', $context)) {
                        $course['visible'] = $category->visible;
                    }

                    //set default value for completion
                    $courseconfig = get_config('moodlecourse');
                    if (completion_info::is_enabled_for_site()) {
                        if (!array_key_exists('enablecompletion', $course)) {
                            $course['enablecompletion'] = $courseconfig->enablecompletion;
                        }
                        if (!array_key_exists('completionstartonenrol', $course)) {
                            $course['completionstartonenrol'] = $courseconfig->completionstartonenrol;
                        }
                    } else {
                        $course['enablecompletion'] = 0;
                        $course['completionstartonenrol'] = 0;
                    }

                    $course['category'] = $course['categoryid'];
                    self::log_for_artena('create_course', 'new course obj=' . print_r($course,1));
                    /*
                    if ($course['link_courses']){
                        $course['idnumber'] = '';
                    }
                    */
                    $course['id'] = create_course((object) $course)->id;
                    self::log_for_artena('create_course', 'new course id=' . $course['id']);
                    $resultcourses[] = array('artenaid' => $course['artenaid'], 'id' => $course['id'], 'idnumber' => $course['idnumber'], 'fullname' => $course['fullname'], 'category' => $category->name, 'action'=> 'add');

                } else {    // update
                    self::log_for_artena('create_course', 'update course');
                    $category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
                    require_capability('moodle/course:update', $context);

                    $updated_course = (array) $existing_course;
                    $updated_course['category'] = $course['categoryid'];
                    $updated_course['startdate'] = $course['startdate'];
                    if (isset($course['enddate'])) {
                      $updated_course['enddate'] = $course['enddate'];
                    }
                    if (1 == $course['overwrite_names']) {
                      $updated_course['fullname'] = $course['fullname'];
                      $updated_course['shortname'] = $course['shortname'];
                      $updated_course['summary'] = $course['summary'];
                    }

                    update_course((object) $updated_course);
                    $resultcourses[] = array('artenaid' => $course['artenaid'], 'id' => $updated_course['id'], 'idnumber' => $course['idnumber'], 'fullname' => $course['fullname'], 'category' => $category->name, 'action'=> 'update');
                }

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('create_course', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('artenaid' => $course['artenaid'], 'id' => -1, 'idnumber' => $course['idnumber'], 'fullname' => $course['fullname'], 'category' => (isset($category) ? $category->name : ''), 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('create_course', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('artenaid' => $course['artenaid'], 'id' => -1, 'idnumber' => $course['idnumber'], 'fullname' => $course['fullname'], 'category' => (isset($category) ? $category->name : ''), 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('create_course', 'EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('artenaid' => $course['artenaid'], 'id' => -1, 'idnumber' => $course['idnumber'], 'fullname' => $course['fullname'], 'category' => (isset($category) ? $category->name : ''), 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }
        self::log_for_artena('create_course', 'END');
        return $resultcourses;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function create_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'idnumber' => new external_value(PARAM_RAW, 'artena course idnumber'),
                    'fullname' => new external_value(PARAM_TEXT, 'full name'),
                    'category' => new external_value(PARAM_TEXT, 'course category'),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function change_course_id_parameters() {
        return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'idnumberold' => new external_value(PARAM_RAW, 'existing id number'),
                            'idnumbernew' => new external_value(PARAM_RAW, 'new id number'),
                        )
                    ), 'course ids to update'
                )
            )
        );
    }

    /**
     * Create  courses
     * @param array $courses
     * @return array courses (id and idnumber only)
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public static function change_course_id($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');

        self::log_for_artena('change_course_id', 'BEGIN',1);

        $params = self::validate_parameters(self::change_course_id_parameters(),
                        array('courses' => $courses));

        foreach ($params['courses'] as $course) {
            try {
                $transaction = $DB->start_delegated_transaction();

                // Check if this is a create or update request
                $existing_course = $DB->get_record('course', array('idnumber' => $course['idnumberold']));

                if (false === $existing_course) {
                    throw new Exception('record does not exist!');
                } else {

                    // Ensure the current user is allowed to run this function
                    $context = context_coursecat::instance($existing_course->category);
                    require_capability('moodle/course:update', $context);

                    $existing_course->idnumber = $course['idnumbernew'];

                    update_course((object) $existing_course);
                    $updated_course = $DB->get_record('course', array('idnumber' => $course['idnumbernew']));
                    $resultcourses[] = array('id' => $updated_course->id, 'idnumber' => $updated_course->idnumber, 'action' => 'update');
                }

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('change_course_id', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('id' => -1, 'idnumber' => $course['idnumbernew'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('change_course_id', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('id' => -1, 'idnumber' => $course['idnumbernew'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('change_course_id', 'EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('id' => -1, 'idnumber' => $course['idnumbernew'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        self::log_for_artena('change_course_id', 'END');

        return $resultcourses;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function change_course_id_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'idnumber' => new external_value(PARAM_RAW, 'id number'),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_course_parameters() {
        return new external_function_parameters(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'fullname'      => new external_value(PARAM_TEXT, 'course full name'),
                            'shortname'     => new external_value(PARAM_TEXT, 'course short name'),
                            'idnumber'      => new external_value(PARAM_RAW, 'course id number'),
                            'link_courses'  => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),
                        )
                    ), 'courses to remove'
                )
            )
        );
    }

    /**
     * Create  courses
     * @param array $courses
     * @return array courses (id and idnumber only)
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public static function remove_course($courses) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/moodlelib.php');

        $params = self::validate_parameters(self::remove_course_parameters(),
                        array('courses' => $courses));

        foreach ($params['courses'] as $course) {
            try {
                $transaction = $DB->start_delegated_transaction();

                // Check if this is a create or update request
                if ($course['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $course['fullname'], 'shortname' => $course['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $course['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $course['idnumber']));
                }

                if (false === $existing_course) {   // unknown course
                    throw new Exception('Unknown course: '.$course['idnumber']);
                } else {

                    // Ensure the current user is allowed to run this function
                    $context = context_coursecat::instance($existing_course->category);
                    require_capability('moodle/course:delete', $context);

                    if (!delete_course((object) $existing_course, false)){
                        throw new Exception('Unable to delete course: ' . $course['idnumber']);
                    } else {
                        $resultcourses[] = array('idnumber' => $existing_course->idnumber, 'action' => 'delete');
                    }
                }

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('remove_course', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('idnumber' => $course['idnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('remove_course', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('idnumber' => $course['idnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('remove_course', 'EXCEPTION! ' . $e->getMessage());
                $resultcourses[] = array('idnumber' => $course['idnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        return $resultcourses;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function remove_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'idnumber'  => new external_value(PARAM_RAW, 'id number'),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

   /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function create_group_parameters() {
        return new external_function_parameters(
            array(
                'groups' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'courseidnumber' => new external_value(PARAM_RAW, 'course id number', VALUE_OPTIONAL),
                            'groupidnumber' => new external_value(PARAM_RAW, 'group id number'),
                            'groupdescription' => new external_value(PARAM_RAW, 'group description'),
                            'timestart' => new external_value(PARAM_INT, 'timestamp of the start date of the class', VALUE_OPTIONAL),
                            'timeend' => new external_value(PARAM_INT, 'timestamp of the finish date of the class', VALUE_OPTIONAL),
                            'tutorname' => new external_value(PARAM_TEXT, 'tutor name', VALUE_OPTIONAL),
                            'roleid' => new external_value(PARAM_INT, 'id of the role (tutor, non-editing tutor) to assign', VALUE_OPTIONAL),
                            'link_courses' => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),
                        )
                    ), 'groups to create'
                )
            )
        );
    }

    /**
     * Create groups
     * @param array $groups
     * @return array groups (id and shortname only)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */

    public static function create_group($groups) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/group/lib.php");

        self::log_for_artena('create_group','BEGIN',1);
        self::log_for_artena('create_group',print_r($groups,1));

        $resultgroups = array();
        $params = self::validate_parameters(self::create_group_parameters(),array('groups' => $groups));

        foreach ($params['groups'] as $group) {
            try {
                $transaction = $DB->start_delegated_transaction();
                self::log_for_artena('create_group',print_r($group,1));

                // Check if this is a create or update request
                if ($group['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $group['fullname'], 'shortname' => $group['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $group['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $group['courseidnumber']));
                }

                if (false === $existing_course) {
                    //throw new Exception('The course associated with the group does not exist');
                    $resultgroups[] = array('id' => -1, 'name' => $group['name'], 'coursename' => $group['fullname'], 'action' => 'skip');
                    continue;
                }

                if (trim($group['groupidnumber']) == '') {
                    throw new Exception('Invalid group name');
                }

                // now security checks
                $context = context_course::instance($existing_course->id, IGNORE_MISSING);
                require_capability('moodle/course:managegroups', $context);

                $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $group['groupidnumber']));
                self::log_for_artena('create_group',"GROUP:\n".print_r($existing_group,1));

                $new_tutor = false;
                if (false === $existing_group) {    // create
                    $new_tutor = true;
                    $new_group = array();
                    $new_group['courseid'] = $existing_course->id;
                    $new_group['name'] = $group['groupidnumber'];
                    $new_group['description'] = $group['groupdescription'];
                    $new_group['descriptionformat'] = FORMAT_HTML;
                    $new_group['id'] = groups_create_group((object)$new_group, false);
                    self::log_for_artena('create_group',"new group=\n" . print_r($new_group,1));
                    $resultgroups[] = array('id' => $new_group['id'], 'name' => $new_group['name'], 'coursename' => $group['fullname'], 'action'=> 'insert');

                } else {    // update
                    self::log_for_artena('create_group','UPDATE');
                    $existing_group->description = $group['groupdescription'];
                    $existing_group->descriptionformat = FORMAT_HTML;
                    self::log_for_artena('create_group',"existing group=\n" . print_r($existing_group,1));
                    groups_update_group((object)$existing_group, false);

                    $resultgroups[] = array('id' => $existing_group->id, 'name' => $group['groupidnumber'], 'coursename' => $group['fullname'], 'action'=> 'update');
                }

                if (('' != $group['tutorname']) && (0 < $group['roleid'])) {
                    $tutor = $DB->get_record('user', array('username' => $group['tutorname']));

                    if ($tutor) {

                      if (false == $new_tutor) {
                          self::log_for_artena('create_group',"existing tutor=\n" . print_r($tutor,1));
                          // check for existing enrolment for this tutor
                         $enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $existing_course->id));
                          if (false === $enrolment_configuration) {
                              $new_tutor = true;
                          } else if (false === ($user_enrolment = $DB->get_record('user_enrolments', array('userid' => $tutor->id, 'enrolid' => $enrolment_configuration->id)))) {
                              $new_tutor = true;
                          }
                      }

                      if (true == $new_tutor) {
                          self::log_for_artena('create_group',"new tutor assignment");
                          self::log_for_artena('create_group',"tutor=\n" . print_r($tutor,1));
                          self::log_for_artena('create_group',"tutor context=\n" . print_r($context,1));
                          // not found, add tutor enrolment
                          //

                          // Ensure the current user is allowed to run this function in the enrolment context
                          self::log_for_artena('create_group',"t0");
                          require_capability('moodle/role:assign', $context);
                          self::log_for_artena('create_group',"t1");
                          role_assign($group['roleid'], $tutor->id, $context->id);
                          self::log_for_artena('create_group',"t2");

                          // retrieve new user_enrolments record to update with start/finish dates
                          //$enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => $group['roleid'], 'courseid' => $existing_course->id));
                          $enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $existing_course->id));
                          self::log_for_artena('create_group',"t3");
                          self::log_for_artena('create_group',"enrolment configuration=\n" . print_r($enrolment_configuration,1));

                          // Ensure the current user is allowed to run this function in the enrolment context
                          require_capability('enrol/manual:enrol', $context);
                          self::log_for_artena('create_group',"t4");

                          // create the user_enrolments record (lib/enrollib.php)
                          $plugin = enrol_get_plugin('manual');
                          $plugin->enrol_user($enrolment_configuration, $tutor->id, $group['roleid'], $group['timestart'], $group['timeend']);
                          self::log_for_artena('create_group',"t5");

                          if (false === $existing_group) {
                            $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $group['groupidnumber']));
                          }
                          groups_add_member($existing_group->id, $tutor->id);
                      }
                    } else {
                      // bad tutor reference in object; do not stop group creation
                      // perhaps send message back in a later release?
                    }
                }

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('create_group', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'coursename' => $group['fullname'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('create_group', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'coursename' => $group['fullname'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('create_group', 'EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'coursename' => $group['fullname'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        self::log_for_artena('create_group',"END\n".print_r($resultgroups,1));
        return $resultgroups;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function create_group_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'id' => new external_value(PARAM_INT, 'group id'),
                    'name' => new external_value(PARAM_TEXT, 'group name'),
                    'coursename' => new external_value(PARAM_TEXT, 'course fullname'),
                    'action' => new external_value(PARAM_TEXT, 'action performed (insert,update,skip,error)'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

   /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function remove_group_parameters() {
        return new external_function_parameters(
            array(
                'groups' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'courseidnumber' => new external_value(PARAM_RAW, 'course id number', VALUE_OPTIONAL),
                            'groupidnumber' => new external_value(PARAM_RAW, 'group id number'),
                            'link_courses' => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),
                        )
                    ), 'groups to remove'
                )
            )
        );
    }

    /**
     * Remove groups
     * @param array $groups
     * @return array groups (id number and result only)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */

    public static function remove_group($groups) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/group/lib.php");

        self::log_for_artena('remove_group','BEGIN',1);
        self::log_for_artena('remove_group',print_r($groups,1));

        $resultgroups = array();
        $params = self::validate_parameters(self::remove_group_parameters(),array('groups' => $groups));

        $transaction = $DB->start_delegated_transaction();

        foreach ($params['groups'] as $group) {
            try {

                $transaction = $DB->start_delegated_transaction();
                self::log_for_artena('create_group',print_r($group,1));

                // Check if the expected associated course exists
                if ($group['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $group['fullname'], 'shortname' => $group['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $group['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $group['courseidnumber']));
                }

                if (false === $existing_course) {
                    $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'action' => 'skip');
                    continue;
                }

                if (trim($group['groupidnumber']) == '') {
                    throw new Exception('Invalid group name');
                }

                // now security checks
                $context = context_course::instance($existing_course->id, IGNORE_MISSING);
                require_capability('moodle/course:managegroups', $context);

                $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $group['groupidnumber']));
                self::log_for_artena('remove_group',"GROUP:\n".print_r($existing_group,1));

                if (false === $existing_group) {
                    $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'action' => 'skip');
                    continue;
                }
                groups_delete_group($existing_group->id);

                $resultgroups[] = array('id' => $existing_group->id, 'name' => $group['groupidnumber'], 'action'=> 'delete');

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('remove_group', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('remove_group', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('remove_group', 'EXCEPTION! ' . $e->getMessage());
                $resultgroups[] = array('id' => -1, 'name' => $group['groupidnumber'], 'action'=> 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        self::log_for_artena('remove_group',"END\n".print_r($resultgroups,1));
        return $resultgroups;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */

    public static function remove_group_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'id' => new external_value(PARAM_INT, 'group id'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'action' => new external_value(PARAM_TEXT, 'action performed (delete,skip,error)'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_user_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'username'    => new external_value(PARAM_RAW, 'Username policy is defined in Moodle security config'),
                            'password'    => new external_value(PARAM_RAW, 'Plain text password consisting of any characters'),
                            'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                            'lastname'    => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                            'email'       => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
                            'emailvalid'  => new external_value(PARAM_RAW, 'An indication of whether the email is real or a placeholder (1=valid)', VALUE_OPTIONAL, '1'),
                            'auth'        => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc', VALUE_DEFAULT, 'manual', NULL_NOT_ALLOWED),
                            'idnumber'    => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution', VALUE_DEFAULT, ''),
                            'mailformat'  => new external_value(PARAM_INT, 'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                            'phone1'    => new external_value(PARAM_RAW, 'Primary phone number', VALUE_OPTIONAL),
                            'phone2'    => new external_value(PARAM_RAW, 'Secondary phone number', VALUE_OPTIONAL),
                            'confirmed'  => new external_value(PARAM_INT, 'Binary indication the student data is confirmed'),
                            'lang'        => new external_value(PARAM_SAFEDIR, 'Language code such as "en", must exist on server', VALUE_DEFAULT, $CFG->lang, NULL_NOT_ALLOWED),
                            'theme'       => new external_value(PARAM_SAFEDIR, 'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                            'timezone'    => new external_value(PARAM_ALPHANUMEXT, 'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                            'mailformat'  => new external_value(PARAM_INT, 'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                            'description' => new external_value(PARAM_TEXT, 'User profile description, no HTML', VALUE_OPTIONAL),
                            'city'        => new external_value(PARAM_NOTAGS, 'Home city of the user', VALUE_OPTIONAL),
                            'country'     => new external_value(PARAM_ALPHA, 'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                            'preferences' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the preference'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the preference')
                                    )
                                ), 'User preferences', VALUE_OPTIONAL),
                            'customfields' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'The name of the custom field'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                                    )
                                ), 'User custom fields (also known as user profile fields)', VALUE_OPTIONAL)
                        )
                    ), 'users to add'
                )
            )
        );
    }

    /**
     * Create one or more users
     *
     * @param array $users An array of users to create.
     * @return array An array of arrays
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function create_user($users) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/user/lib.php");
        require_once($CFG->dirroot."/user/profile/lib.php"); //required for customfields related function
                                                             //TODO: move the functions somewhere
                                                             //they are "user" related

        self::log_for_artena('create_user','BEGIN',1);
        // Ensure the current user is allowed to run this function
        $context = context_system::instance();
        require_capability('moodle/user:create', $context);

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::create_user_parameters(), array('users'=>$users));
        $availableauths  = core_component::get_plugin_list('auth');
        unset($availableauths['mnet']);       // these would need mnethostid too
        unset($availableauths['webservice']); // we do not want new webservice users for now

        $availablethemes = core_component::get_plugin_list('theme');
        $availablelangs  = get_string_manager()->get_list_of_translations();

        $userids = array();
        foreach ($params['users'] as $user) {
            try {
                self::log_for_artena('create_user',print_r($user,1));
                $transaction = $DB->start_delegated_transaction();

                // Make sure auth is valid
                if (empty($availableauths[$user['auth']])) {
                    throw new Exception('Invalid authentication type: '.$user['auth']);
                }

                // Make sure lang is valid
                if (empty($availablelangs[$user['lang']])) {
                    throw new Exception('Invalid language code: '.$user['lang']);
                }

                // Make sure lang is valid
                if (!empty($user['theme']) && empty($availablethemes[$user['theme']])) { //theme is VALUE_OPTIONAL,
                                                                                         // so no default value.
                                                                                         // We need to test if the client sent it
                                                                                         // => !empty($user['theme'])
                    throw new Exception('Invalid theme: '.$user['theme']);
                }

                // make sure there is no data loss during truncation
                $truncated = truncate_userinfo($user);
                foreach ($truncated as $key=>$value) {
                        if ($truncated[$key] !== $user[$key]) {
                            throw new Exception('Property: '.$key.' is too long: '.$user[$key]);
                        }
                }

                // Make sure that the username doesn't already exist
                //if ($DB->record_exists('user', array('username'=>$user['username'], 'mnethostid'=>$CFG->mnet_localhost_id))) {
                $existing_user = $DB->get_record('user', array('username' => $user['username']));
                if (false === $existing_user) {   // new user, create

                    // NOTE: should we check for a deleted/suspended user (deleted flag in the user table) that correlates
                    // with this user, and bring that account back to active instead of creating a new one?
                    self::log_for_artena('create_user', 'new user');

                    $user['confirmed'] = true;
                    $user['mnethostid'] = $CFG->mnet_localhost_id;
                    $user['id'] = self::create_user_record($user);

                    // custom fields
                    if(!empty($user['customfields'])) {
                        foreach($user['customfields'] as $customfield) {
                            $user["profile_field_".$customfield['type']] = $customfield['value']; //profile_save_data() saves profile file
                                                                                                    //it's expecting a user with the correct id,
                                                                                                    //and custom field to be named profile_field_"shortname"
                        }
                        profile_save_data((object) $user);
                    }

                    //preferences
                    if (!empty($user['preferences'])) {
                        foreach($user['preferences'] as $preference) {
                            set_user_preference($preference['type'], $preference['value'],$user['id']);
                        }
                    }

                    // remove email as a valid message provider where the provided email is a placeholder
                    if (0 == $user['emailvalid']) {
                        //$DB->delete_records_select('user_preferences', "userid IN (".$user['id'].") and value='email'");
                            $email_preferences = $DB->get_recordset_select('user_preferences', "userid=".$user['id']." and value like '%email%'"); // CATALYST - this should use $DB->sql_like function
                              foreach ($email_preferences as $pref) {
                          $value = 'none';
                          $value_set = explode(',', $pref->value);
                          unset($value_set['email']);
                          if (0 < count($value_set))
                            $value = implode(',', $value_set);

                          $pref->value = $value;
                          $DB->update_record('user_preferences', $pref);
                        }
                    }

                    $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>$user['id'], 'username'=>$user['username'], 'action'=>'add');

                } else {    // existing user, respond as if a successful creation
                    /*
                    $existing_user->firstname = $user['firstname'];
                    $existing_user->lastname = $user['lastname'];
                    $existing_user->email = $user['email'];
                    $DB->update_record('user', $existing_user);
                    */
                    $userids[] = array('artenaid'=>$user['artenaid'], 'id' => $existing_user->id, 'username' => $existing_user->id, 'action'=>'update');
                }
                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('create_user', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>-1, 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('create_user', 'moodle EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>$user['id'], 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('create_user', 'EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>$user['id'], 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        return $userids;
    }

	public static function create_user_record($user, $triggerevent = true) {
		global $DB;

		// set the timecreate field to the current time
		if (!is_object($user)) {
				$user = (object)$user;
		}

		// clear password field if not relevant to this record
		if (isset($user->password) && ($user->auth == 'ldap')){
			unset($user->password);
		}

		// save the password in a temp value for later
    if (!empty($user->password)) {
      if (!is_null($user->password)) {
        $userpassword = $user->password;
      }
      unset($user->properties['password']);
    }

		$user->timecreated = time();
		$user->timemodified = $user->timecreated;

self::log_for_artena('create_user', "create_user_record user=\n" . print_r($user, 1));
		// insert the user into the database
		$newuserid = $DB->insert_record('user', $user);

		// trigger user_created event on the full database user row if required
		$newuser = $DB->get_record('user', array('id' => $newuserid));

		// create USER context for this user
        context_user::instance($newuserid);

		// update user password if necessary
		if (isset($userpassword)) {
			update_internal_user_password($newuser, $userpassword);
		}

        if ($triggerevent) {
            \core\event\user_created::create_from_userid($newuserid)->trigger();
        }

		return $newuserid;

	}

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function create_user_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'username' => new external_value(PARAM_RAW, 'user name'),
                    'action' => new external_value(PARAM_RAW, 'action performed (add, edit, error)'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_user_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'username'    => new external_value(PARAM_RAW, 'Username policy is defined in Moodle security config'),
                        )
                    ), 'users to add'
                )
            )
        );
    }

    /**
     * Delete one or more users
     *
     * @param array $users An array of users to create.
     * @return array An array of arrays
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function remove_user($users) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/user/lib.php");

        // Ensure the current user is allowed to run this function
        $context = context_system::instance();
        require_capability('moodle/user:delete', $context);

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::remove_user_parameters(), array('users'=>$users));
        $availableauths  = core_component::get_plugin_list('auth');
        unset($availableauths['mnet']);       // these would need mnethostid too
        unset($availableauths['webservice']); // we do not want new webservice users for now

        $userids = array();
        foreach ($params['users'] as $user) {
            try {
                self::log_for_artena('remove_user',print_r($user,1));
                $transaction = $DB->start_delegated_transaction();

                // make sure there is no data loss during truncation
                $truncated = truncate_userinfo($user);
                foreach ($truncated as $key=>$value) {
                    if ($truncated[$key] !== $user[$key]) {
                        throw new Exception('Property: '.$key.' is too long: '.$user[$key]);
                    }
                }

                // check for user's existence
                $existing_user = $DB->get_record('user', array('username' => $user['username']));
                if (false === $existing_user) {   // unknown user
                    throw new Exception('Unknown user: '.$user['username']);
                } else {    // existing user, delete
                    user_delete_user($existing_user);
                    $userids[] = array('artenaid'=>$user['artenaid'], 'id' => $existing_user->id, 'username' => $existing_user->username, 'action' => 'delete');
                }
                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('remove_user', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>-1, 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('remove_user', 'moodle EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>-1, 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('remove_user', 'EXCEPTION! ' . $e->getMessage());
                $userids[] = array('artenaid'=>$user['artenaid'], 'id'=>-1, 'username'=>$user['username'], 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        return $userids;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function remove_user_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'username' => new external_value(PARAM_RAW, 'user name'),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_enrol_parameters() {
        return new external_function_parameters(
            array(
                'assignments' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'username'      => new external_value(PARAM_RAW, 'The user that is going to be assigned'),
                            'fullname'      => new external_value(PARAM_TEXT, 'full name'),
                            'shortname'     => new external_value(PARAM_TEXT, 'course short name'),
                            'idnumber'      => new external_value(PARAM_RAW, 'id of the course to assign to the user'),
                            'groupidnumber' => new external_value(PARAM_RAW, 'id of the group to assign to the user, if applicable', VALUE_OPTIONAL),
                            'roleid'        => new external_value(PARAM_INT, 'id of the role (student,tutor) to assign'),
                            'timestart'     => new external_value(PARAM_INT, 'timestamp at which to start the enrolment'),
                            'timeend'       => new external_value(PARAM_INT, 'timestamp at which to finish the enrolment'),
                            'link_courses'  => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    /**
     * Manual role assignments to users
     *
     * @param $assignments
     * @return null
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @internal param array $assignment An array of manual role assignment
     */

    public static function create_enrol($assignments) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/grouplib.php');

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::create_enrol_parameters(), array('assignments'=>$assignments));

        self::log_for_artena('create_enrol','BEGIN',1);
        $enrolments = array();
        foreach ($params['assignments'] as $assignment) {
            try {
                self::log_for_artena('create_enrol',print_r($assignment,1));
                $transaction = $DB->start_delegated_transaction();

                //self::log_for_artena('create_enrol','get user');
                $existing_user = $DB->get_record('user', array('username' => $assignment['username']));
                if (false === $existing_user) {   // unknown user
                    throw new Exception('Unknown user: '.$assignment['username']);
                }

                $link_retrieve = false;
                //self::log_for_artena('create_enrol','get course');
                if ($assignment['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $assignment['fullname'], 'shortname' => $assignment['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $assignment['shortname']));
                    if (false === $existing_course) {   // unknown course
                        throw new Exception('Unknown course: '.$assignment['idnumber']);
                    } else {
                      $link_retrieve = true;
                    }
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $assignment['idnumber']));
                    if (false === $existing_course) {   // unknown course
                        throw new Exception('Unknown course: '.$assignment['idnumber']);
                    }
                }
                //self::log_for_artena('create_enrol','get context');
                $context = context_course::instance($existing_course->id);
                if (false === $context) {   // unknown context
                    throw new Exception('Context does not exist for course: '.$existing_course->id);
                }

                $new_enrolment = false;
                // check for an existing enrolment for this user/course pairing
                // existing enrolment ==
                // 1) user has an enrolment in the specified course
                // 2) the end date for that enrolment is after the start date for the input enrolment
                //
                self::log_for_artena('create_enrol','get enrol');
                if (false === ($enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => $assignment['roleid'], 'courseid' => $existing_course->id)))) {
                    $new_enrolment = true;
                } else {
                    self::log_for_artena('create_enrol','get user_enrolments');
                    if (false === ($user_enrolment = $DB->get_record('user_enrolments', array('userid' => $existing_user->id, 'enrolid' => $enrolment_configuration->id)))) {
                        $new_enrolment = true;
                    } else {
                        if ($user_enrolment->timeend > 0 && $user_enrolment->timeend < $assignment['timestart']) {
                            $new_enrolment = true;
                        }
                    }
                }

                // need to temporarily make course accessible/visible in order to perform enrolment
                $flip_visible = false;

                if (0 == $existing_course->visible) {
                  $existing_course->visible = 1;
                  $DB->update_record('course', $existing_course);
                  $flip_visible = true;
                }

                if ($new_enrolment) {   // create
                    self::log_for_artena('create_enrol','new enrolment');

                    // Ensure the current user is allowed to run this function in the enrolment context
                    require_capability('moodle/role:assign', $context);
                    role_assign($assignment['roleid'], $existing_user->id, $context->id);

                    // retrieve new user_enrolments record to update with start/finish dates
                    $enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => $assignment['roleid'], 'courseid' => $existing_course->id));

                    // Ensure the current user is allowed to run this function in the enrolment context
                    require_capability('enrol/manual:enrol', $context);

                    // create the user_enrolments record (lib/enrollib.php)
                    $plugin = enrol_get_plugin('manual');
                    $plugin->enrol_user($enrolment_configuration, $existing_user->id, $assignment['roleid'], $assignment['timestart'], $assignment['timeend']);
                    $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $existing_user->username, 'coursename' => $existing_course->fullname, 'action' => 'add');

                } else {    // update
                    self::log_for_artena('create_enrol','existing enrolment');

                    // update user_enrolments with the start/finish timestamps for this enrolment
                    $user_enrolment = $DB->get_record('user_enrolments', array('userid' => $existing_user->id, 'enrolid' => $enrolment_configuration->id));
                    $user_enrolment->timestart = $assignment['timestart'];
                    $user_enrolment->timeend = $assignment['timeend'];
                    $user_enrolment->status = ENROL_USER_ACTIVE;
                    //self::log_for_artena('create_enrol','update user_enrolments');
                    $DB->update_record('user_enrolments', $user_enrolment);
                    $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $existing_user->username, 'coursename' => $existing_course->fullname, 'action' => 'update');
                }

                // assign/unassign group, if required
                //self::log_for_artena('create_enrol','get group');
                $existing_memberships = groups_get_user_groups($existing_course->id, $existing_user->id);
                if (!empty($assignment['groupidnumber'])) {
                    $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $assignment['groupidnumber']));

                    self::log_for_artena('create_enrol',print_r($existing_group,1));
                    if (false === $existing_group) {
                        $new_group = array();
                        $new_group['fullname'] = $assignment['fullname'];
                        $new_group['shortname'] = $assignment['shortname'];
                        $new_group['courseidnumber'] = $assignment['idnumber'];
                        $new_group['groupidnumber'] = $assignment['groupidnumber'];
                        $new_group['groupdescription'] = '';
                        $new_group['link_courses'] = $assignment['link_courses'];
                        $paramsgroup = array();
                        $paramsgroup[] = $new_group;
                        self::create_group($paramsgroup);
                        $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $assignment['groupidnumber']));
                        if (false === $existing_group) {
                            throw new Exception('Unknown group: '.$assignment['groupidnumber']);
                        }
                    }
					/*
                    if (count($existing_memberships)) {
                        $existing_memberships = $existing_memberships[0];
                        foreach ($existing_memberships as $membership_id) {
                            if ($membership_id != $existing_group->id) {
                                groups_remove_member($membership_id, $existing_user->id);
                            }
                        }
                    }
					*/
                    groups_add_member($existing_group->id, $existing_user->id);
                } else {
					/*
                    if (count($existing_memberships)) {
                        $existing_memberships = $existing_memberships[0];
                        foreach ($existing_memberships as $membership_id) {
                            groups_remove_member($membership_id, $existing_user->id);
                        }
                    }
					*/
                }

                // reset accessibility/visibility
                if (true == $flip_visible) {
                  if (!$link_retrieve) {
                    //self::log_for_artena('create_enrol','get flip_visible course');
                    $existing_course = $DB->get_record('course', array('idnumber' => $assignment['idnumber']));
                  } else {
                    //self::log_for_artena('create_enrol','get flip_visible linked course');
                    //$existing_course = $DB->get_record('course', array('fullname' => $assignment['fullname'], 'shortname' => $assignment['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $assignment['shortname']));
                  }
                  $existing_course->visible = 0;
                  //self::log_for_artena('create_enrol','update course');
                  $DB->update_record('course', $existing_course);
                }
                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('create_enrol','parameter EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username'=>'', 'coursename'=>'', 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('create_enrol', 'moodle EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username'=>'', 'coursename'=>'', 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('create_enrol', 'EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username'=>'', 'coursename'=>'', 'action'=>'error', 'message'=>$e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        //self::log_for_artena('create_enrol','END');
        return $enrolments;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function create_enrol_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'username'   => new external_value(PARAM_RAW, 'user name'),
                    'coursename' => new external_value(PARAM_RAW, 'course fullname'),
                    'action'   => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_enrol_parameters() {
        return new external_function_parameters(
            array(
                'assignments' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'username'      => new external_value(PARAM_RAW, 'The user that is going to be assigned'),
                            'fullname'      => new external_value(PARAM_TEXT, 'course full name'),
                            'shortname'     => new external_value(PARAM_TEXT, 'course short name'),
                            'idnumber'      => new external_value(PARAM_RAW, 'id of the course to assign to the user'),
							'groupidnumber' => new external_value(PARAM_RAW, 'group id number'),
                            'roleid'        => new external_value(PARAM_INT, 'id of the role (student,tutor) to assign'),
                            'action'        => new external_value(PARAM_RAW, 'indication to delete or suspend the enrolment'),
                            'link_courses'  => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0:treat all as distinct)', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    /**
     * Manual role assignments to users
     *
     * @param $assignments
     * @return null
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @internal param array $assignment An array of manual role assignment
     */

    public static function remove_enrol($assignments) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/grouplib.php');

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::remove_enrol_parameters(), array('assignments'=>$assignments));

        self::log_for_artena('remove_enrol', 'BEGIN', 1);
        foreach ($params['assignments'] as $assignment) {
            try {

                $transaction = $DB->start_delegated_transaction();

                $existing_user = $DB->get_record('user', array('username' => $assignment['username']));
                if (false === $existing_user) {   // unknown user
                    throw new Exception('Unknown user: '.$assignment['username']);
                }

                if ($assignment['link_courses']) {
                    //$existing_course = $DB->get_record('course', array('fullname' => $assignment['fullname'], 'shortname' => $assignment['shortname']));
                    $existing_course = $DB->get_record('course', array('shortname' => $assignment['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $assignment['idnumber']));
                }

                if (false === $existing_course) {   // unknown course
                    throw new Exception('Unknown course: '.$assignment['idnumber']);
                }

                $context = context_course::instance($existing_course->id);
                if (false === $context) {   // unknown context
                    throw new Exception('Context does not exist for course: '.$existing_course->id);
                }

                if (false === ($enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => $assignment['roleid'], 'courseid' => $existing_course->id)))) {
                    throw new Exception('Unknown enrolment configuration for:' . $assignment['idnumber']);
                }
                if (false === ($user_enrolment = $DB->get_record('user_enrolments', array('userid' => $existing_user->id, 'enrolid' => $enrolment_configuration->id)))) {
                    throw new Exception('Unknown enrolment for student: ' . $assignment['username'] . ' in course: ' . $assignment['idnumber']);
                }

                switch ($assignment['action']) {

                    case 'delete':
                        // Ensure the current user is allowed to run this function in the enrolment context
                        require_capability('enrol/manual:unenrol', $context);

                        // retrieve new user_enrolments record to update with start/finish dates
                        $enrolment_configuration = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => $assignment['roleid'], 'courseid' => $existing_course->id));

                        // remove the user_enrolments record (lib/enrollib.php)
                        $plugin = enrol_get_plugin('manual');
                        $plugin->unenrol_user($enrolment_configuration, $existing_user->id);
                        break;

                    case 'suspend':
                        // Ensure the current user is allowed to run this function in the enrolment context
                        require_capability('enrol/manual:manage', $context);

                        // suspend enrolment
                        $DB->set_field('user_enrolments', 'status', ENROL_USER_SUSPENDED, array('enrolid'=>$enrolment_configuration->id, 'userid'=>$existing_user->id));

                        // remove group memberships for this enrolment
                        $existing_memberships = groups_get_user_groups($existing_course->id, $existing_user->id);
                        if (count($existing_memberships)) {
                            $existing_memberships = $existing_memberships[0];
                            foreach ($existing_memberships as $membership_id) {
                              groups_remove_member($membership_id, $existing_user->id);
                            }
                        }
                        break;

                    default:
                        throw new Exception('Unknown action');
                        break;
                }

                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $assignment['username'], 'coursename' => $existing_course->fullname, 'action' => 'delete');

                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('remove_enrol','parameter EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $assignment['username'], 'coursename' => $assignment['fullname'], 'action' => 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('remove_enrol', 'moodle EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $assignment['username'], 'coursename' => $assignment['fullname'], 'action' => 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('remove_enrol', 'EXCEPTION! ' . $e->getMessage());
                $enrolments[] = array('artenaid' => $assignment['artenaid'], 'username' => $assignment['username'], 'coursename' => $assignment['fullname'], 'action' => 'error', 'message' => $e->getMessage());
                self::rollback_suppress_exception($transaction);
            }
        }

        return $enrolments;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function remove_enrol_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'username'   => new external_value(PARAM_RAW, 'user name'),
                    'coursename'   => new external_value(PARAM_RAW, 'course fullname'),
                    'action'   => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }


   /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_grades_parameters() {
        return new external_function_parameters(
            array(
                'academicrecords' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'username' => new external_value(PARAM_RAW, 'The user whose grade will be retrieved'),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_INT, 'timestamp when the enrolment starts', VALUE_OPTIONAL),
                            'finishdate' => new external_value(PARAM_INT, 'timestamp when the enrolment ends', VALUE_OPTIONAL),
                            'link_courses' => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0: treat all as distinct)', VALUE_OPTIONAL),
                        )
                    ), 'courses for which to retrieve grades'
                )
            )
        );
    }

    /**
     * Get grades
     * @param $academicrecords
     * @return array grades (course, student, final grade)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @internal param array $grades
     */
    public static function get_grades($academicrecords) {
        global $CFG, $DB;
        //self::log_for_artena('get_grades','BEGIN ' . print_r($courses,1),1);

        $params = self::validate_parameters(self::get_grades_parameters(), array('academicrecords'=>$academicrecords));

        $transaction = $DB->start_delegated_transaction();
        foreach ($params['academicrecords'] as $ar) {

            // get student
            $student = $DB->get_record('user', array('username' => $ar['username']));
            if (false === $student) {
                continue;
            }

            // get course
            if ($ar['link_courses']) {
                $existing_course = $DB->get_record('course', array('shortname' => $ar['shortname']));
            } else {
                $existing_course = $DB->get_record('course', array('idnumber' => $ar['idnumber']));
            }

            if (false === $existing_course) {
                continue;
            }

            // get grades
            $grade_item = $DB->get_record('grade_items', array('courseid' => $existing_course->id, 'itemtype' => 'course'));
            $grades = $DB->get_records('grade_grades', array('itemid' => $grade_item->id, 'userid' => $student->id));

            foreach ($grades as $grade) {

                // populate return structure
                //
                $resultgrades[] = array(
                    'courseid' => $ar['idnumber'],
                    'studentid' => $student->idnumber,
                    'startdate' => $ar['startdate'],
                    'finishdate' => $ar['finishdate'],
                    'gradedate' => $grade->timemodified,
                    'result' => $grade->finalgrade,
                    );
            }
        }
        self::log_for_artena('get_grades','END ' . print_r($resultgrades,1));
        $transaction->allow_commit();

        return $resultgrades;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'courseid' => new external_value(PARAM_RAW, 'course id number'),
                    'studentid'  => new external_value(PARAM_RAW, 'student id number'),
                    'startdate' => new external_value(PARAM_INT, 'timestamp when the enrolment starts', VALUE_OPTIONAL),
                    'finishdate' => new external_value(PARAM_INT, 'timestamp when the enrolment ends', VALUE_OPTIONAL),
                    'gradedate' => new external_value(PARAM_INT, 'timestamp when the grade was entered', VALUE_OPTIONAL),
                    'result'  => new external_value(PARAM_FLOAT, 'final grade'),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_grades_after_parameters() {
        return new external_function_parameters(
            array(
                'parameters' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'retrievedate' => new external_value(PARAM_INT, 'timestamp after which to retrieve record inserts/updates'),
                        )
                    ), 'parameters by which to retrieve grades'
                )
            )
        );
    }

    /**
     * Get grades
     * @param $academicrecords
     * @return array grades (course, student, final grade)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @internal param array $grades
     */
    public static function get_grades_after($parameters) {
        global $CFG, $DB;

        self::log_for_artena('get_grades_after','BEGIN',1);

        $params = self::validate_parameters(self::get_grades_after_parameters(), array('parameters'=>$parameters));
        self::log_for_artena('get_grades_after',"params validated:\n".print_r($params,1));
        $retrievedate = $params['parameters'][0]['retrievedate'];

        $resultgrades = array();
        try {

            $grades = $DB->get_recordset_select('grade_grades', "timemodified >= ".$retrievedate);

            self::log_for_artena('get_grades_after',"grades=\n".print_r($grades,1));
            foreach ($grades as $gg) {

                $grade_rec = array(
                    'studentname' => '',
                    'coursename' => '',
                    'startdate' => '',
                    'finishdate' => '',
                    'gradedate' => $gg->timemodified,
                    'result' => $gg->finalgrade,
                    'action' => 'retrieve',
                    );

                // get student
                $grade_student = $DB->get_record('user', array('id' => $gg->userid));
                $grade_rec['studentname'] = $grade_student->username;

                // get course
                $grade_item = $DB->get_record('grade_items', array('id' => $gg->itemid, 'itemtype' => 'course'));

                if (!$grade_item) {
                    // not the course final grade
                    continue;
                }

                $grade_course = $DB->get_record('course', array('id' => $grade_item->courseid));
                $grade_rec['coursename'] = $grade_course->shortname;

                // get enrolment
                $grade_enrolconfig = $DB->get_record('enrol', array('enrol' => 'manual', 'roleid' => 5, 'courseid' => $grade_course->id));
                $grade_enrolment = $DB->get_record('user_enrolments', array('userid' => $grade_student->id, 'enrolid' => $grade_enrolconfig->id));
                $grade_rec['startdate'] = $grade_enrolment->timestart;
                $grade_rec['finishdate'] = $grade_enrolment->timeend;

                $resultgrades[] = $grade_rec;
            }

        } catch (invalid_parameter_exception $e) {
            self::log_for_artena('get_grades_after', 'invalid parameter EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }
        catch (moodle_exception $e) {
            self::log_for_artena('get_grades_after', 'moodle EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }
        catch (Exception $e) {
            self::log_for_artena('get_grades_after', 'EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }

        self::log_for_artena('get_grades_after',"result=\n". print_r($resultgrades,1));
        self::log_for_artena('get_grades_after','END');

        return $resultgrades;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_grades_after_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'studentname'  => new external_value(PARAM_RAW, 'student username'),
                    'coursename' => new external_value(PARAM_RAW, 'course shortname'),
                    'startdate' => new external_value(PARAM_INT, 'timestamp when the enrolment starts', VALUE_OPTIONAL),
                    'finishdate' => new external_value(PARAM_INT, 'timestamp when the enrolment ends', VALUE_OPTIONAL),
                    'gradedate' => new external_value(PARAM_INT, 'timestamp when the grade was entered', VALUE_OPTIONAL),
                    'result'  => new external_value(PARAM_FLOAT, 'final grade'),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_attendance_parameters() {
        return new external_function_parameters(
            array(
                'enrolments' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                            'username' => new external_value(PARAM_RAW, 'The user whose grade will be retrieved'),
                            'coursename' => new external_value(PARAM_TEXT, 'course short name'),
                            'groupname' => new external_value(PARAM_TEXT, 'group short name'),
                            'link_courses' => new external_value(PARAM_INT, 'ARTENA FIELD (1: link courses of same name, 0: treat all as distinct)', VALUE_OPTIONAL),
                        )
                    ), 'courses for which to retrieve attendance'
                )
            )
        );
    }

    /**
     * Get attendance
     * @param $enrolments
     * @return array attendance (course, student, date, attendance status)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @internal param array $attendance
     */
    public static function get_attendance($enrolments) {
        global $CFG, $DB;

        self::log_for_artena('get_attendance','BEGIN',1);

        $plugin = core_plugin_manager::instance()->get_plugin_info('mod_attendance');
        self::log_for_artena('get_attendance',"plugins=\n" . print_r($plugins,1));
        if (!$plugin) {
            $resultattendance[] = array(
                'username' => '',
                'coursename' => '',
                'groupname' => '',
                'action' => 'skip',
                'message' => 'attendance plugin not installed',
                );

            return $resultattendance;
        }

        $params = self::validate_parameters(self::get_attendance_parameters(), array('enrolments'=>$enrolments));

        foreach ($params['enrolments'] as $enr) {
            try {
                $transaction = $DB->start_delegated_transaction();

                // get student
                $student = $DB->get_record('user', array('username' => $enr['username']));
                if (false === $student) {
                    continue;
                }

                // get course
                if ($enr['link_courses']) {
                    $existing_course = $DB->get_record('course', array('shortname' => $enr['shortname']));
                } else {
                    $existing_course = $DB->get_record('course', array('idnumber' => $enr['courseidnumber']));
                }

                if (false === $existing_course) {
                    continue;
                }

                if  ($enr['groupidnumber'] == ''){
                    continue;
                }
                $existing_group = $DB->get_record('groups', array('courseid' => $existing_course->id, 'name' => $enr['groupidnumber']));

                // get attendance
                $attendance_item = $DB->get_record('attendance', array('course' => $existing_course->id));

                $statuses_hash = array();
                $attendance_statuses = $DB->get_records('attendance_statuses', array('attendanceid' => $attendance_item->id));
                foreach ($attendance_statuses as $ats) {
                    $statuses_hash[$ats->id] = $ats->acronym;
                }

                $attendance_sessions = $DB->get_records('attendance_sessions', array('attendanceid' => $attendance_item->id, 'groupid' => $existing_group->id));
                foreach ($attendance_sessions as $session) {
                    $attendance_items = $DB->get_records('attendance_log', array('sessionid' => $session->id, 'studentid' => $student->id));

                    foreach ($attendance_items as $atti) {
                        // populate return structure
                        //
                        $resultattendance[] = array(
                            'artenaid' => $enr['artenaid'],
                            'username' => $enr['username'],
                            'coursename' => $enr['coursename'],
                            'groupname' => $enr['groupname'],
                            'attendancename' => $attendance_item->name,
                            'attendancedate' => $atti->timetaken,
                            'attendancenote' => $atti->remarks,
                            'attendance' => $statuses_hash[$atti->statusid],
                            'action' => 'retrieve'
                            );
                    }
                }
                $transaction->allow_commit();

            } catch (invalid_parameter_exception $e) {
                self::log_for_artena('get_attendance', 'invalid parameter EXCEPTION! ' . $e->getMessage());
                $resultattendance[] = array(
                    'username' => $enr['username'],
                    'coursename' => $enr['coursename'],
                    'groupname' => $enr['groupname'],
                    'action'=> 'error',
                    'message' => $e->getMessage()
                    );
                self::rollback_suppress_exception($transaction);
            }
            catch (moodle_exception $e) {
                self::log_for_artena('get_attendance', 'moodle EXCEPTION! ' . $e->getMessage());
                $resultattendance[] = array(
                    'username' => $enr['username'],
                    'coursename' => $enr['coursename'],
                    'groupname' => $enr['groupname'],
                    'action'=> 'error',
                    'message' => $e->getMessage()
                    );
                self::rollback_suppress_exception($transaction);
            }
            catch (Exception $e) {
                self::log_for_artena('get_attendance', 'EXCEPTION! ' . $e->getMessage());
                $resultattendance[] = array(
                    'username' => $enr['username'],
                    'coursename' => $enr['coursename'],
                    'groupname' => $enr['groupname'],
                    'action'=> 'error',
                    'message' => $e->getMessage()
                    );
                self::rollback_suppress_exception($transaction);
            }
        }

        self::log_for_artena('get_attendance','END ' . print_r($resultattendance,1));

        return $resultattendance;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_attendance_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'artenaid' => new external_value(PARAM_INT, 'artena primary key', VALUE_OPTIONAL),
                    'username'  => new external_value(PARAM_RAW, 'student username'),
                    'coursename' => new external_value(PARAM_TEXT, 'course short name'),
                    'groupname' => new external_value(PARAM_RAW, 'group short name'),
                    'attendancename' => new external_value(PARAM_TEXT, 'attendance name', VALUE_OPTIONAL),
                    'attendancedate' => new external_value(PARAM_INT, 'timestamp when the attendance was entered', VALUE_OPTIONAL),
                    'attendancenote' => new external_value(PARAM_TEXT, 'attendance note', VALUE_OPTIONAL),
                    'attendance'  => new external_value(PARAM_FLOAT, 'attendance status', VALUE_OPTIONAL),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_attendance_after_parameters() {
        return new external_function_parameters(
            array(
                'parameters' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            // artena supplies
                            'retrievedate' => new external_value(PARAM_INT, 'timestamp after which to retrieve record inserts/updates'),
                        )
                    ), 'parameters by which to retrieve attendance'
                )
            )
        );
    }

    /**
     * Get attendance set
     * @param $parameters
     * @return array attendance (course, student, date, attendance status)
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @internal param array $attendance
     */
    public static function get_attendance_after($parameters) {
        global $CFG, $DB;

        self::log_for_artena('get_attendance_after','BEGIN',1);

        $plugin = core_plugin_manager::instance()->get_plugin_info('mod_attendance');
        self::log_for_artena('get_attendance_after',"plugin=\n" . print_r($plugin,1));
        if (!$plugin) {
            $resultattendance[] = array(
                'username' => '',
                'coursename' => '',
                'groupname' => '',
                'action' => 'skip',
                'message' => 'attendance plugin not installed',
                );

            return $resultattendance;
        }

        $params = self::validate_parameters(self::get_attendance_after_parameters(), array('parameters'=>$parameters));
        self::log_for_artena('get_attendance_after',"params validated:\n".print_r($params,1));
        $retrievedate = $params['parameters'][0]['retrievedate'];

        try {
            $transaction = $DB->start_delegated_transaction();

            // get records where attendance was recently taken
            self::log_for_artena('get_attendance_after',"retrievedate=".$retrievedate);
            $attendance_log_items = $DB->get_recordset_select('attendance_log', "timetaken >= ".$retrievedate);

            foreach ($attendance_log_items as $atti) {

                self::log_for_artena('get_attendance_after',"atti=\n".print_r($atti,1));
                $attendance_rec = array(
                    'username' => '',
                    'coursename' => '',
                    'groupname' => '',
                    'activityname' => '',
                    'activitydate' => '',
                    'activityduration' => '',
                    'attendancedate' => $atti->timetaken,
                    'attendancenote' => $atti->remarks,
                    'attendance' => '',
                    'action' => 'retrieve',
                    );

                $attendance_user = $DB->get_record('user', array('id' => $atti->studentid));
                self::log_for_artena('get_attendance_after',"attendance_user=\n".print_r($attendance_user,1));

                $attendance_session = $DB->get_record('attendance_sessions', array('id' => $atti->sessionid));
                self::log_for_artena('get_attendance_after',"attendance_session=\n".print_r($attendance_session,1));

                $attendance_item = $DB->get_record('attendance', array('id' => $attendance_session->attendanceid));
                self::log_for_artena('get_attendance_after',"attendance_item=\n".print_r($attendance_item,1));

                $statuses_hash = array();
                $attendance_statuses = $DB->get_records('attendance_statuses', array('attendanceid' => $attendance_item->id));
                foreach ($attendance_statuses as $ats) {
                    $statuses_hash[$ats->id] = $ats->acronym;
                }
                self::log_for_artena('get_attendance_after',"statuses_hash=\n".print_r($statuses_hash,1));

                $attendance_course = $DB->get_record('course', array('id' => $attendance_item->course));
                self::log_for_artena('get_attendance_after',"attendance_course=\n".print_r($attendance_course,1));

                $existing_memberships = groups_get_user_groups($attendance_item->course, $attendance_user->id);
                self::log_for_artena('get_attendance_after',"existing_memberships=\n".print_r($existing_memberships,1));
                $groupname = groups_get_group_name($existing_memberships[0][0]);
                self::log_for_artena('get_attendance_after',"groupname=".$groupname);

                $attendance_rec['coursename'] = $attendance_course->shortname;
                $attendance_rec['groupname'] = ($groupname == '') ? '<no group assigned>' : $groupname;
                $attendance_rec['username'] = $attendance_user->username;
                $attendance_rec['activityname'] = $attendance_session->description;
                $attendance_rec['activitydate'] = $attendance_session->sessdate;
                $attendance_rec['activityduration'] = $attendance_session->duration;
                $attendance_rec['attendance'] = $statuses_hash[$atti->statusid];

                $resultattendance[] = $attendance_rec;
            }

            $transaction->allow_commit();

        } catch (invalid_parameter_exception $e) {
            self::log_for_artena('get_attendance_after', 'invalid parameter EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }
        catch (moodle_exception $e) {
            self::log_for_artena('get_attendance_after', 'moodle EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }
        catch (Exception $e) {
            self::log_for_artena('get_attendance_after', 'EXCEPTION! ' . $e->getMessage());
            self::rollback_suppress_exception($transaction);
        }

        self::log_for_artena('get_attendance_after',"result=\n". print_r($resultattendance,1));
        self::log_for_artena('get_attendance_after','END');

        return $resultattendance;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_attendance_after_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'username'  => new external_value(PARAM_RAW, 'student username'),
                    'coursename' => new external_value(PARAM_TEXT, 'course shortname'),
                    'groupname' => new external_value(PARAM_RAW, 'group shortname'),
                    'activityname' => new external_value(PARAM_RAW, 'activity name', VALUE_OPTIONAL),
                    'activitydate' => new external_value(PARAM_INT, 'activity datetime', VALUE_OPTIONAL),
                    'activityduration' => new external_value(PARAM_INT, 'activity duration', VALUE_OPTIONAL),
                    'attendancedate' => new external_value(PARAM_INT, 'timestamp when the attendance was entered', VALUE_OPTIONAL),
                    'attendancenote' => new external_value(PARAM_TEXT, 'attendance note', VALUE_OPTIONAL),
                    'attendance'  => new external_value(PARAM_TEXT, 'attendance status', VALUE_OPTIONAL),
                    'action' => new external_value(PARAM_TEXT, 'action performed'),
                    'message' => new external_value(PARAM_RAW, 'result message', VALUE_OPTIONAL),
                )
            )
        );
    }

    public static function rollback_suppress_exception(moodle_transaction $transaction) {
        global $DB;
        $e = new Exception();
        try {
            $DB->rollback_delegated_transaction($transaction, $e);
        }
        catch (Exception $e) {}
    }

    public static function log_for_artena($method,$data,$new=0) {
        global $CFG;

        $fn = $CFG->dataroot . '/' . $method . '.log';
        if (1 == $new) {
            $fp = @fopen($fn,'w+');
        } else {
            $fp = @fopen($fn,'a+');
        }

        if ($fp) {
            @fwrite($fp,$data."\r\n");
            @fflush($fp);
            @fclose($fp);
        }
    }
}
