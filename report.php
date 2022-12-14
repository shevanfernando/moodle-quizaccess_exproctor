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
 * Report for the quizaccess_exproctor plugin.
 *
 * @package    ${PLUGINNAME}
 * @copyright  2022 Shevan Thiranja Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $DB, $PAGE, $OUTPUT, $USER;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/tablelib.php');

// Get vars.
$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);
$studentid = optional_param('studentid', '', PARAM_INT);
$reportid = optional_param('reportid', '', PARAM_INT);
$log_action = optional_param('log_action', '', PARAM_TEXT);

$context = context_module::instance($cmid, MUST_EXIST);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');

require_login($course, true, $cm);


$COURSE = $DB->get_record('course', array('id' => $courseid));
$quiz = $DB->get_record('quiz', array('id' => $cm->instance));

$proctoring = $DB->get_record('quizaccess_exproctor', array('quizid' => $quiz->id));

$webcamproctoringrequired = (bool)$proctoring->webcamproctoringrequired;
$screenproctoringrequired = (bool)$proctoring->screenproctoringrequired;

$params = array(
    'courseid' => $courseid,
    'userid' => $studentid,
    'cmid' => $cmid
);

if ($studentid) {
    $params['studentid'] = $studentid;
}
if ($reportid) {
    $params['reportid'] = $reportid;
}

$url = new moodle_url(
    '/mod/quiz/accessrule/exproctor/report.php',
    $params
);

//$form = new quizaccess_exproctor_delete_form($url);

get_string_manager()->reset_caches();

$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_title($COURSE->shortname . ': ' . get_string('pluginname', 'quizaccess_exproctor'));
$PAGE->set_heading($COURSE->fullname . ': ' . get_string('pluginname', 'quizaccess_exproctor'));

$PAGE->navbar->add(get_string('quizaccess_exproctor', 'quizaccess_exproctor'), $url);

echo $OUTPUT->header();

echo '<div id="main">
<h2>' . get_string('proctoring_reports', 'quizaccess_exproctor') . '' . $quiz->name . '</h2>
<div class="box generalbox m-b-1 adminerror alert alert-info p-y-1">'
    . get_string('proctoring_reports_desc', 'quizaccess_exproctor') . '</div>
';

// Delete webcam shots
if (has_capability('quizaccess/exproctor:delete_evidence', $context, $USER->id)
    && $studentid != null
    && $quizid != null
    && $courseid != null
    && $reportid != null
    && !empty($log_action)
) {
    // Delete images
    // Remove logs from quizaccess_exproctor_wb_logs
    $DB->delete_records('quizaccess_exproctor_wb_logs', array('courseid' => $courseid, 'quizid' => $quizid, 'userid' => $studentid));

    $filesql = 'SELECT * FROM {files} WHERE userid IN (' . $studentid . ') AND contextid IN (' . $context->id . ') AND component = \'quizaccess_exproctor\' AND filearea = \'webcam_images\'';
    $usersfile = $DB->get_records_sql($filesql);

    $fs = get_file_storage();
    foreach ($usersfile as $file):
        // Delete the actual file
        $fs->delete_area_files($context->id, 'quizaccess_exproctor', 'picture', $file->id);
    endforeach;
    $url2 = new moodle_url(
        '/mod/quiz/accessrule/exproctor/report.php',
        array(
            'courseid' => $courseid,
            'quizid' => $quizid,
            'cmid' => $cmid
        )
    );
    redirect($url2, 'Images deleted!', -11);
}

// Delete screen shots
if (has_capability('quizaccess/exproctor:delete_evidence', $context, $USER->id)
    && $studentid != null
    && $quizid != null
    && $courseid != null
    && $reportid != null
    && !empty($log_action)
) {
    // Delete images
    // Remove logs from quizaccess_exproctor_sc_logs
    $DB->delete_records('quizaccess_exproctor_sc_logs', array('courseid' => $courseid, 'quizid' => $quizid, 'userid' => $studentid));

    $filesql = 'SELECT * FROM {files} WHERE userid IN (' . $studentid . ') AND contextid IN (' . $context->id . ') AND component = \'quizaccess_exproctor\' AND filearea = \'screen_shots\'';
    $usersfile = $DB->get_records_sql($filesql);

    $fs = get_file_storage();
    foreach ($usersfile as $file):
        // Delete the actual file
        $fs->delete_area_files($context->id, 'quizaccess_exproctor', 'picture', $file->id);
    endforeach;

    $url2 = new moodle_url(
        '/mod/quiz/accessrule/exproctor/report.php',
        array(
            'courseid' => $courseid,
            'quizid' => $quizid,
            'cmid' => $cmid
        )
    );

    redirect($url2, 'Images deleted!', -11);
}

# Delete single webcam picture
if (has_capability('quizaccess/exproctor:delete_evidence', $context, $USER->id)
    && $log_action == "deletesinglewebcampic"
) {
    $logsql = "SELECT * FROM {quizaccess_exproctor_wb_logs} WHERE id= $reportid";
    $records = $DB->get_records_sql($logsql);

    if (count($records) > 0) {
        $file_id = 0;
        $tempcontextid = 0;

        foreach ($records as $record) {
            $file_id = $record->fileid;
        }

        $filesql = "SELECT * FROM {files} WHERE id=$file_id";
        $usersfile = $DB->get_records_sql($filesql);

        foreach ($usersfile as $tempfile) {
            $tempcontextid = $tempfile->contextid;
        }

        // Delete Image
        // Delete the file record
        $DB->delete_records('quizaccess_exproctor_wb_logs', array('id' => $reportid));

        // Delete the actual file
        $fs = get_file_storage();
        $fs->delete_area_files($tempcontextid, 'quizaccess_exproctor', 'webcam_images', $file_id);
    }
}

# Delete single screen shot
if (has_capability('quizaccess/exproctor:delete_evidence', $context, $USER->id)
    && $log_action == "deletesinglescreenshot"
) {
    $logsql = "SELECT * FROM {quizaccess_exproctor_sc_logs} WHERE id= $reportid";
    $records = $DB->get_records_sql($logsql);

    if (count($records) > 0) {
        $file_id = 0;
        $tempcontextid = 0;

        foreach ($records as $record) {
            $file_id = $record->fileid;
        }

        $filesql = "SELECT * FROM {files} WHERE id=$file_id";
        $usersfile = $DB->get_records_sql($filesql);

        foreach ($usersfile as $tempfile) {
            $tempcontextid = $tempfile->contextid;
        }

        // Delete Image
        /// Delete the file record
        $DB->delete_records('quizaccess_exproctor_sc_logs', array('id' => $reportid));

        // Delete the actual file
        $fs = get_file_storage();
        $fs->delete_area_files($tempcontextid, 'quizaccess_exproctor', 'screen_shots', $file_id);
    }
}

# View webcam shot
if (has_capability('quizaccess/exproctor:view_report', $context, $USER->id) && $cmid != null && $courseid != null) {
    if ($webcamproctoringrequired) {
        // Check if report if for some user.
        if ($studentid != null && $quizid != null && $courseid != null && $reportid != null) {
            // Report for this user.
            $sql = "SELECT e.id as reportid, e.userid as studentid, e.webcamshot as webcamshot, e.attemptid as attemptid,
         e.timemodified as timemodified, u.firstname as firstname, u.lastname as lastname, u.email as email
         from  {quizaccess_exproctor_wb_logs} e INNER JOIN {user} u  ON u.id = e.userid
         WHERE e.courseid = '$courseid' AND e.quizid = '$quizid' AND u.id = '$studentid' AND e.id = '$reportid'";
        }

        if ($studentid == null && $quizid != null && $courseid != null) {
            // Report for all users.
            $sql = "SELECT  DISTINCT e.userid as studentid, u.firstname as firstname, u.lastname as lastname,
                u.email as email, max(e.webcamshot) as webcamshot,max(e.id) as reportid, max(e.attemptid) as attemptid,
                max(e.timemodified) as timemodified
                from  {quizaccess_exproctor_wb_logs} e INNER JOIN {user} u ON u.id = e.userid
                WHERE e.courseid = '$courseid' AND e.quizid = '$quizid'
                group by e.userid, u.firstname, u.lastname, u.email";
        }

        // Print report.
        $table = new flexible_table('exproctor-report-' . $COURSE->id . '-' . $cmid);

        $table->define_columns(array('fullname', 'email', 'dateverified', 'actions'));
        $table->define_headers(
            array(
                get_string('user'),
                get_string('email'),
                get_string('dateverified', 'quizaccess_exproctor'),
                get_string('actions', 'quizaccess_exproctor')
            )
        );

        $table->define_baseurl($url);

        $table->set_attribute('cellpadding', '5');
        $table->set_attribute('class', 'generaltable generalbox reporttable');
        $table->setup();

        // Prepare data.
        $sqlexecuted = $DB->get_recordset_sql($sql);

        foreach ($sqlexecuted as $info) {
            $data = array();
            $data[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $info->studentid .
                '&course=' . $courseid . '" target="_blank">' . $info->firstname . ' ' . $info->lastname . '</a>';

            $data[] = $info->email;

            $data[] = date("Y/M/d H:m:s", $info->timemodified);

            if ($webcamproctoringrequired) {
                $data[] = '<a href="?courseid=' . $courseid .
                    '&quizid=' . $quizid . '&cmid=' . $cmid . '&studentid=' . $info->studentid . '&reportid=' . $info->reportid . '">' .
                    get_string('webcam_report', 'quizaccess_exproctor') . '</a>';
            }

            $table->add_data($data);
        }

        if (!$screenproctoringrequired) {
            $table->finish_html();
        }

        // Print image results for webcam.
        if ($studentid != null && $quizid != null && $courseid != null && $reportid != null) {

            $data = array();
            $sql = "SELECT e.id as reportid, e.userid as studentid, e.webcamshot as webcamshot, e.attemptid as attemptid,
        e.timemodified as timemodified, u.firstname as firstname, u.lastname as lastname, u.email as email
        from {quizaccess_exproctor_wb_logs} e INNER JOIN {user} u  ON u.id = e.userid
        WHERE e.courseid = '$courseid' AND e.quizid = '$quizid' AND u.id = '$studentid'";

            $sqlexecuted = $DB->get_recordset_sql($sql);

            $get_records_count = $DB->get_records('quizaccess_exproctor_wb_logs', array('quizid' => $quizid, 'courseid' => $courseid));

            echo '<hr>';

            if (count($get_records_count) > 0) {

                echo '<h3>' . get_string('pictures_webcam_used_report', 'quizaccess_exproctor') . '</h3>';

                $tablepictures = new flexible_table('exproctor-report-pictures' . $COURSE->id . '-' . $cmid);

                $tablepictures->define_columns(
                    array(get_string('std_name', 'quizaccess_exproctor'),
                        get_string('webcam_picture', 'quizaccess_exproctor'),
                        'Actions'
                    )
                );
                $tablepictures->define_headers(
                    array(get_string('std_name', 'quizaccess_exproctor'),
                        get_string('webcam_picture', 'quizaccess_exproctor'),
                        get_string('actions', 'quizaccess_exproctor')
                    )
                );
                $tablepictures->define_baseurl($url);

                $tablepictures->set_attribute('cellpadding', '2');
                $tablepictures->set_attribute('class', 'generaltable generalbox reporttable');

                $tablepictures->setup();
                $pictures = '';

                foreach ($sqlexecuted as $info) {
                    $pictures .= $info->webcamshot
                        ? '<a class="quiz-img-div" onclick="return confirm(`Are you sure want to delete this webcam picture?`)" href="?courseid=' . $courseid . '&quizid=' . $quizid . '&cmid=' . $cmid . '&reportid=' . $info->reportid . '&log_action=deletesinglewebcampic">
                    <img title="Click to Delete" width="320" src="' . $info->webcamshot . '" alt="' . $info->firstname . ' ' . $info->lastname . '" />
                   </a>'
                        : '';
                }

                $datapictures = array(
                    $info->firstname . ' ' . $info->lastname . '<br/>' . $info->email,
                    $pictures,
                    '<a onclick="return confirm(`Are you sure want to delete this webcam picture?`)" class="text-danger" href="?courseid=' . $courseid .
                    '&quizid=' . $quizid . '&cmid=' . $cmid . '&studentid=' . $info->studentid . '&reportid=' . $info->reportid . '&log_action=delete">Delete ALL Images</a>',
                );
                $tablepictures->add_data($datapictures);
                $tablepictures->finish_html();
            } else {
                echo '<h3>' . get_string('pictures_webcam_no_report', 'quizaccess_exproctor') . '</h3>';
            }
        }
    }
} else {
    // User has no permissions to view this page.
    echo '<div class="box generalbox m-b-1 adminerror alert alert-danger p-y-1">' .
        get_string('no_permission_report', 'quizaccess_exproctor') . '</div>';
}

# View screen shot
if (has_capability('quizaccess/exproctor:view_report', $context, $USER->id) && $cmid != null && $courseid != null) {
    if ($screenproctoringrequired) {
        // Check if report if for some user.
        if ($studentid != null && $quizid != null && $courseid != null && $reportid != null) {
            // Report for this user.
            $sql = "SELECT e.id as reportid, e.userid as studentid, e.screenshot as screenshot, e.attemptid as attemptid,
         e.timemodified as timemodified, u.firstname as firstname, u.lastname as lastname, u.email as email
         from  {quizaccess_exproctor_sc_logs} e INNER JOIN {user} u  ON u.id = e.userid
         WHERE e.courseid = '$courseid' AND e.quizid = '$quizid' AND u.id = '$studentid' AND e.id = '$reportid'";
        }

        if ($studentid == null && $quizid != null && $courseid != null) {
            // Report for all users.
            $sql = "SELECT  DISTINCT e.userid as studentid, u.firstname as firstname, u.lastname as lastname,
                u.email as email, max(e.screenshot) as screenshot,max(e.id) as reportid, max(e.attemptid) as attemptid,
                max(e.timemodified) as timemodified
                from  {quizaccess_exproctor_sc_logs} e INNER JOIN {user} u ON u.id = e.userid
                WHERE e.courseid = '$courseid' AND e.quizid = '$quizid'
                group by e.userid, u.firstname, u.lastname, u.email";
        }

        // Print report.
        $table = new flexible_table('exproctor-report-' . $COURSE->id . '-' . $cmid);

        $table->define_columns(array('fullname', 'email', 'dateverified', 'actions'));
        $table->define_headers(
            array(
                get_string('user'),
                get_string('email'),
                get_string('dateverified', 'quizaccess_exproctor'),
                get_string('actions', 'quizaccess_exproctor')
            )
        );

        $table->define_baseurl($url);

        $table->set_attribute('cellpadding', '5');
        $table->set_attribute('class', 'generaltable generalbox reporttable');
        $table->setup();

        // Prepare data.
        $sqlexecuted = $DB->get_recordset_sql($sql);

        foreach ($sqlexecuted as $info) {
            $data = array();
            $data[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $info->studentid .
                '&course=' . $courseid . '" target="_blank">' . $info->firstname . ' ' . $info->lastname . '</a>';

            $data[] = $info->email;

            $data[] = date("Y/M/d H:m:s", $info->timemodified);

            if ($screenproctoringrequired) {
                $data[] = '<a href="?courseid=' . $courseid .
                    '&quizid=' . $quizid . '&cmid=' . $cmid . '&studentid=' . $info->studentid . '&reportid=' . $info->reportid . '">' .
                    get_string('screen_report', 'quizaccess_exproctor') . '</a>';
            }

            $table->add_data($data);
        }

        if (!$webcamproctoringrequired) {
            $table->finish_html();
        }

        // Print image results for screen shots.

        if ($studentid != null && $cmid != null && $courseid != null && $reportid != null) {

            $data = array();
            $sql = "SELECT e.id as reportid, e.userid as studentid, e.screenshot as screenshot, e.attemptid as attemptid,
        e.timemodified as timemodified, u.firstname as firstname, u.lastname as lastname, u.email as email
        from {quizaccess_exproctor_sc_logs} e INNER JOIN {user} u  ON u.id = e.userid
        WHERE e.courseid = '$courseid' AND e.quizid = '$quizid' AND u.id = '$studentid'";

            $sqlexecuted = $DB->get_recordset_sql($sql);

            $get_records_count = $DB->get_records('quizaccess_exproctor_sc_logs', array('quizid' => $quizid, 'courseid' => $courseid));


            echo '<hr>';

            if (count($get_records_count) > 0) {
                echo '<h3>' . get_string('pictures_screen_used_report', 'quizaccess_exproctor') . '</h3>';

                $tablepictures = new flexible_table('exproctor-report-pictures' . $COURSE->id . '-' . $quizid);

                $tablepictures->define_columns(
                    array(get_string('std_name', 'quizaccess_exproctor'),
                        get_string('screen_picture', 'quizaccess_exproctor'),
                        'Actions'
                    )
                );
                $tablepictures->define_headers(
                    array(get_string('std_name', 'quizaccess_exproctor'),
                        get_string('screen_picture', 'quizaccess_exproctor'),
                        get_string('actions', 'quizaccess_exproctor')
                    )
                );
                $tablepictures->define_baseurl($url);

                $tablepictures->set_attribute('cellpadding', '2');
                $tablepictures->set_attribute('class', 'generaltable generalbox reporttable');

                $tablepictures->setup();
                $pictures = '';

                foreach ($sqlexecuted as $info) {
                    $pictures .= $info->screenshot
                        ? '<a class="quiz-img-div" onclick="return confirm(`Are you sure want to delete this screen shot?`)" href="?courseid=' . $courseid . '&quizid=' . $quizid . '&cmid=' . $cmid . '&reportid=' . $info->reportid . '&log_action=deletesinglescreenshot">
                    <img title="Click to Delete" width="320px" src="' . $info->screenshot . '" alt="' . $info->firstname . ' ' . $info->lastname . '" />
                   </a>'
                        : '';
                }

                $datapictures = array(
                    $info->firstname . ' ' . $info->lastname . '<br/>' . $info->email,
                    $pictures,
                    '<a onclick="return confirm(`Are you sure want to delete this screen shot?`)" class="text-danger" href="?courseid=' . $courseid .
                    '&quizid=' . $quizid . '&studentid=' . $info->studentid . '&reportid=' . $info->reportid . '&log_action=delete">Delete ALL Images</a>',
                );
                $tablepictures->add_data($datapictures);
                $tablepictures->finish_html();
            } else {
                echo '<h3>' . get_string('pictures_screen_no_report', 'quizaccess_exproctor') . '</h3>';
            }
        }
    }
} else {
    // User has no permissions to view this page.
    echo '<div class="box generalbox m-b-1 adminerror alert alert-danger p-y-1">' .
        get_string('no_permission_report', 'quizaccess_exproctor') . '</div>';
}

echo '</div>';
echo $OUTPUT->footer();

$icon_path = new moodle_url('/mod/quiz/accessrule/exproctor/pix/bin.png');
echo "<style> .quiz-img-div{position:relative; display: inline-block;}.quiz-img-div:hover:after{content:'';position:absolute;left: 0px;top: 0px;bottom: 0px;width: 100%;background: url('$icon_path') center no-repeat;background-size: 25px;}.quiz-img-div:hover img{opacity: 0.1;} </style>";
