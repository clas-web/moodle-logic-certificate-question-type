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
 * Logic Certificate Question definition class.
 *
 * @package	   qtype
 * @subpackage logic_certificate
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a Logic Certificate question.
 *
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_logic_certificate_question extends question_graded_automatically
{
	
	/**
	 * What data may be included in the form submission when a student submits this 
	 * question in its current state?
	 * 
	 * This information is used in calls to optional_param. The parameter name has 
	 * question_attempt::get_field_prefix() automatically prepended.
	 * 
	 * @return array|string     variable name => PARAM_... constant, or, as a special case
	 *                          that should only be used in unavoidable, the constant 
	 *                          question_attempt::USE_RAW_DATA meaning take all the raw 
	 *                          submitted data belonging to this question. 
	 */
	public function get_expected_data()
	{
		return array( 'answer' => PARAM_RAW_TRIMMED );
	}

	/**
	 * What data would need to be submitted to get this question correct. If there is more 
	 * than one correct answer, this method should just return one possibility. If it is 
	 * not possible to compute a correct response, this method should return null.
	 * 
	 * @return array|null       parameter name => value. 
	 */
	public function get_correct_response()
	{
		return null;
	}

	/**
	 * Use by many of the behaviours to determine whether the student has provided enough 
	 * of an answer for the question to be graded automatically, or whether it must be 
	 * considered aborted.
	 * 
	 * @param  array  $reponse  responses, as returned by question_attempt_step::get_qt_data().
	 * @return boolean          whether this response can be graded.
	 */
	public function is_gradable_response( array $response )
	{
		$certificate_number = $this->get_certificate_number( $response );
		if( !$certificate_number ) return false;

		$applet_id = $this->get_applet_id();
		if( !$applet_id ) return false;

		return true;
	}

	/**
	 * Used by many of the behaviours, to work out whether the student's response to the 
	 * question is complete. That is, whether the question attempt should move to the 
	 * COMPLETE or INCOMPLETE state.
	 * 
	 * @param  array $response  responses, as returned by question_attempt_step::get_qt_data().
	 * @return boolean          whether this response is a complete answer to this question.
	 */
	public function is_complete_response( array $response )
	{
		return true;
	}

	/**
	 * Produce a plain text summary of a response. 
	 * 
	 * @param  array  $response a response, as might be passed to grade_response().
	 * @return string           a plain text summary of that response, that could be used 
	 *                          in reports.     
	 */
	public function summarise_response( array $response )
	{
		if( !array_key_exists('answer', $response) )
			return null;
		return $response['answer'];
	}

	/**
	 * Categorise the student's response according to the categories defined by get_possible_responses.
	 * 
	 * @param  array  $response a response, as might be passed to grade_response().
	 * @return array            subpartid => question_classified_response objects. returns 
	 *                          an empty array if no analysis is possible.
	 */
	public function classify_response( array $response )
	{
		return null;
	}

	/**
	 * In situations where is_gradable_response() returns false, this method should 
	 * generate a description of what the problem is. 
	 * 
	 * @return string           The message.
	 */
	public function get_validation_error( array $response )
	{
        if ($this->is_gradable_response($response)) return '';
        return get_string( 'invalid_answer', 'qtype_logic_certificate' );
	}

	/**
	 * 
	 * 
	 * 
	 */
	public function is_same_response( array $prevresponse, array $newresponse )
	{
		return false;
		return question_utils::arrays_same_at_key_missing_is_blank(
				$prevresponse, $newresponse, 'answer');
	}

	/**
	 * Grade a response to the question, returning a fraction between get_min_fraction() 
	 * and 1.0, and the corresponding question_state right, partial or wrong. 
	 * 
	 * @param  array  $response responses, as returned by question_attempt_step::get_qt_data().
	 * @return array(number, integer) 
	 *                          the fraction, and the state.
	 */
	public function grade_response( array $response )
	{
		$certificate_number = $this->get_certificate_number( $response );
		if( !$certificate_number )
		{
			// TODO: invalid
			return array( 0.0, question_state::$gradedwrong );
		}
		
		$applet_id = $this->get_applet_id();
		if( !$applet_id )
		{
			// TODO: invalid
			return array( 0.0, question_state::$gradedwrong );
		}
		
		global $USER;
		$username = $USER->username;
		
		$valid = Logic_Certificate::validate( $certificate_number, $applet_id, $username );
		if( $valid ) return array( 1.0, question_state::$gradedright );
		return array( 0.0, question_state::$gradedwrong );
	}

	/**
	 * Checks whether the users is allow to be served a particular file. 
	 * 
	 * @param  question_attempt $qa the question attempt being displayed.
	 * @param  question_display_options $options
	 *                              the options that control display of the question.
	 * @param  string   $component  the name of the component we are serving files for.
	 * @param  string   $filearea   the name of the file area. 
	 * @param  array    $args       the remaining bits of the file path. 
	 * @param  boolean  $forcedownload 
	 *                              whether the user must be forced to download the file. 
	 * @return booelan              true if the user can access this file.
	 */
	public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload)
	{
		return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
	}

	/**
	 * Retreives the Logic Certificate number from the question's response.
	 * 
	 * @param  array  $response responses, as returned by question_attempt_step::get_qt_data().
	 * @return null|string      The certificate number, or null if an error occurs.
	 */
	private function get_certificate_number( array $response )
	{
		if( !array_key_exists('answer', $response) ) return null;
		return $response['answer'];
	}
	
	/**
	 * Retreives the applet id from the question's options.
	 * 
	 * @return null|string      The applet id, or null if an error occurs.
	 */
	private function get_applet_id()
	{
		global $DB;
		$options = $DB->get_record( 'qtype_logic_certificate', array( 'questionid' => $this->id ) );
		if( !array_key_exists('applet_id', $options) ) return null;
		return $options->applet_id;
	}

}


