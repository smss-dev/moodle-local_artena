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
 * Local Redirect
 *
 * This local plugin that adds a 'friendly url' version of Artena Integration plugin
 * to Moodle
 *
 * @package local
 * @subpackage artena
 * @copyright 2016 Adapt IT Australasia
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG, $USER, $DB;

    $moderator = get_admin();
    $site = get_site();

    $settings = new admin_settingpage('local_artena', get_string('pluginname', 'local_artena'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_artena/enabled';
    $title = get_string('enabled', 'local_artena');
    $description = get_string('enabled_desc', 'local_artena');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $settings->add($setting);

}

