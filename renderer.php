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
 * Logic Certificate question renderer class.
 *
 * @package	   qtype
 * @subpackage logic_certificate
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for Logic Certificate questions.
 *
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_logic_certificate_renderer extends qtype_renderer
{
	
	/**
	 * Generate the display of the formulation part of the question. This is the area that
	 * contains the quetsion text, and the controls for students to input their answers.
	 * Some question types also embed bits of feedback, for example ticks and crosses, in
	 * this area.
	 * 
	 * @param  question_attempt $qa The question attempt to display.
	 * @param  question_display_options  $options
	 *							    Controls what should and should not be displayed.  
	 * @return string			    HTML fragment.
	 */
	public function formulation_and_controls( question_attempt $qa, question_display_options $options )
	{
		$result = '';
		
		$question = $qa->get_question();
		$current_answer = $qa->get_last_qt_var( 'answer' );
		
		$question_text = $question->format_questiontext( $qa );

		$input_name = $qa->get_qt_field_name( 'answer' );
		$input_attributes = array(
			'type' => 'text',
			'name' => $input_name,
			'value' => $current_answer,
			'id' => $input_name,
			'size' => 50,
		);
		if( $options->readonly ) $input_attributes['readonly'] = 'readonly';

		$feedback_image = '';
		if( $options->correctness )
		{
			$input_attributes['class'] = $this->feedback_class( $qa->get_last_step()->get_fraction() );
			$feedback_image = $this->feedback_image( $qa->get_last_step()->get_fraction() );
		}
		
		$link = '';
		if( $question->applet_url )
		{
			$link = html_writer::tag(
				'a',
				$question->applet_url,
				array('class' => 'applet_url', 'href' => $question->applet_url, 'target' => '_blank')
			);
		}
		
		$result .= html_writer::tag( 'div', $question_text.$link, array('class' => 'qtext') );

		$result .= html_writer::start_tag( 'div', array('class' => 'ablock') );

// 			$result .= html_writer::tag(
// 				'label',
// 				get_string('certificate', 'qtype_logic_certificate'),
// 				array('for' => $input_attributes['id'])
// 			);
			$result .= html_writer::start_tag( 'span', array('class' => 'answer') );
			$result .= html_writer::empty_tag( 'input', $input_attributes );
			$result .= $feedback_image;
			$result .= html_writer::end_tag( 'span' );

		$result .= html_writer::end_tag( 'div' ); // ablock
		
		if ($qa->get_state() == question_state::$invalid)
		{
			$result .= html_writer::nonempty_tag(
				'div',
				$question->get_validation_error( array('answer' => $current_answer) ),
				array( 'class' => 'validationerror')
			);
		}
		
// 		echo '<pre>';
// 		print_r($question->applet_url);
// 		echo '</pre>';
		
		return $result;
	}

	/**
	 * Generate the specific feedback. This is feedback that varies according to the
	 * reponse the student gave.
	 * 
	 * @param  question_attempt $qa The question attempt to display.
	 * @return string			   HTML fragment.
	 */
	public function specific_feedback( question_attempt $qa )
	{
		return '';
	}

	/**
	 * Gereate an automatic description of the correct response to this question. Not all
	 * question types can do this. If it is not possible, this method should just return
	 * an empty string.
	 * 
	 * @param  question_attempt $qa The question attempt to display.
	 * @return string			   HTML fragment.
	 */
	public function correct_response( question_attempt $qa )
	{
		return '';
	}

}


