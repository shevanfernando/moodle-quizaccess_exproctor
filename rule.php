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
 * Implementation of the quizaccess_exproctor plugin.
 *
 * @package    quizaccess_exproctor
 * @copyright  2022 Shevan Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');

require_once($CFG->dirroot . '/mod/quiz/accessrule/exproctor/classes/external.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/exproctor/classes/aws_s3.php');

use core\output\notification;
use quizaccess_exproctor\aws_s3;
use quizaccess_exproctor\link_generator;

class quizaccess_exproctor extends quiz_access_rule_base {
    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @param quiz $quizobj
     * @param int $timenow
     * @param bool $canignoretimelimits
     *
     * @return quiz_access_rule_base|quizaccess_exproctor|null
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (!empty($quizobj->get_quiz()->proctoringmethod)) {
            if (empty($quizobj->get_quiz()->webcamproctoringrequired) && empty($quizobj->get_quiz()->screenproctoringrequired)) {
                return null;
            }

            return new self($quizobj, $timenow);
        }

        return null;
    }

    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from mod_quiz_mod_form::definition(), while the
     * security section is being built.
     *
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     *
     * @throws coding_exception
     */
    public static function add_settings_form_fields(
        mod_quiz_mod_form $quizform,
        MoodleQuickForm $mform
    ) {
        global $OUTPUT;

        // This only for debug the code.
        // TODO: Remove this before push the code into git hub.
        get_string_manager()->reset_caches();

        $proctormethods = array(
            0 => get_string('not_required', 'quizaccess_exproctor'),
            1 => get_string(
                'proctoring_method_one',
                'quizaccess_exproctor'
            ), 2 => get_string(
                'proctoring_method_two',
                'quizaccess_exproctor'
            )
        );

        $s3 = new aws_s3();

        if ($s3->get_data()['storagemethod'] !== 'AWS(S3)') {
            unset($proctormethods[2]); // Remove Ai proctor method.
        }

        $mform->addElement(
            'select',
            'proctoringmethod',
            get_string('proctoringmethod', 'quizaccess_exproctor'),
            $proctormethods
        );
        $mform->addHelpButton(
            'proctoringmethod',
            'proctoringmethod',
            'quizaccess_exproctor'
        );

        $mform->addElement(
            'select',
            'webcamproctoringrequired',
            get_string('webcamproctoringrequired', 'quizaccess_exproctor'),
            array(
                0 => get_string('not_required', 'quizaccess_exproctor'),
                1 => get_string('required', 'quizaccess_exproctor'),
            )
        );
        $mform->addHelpButton(
            'webcamproctoringrequired',
            'webcamproctoringrequired',
            'quizaccess_exproctor'
        );

        $mform->addElement(
            'select',
            'screenproctoringrequired',
            get_string('screenproctoringrequired', 'quizaccess_exproctor'),
            array(
                0 => get_string('not_required', 'quizaccess_exproctor'),
                1 => get_string('required', 'quizaccess_exproctor'),
            )
        );
        $mform->addHelpButton(
            'screenproctoringrequired',
            'screenproctoringrequired',
            'quizaccess_exproctor'
        );

        $mform->addElement(
            'select',
            'screenshotdelay',
            get_string('screenshotdelay', 'quizaccess_exproctor'),
            array(
                5 => get_string(
                    'screen_shot_delay_method_one',
                    'quizaccess_exproctor'
                ), 10 => get_string(
                'screen_shot_delay_method_two',
                'quizaccess_exproctor'
            ), 15 => get_string(
                'screen_shot_delay_method_three',
                'quizaccess_exproctor'
            ), 20 => get_string(
                'screen_shot_delay_method_four',
                'quizaccess_exproctor'
            ), 25 => get_string(
                'screen_shot_delay_method_five',
                'quizaccess_exproctor'
            ), 30 => get_string(
                'screen_shot_delay_method_six',
                'quizaccess_exproctor'
            ),
            )
        );

        $mform->addElement(
            'text',
            'screenshotwidth',
            get_string(
                'screenshotwidth',
                'quizaccess_exproctor'
            )
        );
        $mform->setType("screenshotwidth", PARAM_INT);
        $mform->setDefault('screenshotwidth', 320);

        // Display notification about locked settings.
        if (self::is_exproctor_settings_locked($quizform->get_instance())) {
            $notify = new notification(
                get_string(
                    'frozen_message',
                    'quizaccess_exproctor'
                ),
                notification::NOTIFY_WARNING
            );

            $notifyelement = $mform->createElement('html', $OUTPUT->render($notify));
            $mform->insertElementBefore($notifyelement, 'quizpassword');

            // Freeze elements.
            $mform->freeze('proctoringmethod');
            $mform->freeze('webcamproctoringrequired');
            $mform->freeze('screenproctoringrequired');
            $mform->freeze('screenshotdelay');
            $mform->freeze('screenshotwidth');
        }
    }

    /**
     * Check if settings is locked.
     *
     * @param string $quizid Quiz Id
     *
     * @return bool
     */
    private static function is_exproctor_settings_locked(string $quizid): bool {
        global $DB;

        if (empty($quizid)) {
            return false;
        }

        return $DB->record_exists('quiz_attempts', array('quiz' => $quizid));
    }

    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from quiz_after_add_or_update() in lib.php.
     *
     * @param object $quiz the data from the quiz form, including $quiz->id
     *                        which is the id of the quiz being saved.
     *
     * @throws dml_exception
     */
    public static function save_settings($quiz) {
        global $DB;

        $iswebcamproctoringrequired = (int) $quiz->webcamproctoringrequired;
        $isscreenproctoringrequired = (int) $quiz->screenproctoringrequired;
        $proctoringmethod = (int) $quiz->proctoringmethod;
        $screenshotdelay = (int) $quiz->screenshotdelay;
        $screenshotwidth = (int) $quiz->screenshotwidth;

        if (empty($iswebcamproctoringrequired) && empty($isscreenproctoringrequired)) {
            $DB->delete_records(
                'quizaccess_exproctor',
                array('quizid' => (int) $quiz->id)
            );
        } else {
            if (!$DB->record_exists(
                'quizaccess_exproctor',
                array('quizid' => (int) $quiz->id)
            )) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->webcamproctoringrequired =
                    $iswebcamproctoringrequired;
                $record->screenproctoringrequired =
                    $isscreenproctoringrequired;
                $record->proctoringmethod = $proctoringmethod;
                $record->screenshotdelay = $screenshotdelay;
                $record->screenshotwidth = $screenshotwidth;
                $DB->insert_record('quizaccess_exproctor', $record);
            } else {
                $record = $DB->get_record(
                    'quizaccess_exproctor',
                    array('quizid' => $quiz->id)
                );
                $record->webcamproctoringrequired =
                    $iswebcamproctoringrequired;
                $record->screenproctoringrequired =
                    $isscreenproctoringrequired;
                $record->proctoringmethod = $proctoringmethod;
                $record->screenshotdelay = $screenshotdelay;
                $record->screenshotwidth = $screenshotwidth;
                $DB->update_record('quizaccess_exproctor', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from quiz_delete_instance() in lib.php.
     *
     * @param object $quiz the data from the database, including $quiz->id
     *                        which is the id of the quiz being deleted.
     *
     * @throws dml_exception
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records(
            'quizaccess_exproctor',
            array('quizid' => $quiz->id)
        );
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probably to read the code of quiz_access_manager::load_settings().
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the get_extra_settings() method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *                       can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     *
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be aliased
     *        if necessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        be used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid): array {
        return array(
            'e.webcamproctoringrequired,' . 'e.screenproctoringrequired,' . 'e.proctoringmethod,' . 'e.screenshotdelay,' .
            'e.screenshotwidth',
            'LEFT JOIN {quizaccess_exproctor} e ON e.quizid = quiz.id',
            array()
        );
    }

    /**
     * Check is preflight check is required.
     *
     * @param mixed $attemptid
     *
     * @return bool
     */
    public function is_preflight_check_required($attemptid): bool {
        return empty($attemptid);
    }

    /**
     * add_preflight_check_form_fields
     *
     * @param mod_quiz_preflight_check_form $quizform
     * @param MoodleQuickForm $mform
     * @param mixed $attemptid
     *
     * @return void
     * @throws coding_exception
     */
    public function add_preflight_check_form_fields(
        mod_quiz_preflight_check_form $quizform,
        MoodleQuickForm $mform,
        $attemptid
    ) {
        global $PAGE;
        $data = self::get_quiz_details();

        $data['is_quiz_started'] = false;

        // This only for debug the code.
        // TODO: Remove this before push the code into git hub.
        get_string_manager()->reset_caches();

        if ($data["webcamproctoringrequired"] && $data["screenproctoringrequired"]) {
            $this->get_screen_proctoring_form_fields($mform, $data, $PAGE);
            $this->get_webcam_proctoring_form_fields($mform, $data, $PAGE);
        } else if ($data["screenproctoringrequired"]) {
            $this->get_screen_proctoring_form_fields($mform, $data, $PAGE);
        } else if ($data["webcamproctoringrequired"]) {
            $this->get_webcam_proctoring_form_fields($mform, $data, $PAGE);
        }
    }

    /**
     * Get followings,
     * - screenproctoringrequired
     * - webcamproctoringrequired
     *
     * @return array
     */
    private function get_quiz_details(): array {
        global $USER;

        $response = [];
        $response['cmid'] = $this->quiz->cmid;
        $response['courseid'] = $this->quiz->course;
        $response['quizid'] = $this->quiz->id;
        $response['screenproctoringrequired'] =
            (bool) $this->quiz->screenproctoringrequired;
        $response['webcamproctoringrequired'] =
            (bool) $this->quiz->webcamproctoringrequired;
        $response['proctoringmethod'] = $this->quiz->proctoringmethod;
        $response['userid'] = $USER->id;

        $frequency = ((int) $this->quiz->screenshotdelay) * 1000;
        if ($frequency == 0) {
            $frequency = 30 * 1000;
        }

        $response["screenshotdelay"] = $frequency;

        $imagewidth = (int) $this->quiz->screenshotwidth;

        if ($imagewidth == 0) {
            $imagewidth = 320;
        }

        $response['image_width'] = $imagewidth;

        return $response;
    }

    /**
     * Screen settings for start attempt page
     *
     * @param $mform
     * @param $data
     * @param $PAGE
     *
     * @return void
     * @throws coding_exception
     */
    private function get_screen_proctoring_form_fields($mform, $data, $PAGE) {
        $mform->addElement(
            'header',
            'screenproctoringheader',
            get_string('openscreen', 'quizaccess_exproctor')
        );
        $mform->addElement(
            'static',
            'screenproctoringmessage',
            '',
            get_string(
                'screenproctoringstatement',
                'quizaccess_exproctor'
            )
        );
        $mform->addElement(
            'static',
            'screenmessage',
            '',
            get_string('screenhtml', 'quizaccess_exproctor')
        );

        $PAGE->requires->js_call_amd(
            'quizaccess_exproctor/screen_proctoring',
            'init',
            array($data)
        );
    }

    /**
     * Webcam settings for start attempt page
     *
     * @param $mform
     * @param $data
     * @param $PAGE
     *
     * @return void
     * @throws coding_exception
     */
    private function get_webcam_proctoring_form_fields($mform, $data, $PAGE) {
        $mform->addElement(
            'header',
            'webcamproctoringheader',
            get_string('openwebcam', 'quizaccess_exproctor')
        );
        $mform->addElement(
            'static',
            'webcamproctoringmessage',
            '',
            get_string(
                'webcamproctoringstatement',
                'quizaccess_exproctor'
            )
        );
        $mform->addElement(
            'static',
            'webcammessage',
            '',
            get_string('camhtml', 'quizaccess_exproctor')
        );
        $mform->addElement(
            'checkbox',
            'webproctoring',
            '',
            get_string(
                'proctoringlabel',
                'quizaccess_exproctor'
            )
        );

        $PAGE->requires->js_call_amd(
            'quizaccess_exproctor/webcam_proctoring',
            'init',
            array($data)
        );
    }

    /**
     * @return boolean whether this rule requires that the attemp (and review)
     *      pages must be displayed in a pop-up window.
     */
    public function attempt_must_be_in_popup(): bool {
        return true;
    }

    /**
     * @return array any options that are required for showing the attempt page
     *      in a popup window.
     */
    public function get_popup_options(): array {
        return array(
            'left' => 0, 'top' => 0,
            'fullscreen' => true,
            'scrollbars' => true,
            'resizeable' => false,
            'directories' => false,
            'toolbar' => false,
            'titlebar' => false,
            'location' => false,
            'status' => false,
            'menubar' => false,
        );
    }

    /**
     * Validate the preflight check
     *
     * @param mixed $data
     * @param mixed $files
     * @param mixed $errors
     * @param mixed $attemptid
     *
     * @return mixed $errors
     * @throws coding_exception
     */
    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        $values = self::get_quiz_details();

        if (empty($data['webproctoring']) && $values['webcamproctoringrequired']) {
            $errors['webproctoring'] =
                get_string('you_must_agree_for_webcam', 'quizaccess_exproctor');
        }

        return $errors;
    }

    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @return array a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function description(): array {
        $data = self::get_quiz_details();

        $proctoringmethod = "";

        if ($data["webcamproctoringrequired"] && $data["screenproctoringrequired"]) {
            $proctoringmethod = "webcam & screen";
        } else if ($data["screenproctoringrequired"]) {
            $proctoringmethod = "screen";
        } else if ($data["webcamproctoringrequired"]) {
            $proctoringmethod = "webcam";
        }

        $messages = [get_string('proctoringheader', 'quizaccess_exproctor', $proctoringmethod)];

        $messages[] = $this->get_download_config_button();

        return $messages;
    }

    /**
     * Get a button to view the Proctoring report.
     *
     * @return string A link to view report
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_download_config_button(): string {
        global $OUTPUT, $USER;

        $context = context_module::instance($this->quiz->cmid, MUST_EXIST);
        if (has_capability('quizaccess/exproctor:view_report', $context, $USER->id)) {
            // Create a link for report.
            $httplink =
                link_generator::get_link(
                    $this->quiz->course,
                    $this->quiz->id,
                    $this->quiz->cmid,
                    false,
                    is_https()
                );

            return $OUTPUT->single_button(
                $httplink,
                get_string('picturesreport', 'quizaccess_exproctor'),
                'get'
            );
        }
        return '';
    }

    /**
     * Sets up the attempt (review or summary) page with any special extra
     * properties required by this rule.
     *
     * @param moodle_page $page the page object to initialise.
     *
     * @throws coding_exception
     * @throws dml_exception|invalid_parameter_exception
     */
    public function setup_attempt_page($page) {
        // TODO: Check this function.
        $data = self::get_quiz_details();

        $cmid = optional_param('cmid', '', PARAM_INT);
        $attempt = optional_param('attempt', '', PARAM_INT);
        $bucketname = null;

        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_popup_notification_allowed(
            false
        ); // Prevent message notifications.
        $page->set_heading($page->title);

        $s3 = new aws_s3();

        if ($s3->get_data()['storagemethod'] == 'AWS(S3)') {
            $bucketname = $s3->create_bucket($attempt);
        }

        if ($cmid) {
            $page->requires->js_call_amd('quizaccess_exproctor/store_current_attempt', 'store',
                array($attempt, $bucketname)
            );
        }

        $context = context_course::instance($data['courseid']);
        $roles = get_user_roles($context, $data['userid'], true);
        $role = key($roles);
        $rolename = $roles[$role]->shortname;

        if ($page->pagetype === 'mod-quiz-review' && $rolename !== 'student') {
            $external = new quizaccess_exproctor_external();

            if ($data["screenproctoringrequired"] || $data["webcamproctoringrequired"]) {
                $external::set_quiz_status(
                    $data['courseid'],
                    $data['userid'],
                    $data['quizid']
                );
            }
        }
    }

    /**
     * This is called when the current attempt at the quiz is finished. This is
     * used, set quiz status of the webcam log and screen log tables.
     */
    public function current_attempt_finished() {
        try {
            global $USER, $PAGE;
            $data = self::get_quiz_details();

            $external = new quizaccess_exproctor_external();

            $PAGE->requires->js_call_amd(
                'quizaccess_exproctor/store_current_attempt',
                'remove',
                array()
            );

            if ($data["screenproctoringrequired"] || $data["webcamproctoringrequired"]) {
                $external::set_quiz_status(
                    $data['courseid'],
                    $USER->id,
                    $data['quizid']
                );
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
