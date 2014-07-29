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
 * Question type class for the Logic Certificate question type.
 *
 * @package	   qtype
 * @subpackage logic_certificate
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/logic_certificate/question.php');

require_once($CFG->dirroot . '/question/type/logic_certificate/logic-certificate.php');


/**
 * The Logic Certificate question type.
 *
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_logic_certificate extends question_type
{
	
	/**
	 * If your question type has a table that extends the question table, and you want 
	 * the base class to automatically save, backup and restore the extra fields, override 
	 * this method to return an array wherer the first element is the table name, and the 
	 * subsequent entries are the column names (apart from id and questionid).
	 * 
	 * @return mixed array as above, or null to tell the base class to do nothing. 
	 */
	public function extra_question_fields()
	{
		return array( 'qtype_logic_certificate', 'applet_id', 'applet_url' );
	}
	
	/**
	 * Saves question-type specific options
	 * This is called by save_question() to save the question-type specific data.
	 * 
	 * @param  object $question This holds the information from the editing form, it is 
	 *                          not a standard question object.  
	 * @return object           $result->error or $result->noticeyesno or $result->notice
	 */
	public function save_question_options( $question )
	{
		global $DB;
		$result = new stdClass();

		$applet_id = $question->applet_id;
		if( $applet_id <= 0 )
		{
			$result->error = get_string( 'invalid_applet_id', 'qtype_logic_certificate' );
			return $result;
		}

		$applet_url = $question->applet_url;
// 		if( !$applet_url )
// 		{
// 			$result->error = get_string( 'invalid_applet_url', 'qtype_logic_certificate' );
// 			return $result;
// 		}

		// Save question options in question_truefalse table.
		$options = $DB->get_record( 'qtype_logic_certificate', array( 'questionid' => $question->id ) );
		if( $options )
		{
			$options->questionid = $question->id;
			$options->applet_id  = $applet_id;
			$options->applet_url = $applet_url;
			$DB->update_record( 'qtype_logic_certificate', $options );
		}
		else
		{
			$options = new stdClass();
			$options->questionid = $question->id;
			$options->applet_id  = $applet_id;
			$options->applet_url = $applet_url;
			$DB->insert_record( 'qtype_logic_certificate', $options );
		}
					
		parent::save_question_options( $question );
	}

	/**
	 * Loads the question type specific options for the question.
	 * This function loads any question type specific options for the question from the 
	 * database into the question object. This information is placed in the 
	 * $question->options field. A question type is free, however, to decide on a 
	 * internal structure of the options field. 
	 * 
	 * @param object $question  The question object for the question. This object should 
	 *                          be updated to include the question type specific 
	 *                          information (it is passed by reference).
	 * @return boolean          Indicates success or failure.
	 */
	public function get_question_options( $question )
	{
		global $DB, $OUTPUT;
		
		$options = $DB->get_record( 'qtype_logic_certificate', array( 'questionid' => $question->id ) );
		if( !$options )
		{
			echo $OUTPUT->notification( get_string( 'missing_options', 'qtype_logic_certificate' ) );
			return false;
		}
		else
		{
			$question->options = $options;
		}

		return true;
	}

	/**
	 * Move all the files belonging to this question from one context to another.
	 * 
	 * @param  int  $questionid     The question being moved.
	 * @param  int  $oldcontextid   The context it is moving from.
	 * @param  int  $newcontextid   The context it is moving to. 
	 */
	public function move_files( $questionid, $oldcontextid, $newcontextid )
	{
		parent::move_files($questionid, $oldcontextid, $newcontextid);
		$this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
	}

	/**
	 * Delete all the files belonging to this question.
	 * 
	 * @param  int  $questionid     The question being deleted.
	 * @param  int  $contextid      The context the question is in.
	 */
	protected function delete_files( $questionid, $contextid )
	{
		parent::delete_files($questionid, $contextid);
		$this->delete_files_in_answers($questionid, $contextid);
	}

	/**
	 * Initialise the common question_definition fields.
	 * 
	 * @param question_definition $question
	 *                              The question_definition we are creating.
	 * @param object $questiondata  The question data loaded from the database.
	 */
	protected function initialise_question_instance( question_definition $question, $questiondata )
	{
		parent::initialise_question_instance($question, $questiondata);
		 $this->initialise_question_answers($question, $questiondata);
	}

	/**
	 * 
	 * 
	 * @param  object $questiondata The question data loaded from the database.
	 * @return number|null          Either a fraction estimating what the student would 
	 *                              score by guessing, or null, if it is not possible 
	 *                              to estimate. 
	 */
	public function get_random_guess_score( $questiondata )
	{
		return null;
	}

	/**
	 * This method should return all the possible types of response that are recognised 
	 * for this question.
	 * 
	 * The question is modelled as comprising one or more subparts. For each subpart, 
	 * there are one or more classes that that students response might fall into, each of
	 * those classes earning a certain score.
	 * 
	 * For example, in a shortanswer question, there is only one subpart, the text entry
	 * field. The response the student gave will be classified according to which of the
	 * possible $question->options->answers it matches.
	 * 
	 * For the matching question type, there will be one subpart for each question stem,
	 * and for each stem, each of the possible choices is a class of student's response.
	 * 
	 * A response is an object with two fields, ->responseclass is a string presentation
	 * of that response, and ->fraction, the credit for a response in that class.
	 * 
	 * Array keys have no specific meaning, but must be unique, and must be the same if
	 * this function is called repeatedly.
	 * 
	 * @param  object  $question     The question definition data.
	 * @return array                 Keys are subquestionid, values are arrays of possible
	 *                               responses to that subquestion. 
	 */
	public function get_possible_responses( $questiondata )
	{
		return array();
	}

}


