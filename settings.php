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
 * Plugin administration pages are defined here.
 *
 * @package     quizaccess_exproctor
 * @category    admin
 * @copyright   2022 Shevan Thiranja Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $PAGE->requires->js_call_amd('quizaccess_exproctor/dynamic_settings', 'init');

    if ($ADMIN->fulltree) {
        // Storage Choices
        $CHOICES = array("Local" => "Local", "AWS(S3)" => "AWS(S3)");

        $settings->add(new admin_setting_configselect("quizaccess_exproctor/storagemethod", get_string("settings:storage_method", "quizaccess_exproctor"), get_string("settings:storage_method_description", "quizaccess_exproctor"), $CHOICES["Local"], $CHOICES));

//        $settings->add(new admin_setting_configtext("quizaccess_exproctor/localpath", get_string("settings:local_storage_path", "quizaccess_exproctor"), get_string("settings:local_storage_path_description", "quizaccess_exproctor"), $value, PARAM_TEXT));

        $settings->add(new admin_setting_configtext("quizaccess_exproctor/awsregion", get_string("settings:aws_region", "quizaccess_exproctor"), get_string("settings:aws_region_description", "quizaccess_exproctor"), $value, PARAM_TEXT));

        $settings->add(new admin_setting_configtext("quizaccess_exproctor/awsaccessid", get_string("settings:aws_access_id", "quizaccess_exproctor"), get_string("settings:aws_access_id_description", "quizaccess_exproctor"), $value, PARAM_TEXT));

        $settings->add(new admin_setting_configtext("quizaccess_exproctor/awsaccesskey", get_string("settings:aws_access_key", "quizaccess_exproctor"), get_string("settings:aws_access_key_description", "quizaccess_exproctor"), $value, PARAM_TEXT));
    }
}
