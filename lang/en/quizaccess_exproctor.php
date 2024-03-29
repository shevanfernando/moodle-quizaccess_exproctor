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
 * @package     quizaccess_exproctor
 * @category    string
 * @copyright   2022 Shevan Thiranja Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ExProctor';
$string['proctor:name'] = 'Exam Proctor';
$string['proctor:short_name'] = 'proctor';
$string['proctor:description'] =
    'Proctor is a role of the ExProctor plugin. This role can be used to monitor students during exams.';
$string['exproctor:send_evidence'] = 'Send evidence files';
$string['exproctor:get_evidence'] = 'Read evidence files';
$string['exproctor:view_report'] = 'View proctoring report';
$string['exproctor:delete_evidence'] = 'Delete evidence files';
$string['storage_method'] = 'Data storage method';
$string['storage_method_description'] =
    'Select how you want to store the proctoring evidence reports.';
$string['aws_region'] = 'Amazon Web Service region';
$string['aws_region_description'] =
    'Select the AWS server region where S3 or Rekognition or both services are available.';
$string['aws_access_key'] = 'Access key';
$string['aws_access_key_description'] = 'Access key id for AWS.';
$string['aws_secret_key'] = 'Secret key';
$string['aws_secret_key_description'] = 'Secret key for AWS.';
$string['screenshotdelay'] = 'The delay between screenshots in seconds';
$string['screenshotwidth'] =
    'The width of the screenshot image in px (pixels)';
$string['proctoringmethod'] = 'Exam proctoring method';
$string['proctoringmethod_help'] =
    'How do you want to proctor this quiz? Please click the drop down to find Proctoring options.';
$string['proctoring_method_one'] = 'Take screenshot randomly';
$string['proctoring_method_two'] = 'Take screenshot using AI';
$string['proctoring_method_three'] = 'Live proctoring';
$string['not_required'] = 'not required';
$string['required'] = 'required';
$string['openwebcam'] = 'Allow your webcam to continue';
$string['openscreen'] = 'Chose what to share';
$string['webcamproctoringstatement'] =
    'This exam requires webcam validation process. <br />(Please allow your web browser to access your camera).';
$string['screenproctoringstatement'] =
    'This exam requires screen validation process. <br />(Please select screen to share).';
$string['camhtml'] =
    '<div class="exproctor_camera"> <video id="exproctor_video_wb" autoplay muted>Video stream not available.</video></div> <canvas id="exproctor_canvas_wb" style="display:none;"> </canvas> <img style="display:none;" id="exproctor_photo_wb" alt="The webcam capture will appear in this box."/>';
$string['screenhtml'] =
    '<div class="exproctor_screen"> <video id="exproctor_video_sc" autoplay muted>Screen stream not available.</video></div> <canvas id="exproctor_canvas_sc" style="display:none;"> </canvas> <img style="display:none;" id="exproctor_photo_sc" alt="The screen capture will appear in this box."/>';
$string['proctoringlabel'] = 'I agree with the validation process.';
$string['you_must_agree_for_webcam'] =
    'You must agree to webcam validate before continue your attempt.';
$string['you_must_agree_for_screen'] =
    'You must agree to screen validate before continue your attempt.';
$string['webcamproctoringrequired'] = 'Webcam proctoring';
$string['webcamproctoringrequired_help'] =
    'Do you want to use webcam proctoring for this quiz?';
$string['screenproctoringrequired'] = 'Screen proctoring';
$string['screenproctoringrequired_help'] =
    'Do you want to use screen proctoring for this quiz?';
$string['proctoringheader'] =
    '<strong>To continue with this quiz attempt, please grant your web browser permission to access your {$a}.</strong>';
$string['view_report'] = 'View evidences';
$string['no_permission_report'] =
    'You don\'t have permission to review validation reports.';
$string['no_permission_to_delete_report'] =
    'You don\'t have permission to delete the validation reports.';
$string['proctoring_reports'] = 'Identity validation report for: ';
$string['proctoring_reports_desc'] =
    'In this report you will find all the images of the students which are taken during the exam. Now you can validate their exam.';
$string['dateverified'] = 'Date and time';
$string['evidencetype'] = 'Evidence type';
$string['actions'] = 'Actions';
$string['picturesreport'] = 'View proctoring report';
$string['pictures_webcam_used_report'] =
    'These are the webcam evidence captured during the quiz.';
$string['pictures_screen_used_report'] =
    'These are the screen shots captured during the quiz.';
$string['no_evidence_report'] =
    'There is <strong>no attempt</strong> in this quiz. Therefore, there is no evidence data for this.';
$string['std_name'] = 'Student name';
$string['imagecolumn'] = 'Captured images';
$string['screen_shot_delay_method_one'] = '5 Seconds';
$string['screen_shot_delay_method_two'] = '10 Seconds';
$string['screen_shot_delay_method_three'] = '15 Seconds';
$string['screen_shot_delay_method_four'] = '20 Seconds';
$string['screen_shot_delay_method_five'] = '25 Seconds';
$string['screen_shot_delay_method_six'] = '30 Seconds';
$string['frozen_message'] =
    'This quiz already has attempts. Therefore ExProctor plugin settings can no longer be updated.';
$string["invalid_data"] =
    '{$a->field} is invalid. {$a->field} must be {$a->data_type}.';
$string["empty_data"] = '{$a->field} is empty.';
$string["single_image_delete_confirm_msg"] = 'Are you sure you want to delete this evidences.';
$string["all_image_delete_confirm_msg"] = 'Are you sure you want to delete all these evidences.';
$string["s3_bucket_delete"] =
    '<a class="btn btn-primary" href="' . new moodle_url('/mod/quiz/accessrule/exproctor/s3bucketdelete.php', []) .
    '">Delete S3 buckets</a>';
$string['no_permission'] = 'You do not have proper permission to view this page';
$string['s3_bucket_delete_warning'] =
    'Deleting S3 bucket(s) affects the evidence record. If you delete s3 bucket(s) the evidence belonging to each bucket is deleted.';
$string['settings_freeze'] =
    '<div class="box generalbox p-y-1 m-b-1 adminerror alert-info" style="padding: 0 10px; border-radius: 10px">Cannot change storage method!</div>';
