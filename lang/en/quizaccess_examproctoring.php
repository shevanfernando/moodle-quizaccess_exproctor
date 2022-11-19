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
 * Plugin strings are defined here.
 *
 * @package     quizaccess_examproctoring
 * @category    string
 * @copyright   2022 Shevan Thiranja Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Exam Proctoring';
$string['examproctoring:send_evidence'] = 'Send evidence files';
$string['examproctoring:get_evidence'] = 'Read evidence files';
$string['examproctoring:view_report'] = 'View proctoring report';
$string['examproctoring:delete_evidence'] = 'Delete evidence files';
$string['settings:storage_method'] = 'Data storage method';
$string['settings:storage_method_description'] = 'Proctored data storage method. (Values: Local/ AWS(S3))';
$string['settings:local_storage_path'] = 'Local storage folder path';
$string['settings:local_storage_path_description'] = 'File path for store proctored data in locally.';
$string['settings:aws_region'] = 'AWS S3 region';
$string['settings:aws_region_description'] = 'AWS region for S3 bucket.';
$string['settings:aws_access_id'] = 'AWS access key id';
$string['settings:aws_access_id_description'] = 'AWS access key id for S3 bucket.';
$string['settings:aws_access_key'] = 'AWS secret access key';
$string['settings:aws_access_key_description'] = 'AWS secret access key for S3 bucket.';

