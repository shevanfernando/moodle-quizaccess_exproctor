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
 * Link Generator for the quizaccess_proctoring plugin.
 *
 * @package    quizaccess_exproctor
 * @copyright  2022 Shevan Thiranja Fernando <w.k.b.s.t.fernando@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_exproctor;

use coding_exception;
use moodle_exception;
use moodle_url;

/**
 * Generate link for report
 */
class link_generator {
    /**
     * Create link
     *
     * @param string $courseid
     * @param string $quizid
     * @param string $cmid
     * @param bool $proctoring
     * @param bool $secure
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function get_link(string $courseid, string $quizid,
        string $cmid, bool $proctoring = false,
        bool $secure = true
    ): string {
        // Check of course module exists.
        get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);

        $url =
            new moodle_url('/mod/quiz/accessrule/exproctor/report.php?courseid=' . $courseid . '&cmid=' . $cmid . '&quizid=' .
                $quizid
            );
        if ($proctoring) {
            $secure ? $url->set_scheme('proctorings'
            ) : $url->set_scheme('proctoring');
        } else {
            $secure ? $url->set_scheme('https') : $url->set_scheme('http');
        }
        return $url->out();
    }
}
