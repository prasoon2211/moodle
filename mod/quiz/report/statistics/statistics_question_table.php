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
 * Quiz statistics report, table for showing statistics about a particular question.
 *
 * @package   quiz_statistics
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * This table shows statistics about a particular question.
 *
 * Lists the responses that students made to this question, with frequency counts.
 *
 * The responses may be grouped, either by subpart of the question, or by the
 * answer they match.
 *
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_statistics_question_table extends flexible_table {
    /** @var object full question object for this question. */
    protected $questiondata;

    /** @var  int no of attempts. */
    protected $s;

    /**
     * Constructor.
     * @param int $qid the id of the particular question whose statistics are being
     * displayed.
     */
    public function __construct($qid) {
        parent::__construct('mod-quiz-report-statistics-question-table' . $qid);
    }

    /**
     * @param moodle_url $reporturl
     * @param object     $questiondata
     * @param integer    $s             number of attempts on this question.
     * @param \core_question\statistics\responses\analysis_for_question $responseanalysis
     */
    public function question_setup($reporturl, $questiondata, $s, $responseanalysis) {
        $this->questiondata = $questiondata;
        $this->s = $s;

        $this->define_baseurl($reporturl->out());
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable generalbox boxaligncenter quizresponseanalysis');

        // Define the table columns.
        $columns = array();
        $headers = array();

        if ($responseanalysis->has_subparts()) {
            $columns[] = 'part';
            $headers[] = get_string('partofquestion', 'quiz_statistics');
        }

        if ($responseanalysis->has_multiple_response_classes()) {
            $columns[] = 'responseclass';
            $headers[] = get_string('modelresponse', 'quiz_statistics');

            if ($responseanalysis->has_actual_responses()) {
                $columns[] = 'response';
                $headers[] = get_string('actualresponse', 'quiz_statistics');
            }

        } else {
            $columns[] = 'response';
            $headers[] = get_string('response', 'quiz_statistics');
        }

        $columns[] = 'fraction';
        $headers[] = get_string('optiongrade', 'quiz_statistics');

        $columns[] = 'count';
        $headers[] = get_string('count', 'quiz_statistics');

        $columns[] = 'frequency';
        $headers[] = get_string('frequency', 'quiz_statistics');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(false);

        $this->column_class('fraction', 'numcol');
        $this->column_class('count', 'numcol');
        $this->column_class('frequency', 'numcol');

        $this->column_suppress('part');
        $this->column_suppress('responseclass');

        parent::setup();
    }

    protected function format_percentage($fraction) {
        return format_float($fraction * 100, 2) . '%';
    }

    /**
     * The mark fraction that this response earns.
     * @param object $response containst the data to display.
     * @return string contents of this table cell.
     */
    protected function col_fraction($response) {
        if (is_null($response->fraction)) {
            return '';
        }

        return $this->format_percentage($response->fraction);
    }

    /**
     * The frequency with which this response was given.
     * @param object $response contains the data to display.
     * @return string contents of this table cell.
     */
    protected function col_frequency($response) {
        if (!$this->s) {
            return '';
        }

        return $this->format_percentage($response->count / $this->s);
    }
}
