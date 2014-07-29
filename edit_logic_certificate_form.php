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
 * Defines the editing form for the Logic Certificate question type.
 *
 * @package	   qtype
 * @subpackage logic_certificate
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Logic Certificate question editing form definition.
 *
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_logic_certificate_edit_form extends question_edit_form
{

	/**
	 * Add any question-type specific form fields.
	 * 
	 * @param object $mform  The form being built.
	 */
	protected function definition_inner( $mform )
	{
		$mform->addElement( 
			'text', 
			'applet_id', 
			get_string( 'enter_applet_id', 'qtype_logic_certificate' )
		);
		$mform->setType( 'applet_id', PARAM_INT );
		$mform->addRule(
			'applet_id', 
			get_string( 'missing_applet_id', 'qtype_logic_certificate' ), 
			'required', 
			null, 
			'server'
		);

		$mform->addElement( 
			'text', 
			'applet_url', 
			get_string( 'enter_applet_url', 'qtype_logic_certificate' )
		);
		$mform->setType( 'applet_url', PARAM_RAW );
// 		$mform->addRule(
// 			'applet_url', 
// 			get_string( 'missing_applet_url', 'qtype_logic_certificate' ), 
// 			'required', 
// 			null, 
// 			'server'
// 		);
	}

	/**
	 * Perform an preprocessing needed on the data passed to set_data() before it is used to initialise the form. 
	 * 
	 * @param  object $question  The data being passed to the form. 
	 * @return object            The modified question data.
	 */
	protected function data_preprocessing( $question )
	{
		$question = parent::data_preprocessing($question);
		return $question;
	}

	/**
	 * 
	 * 
	 * 
	 */
	public function validation( $data, $files )
	{
		$errors = parent::validation( $data, $files );

		$applet_id = null;
		if( array_key_exists('applet_id', $data) )
		{
			$applet_id = $data['applet_id'];
			$applet_id = intval( $applet_id );
		}
		
		if( $applet_id <= 0 )
		{
			$errors['applet_id'] = get_string( 'invalid_applet_id', 'qtype_logic_certificate' );
		}

// 		$applet_url = null;
// 		if( array_key_exists('applet_url', $data) )
// 		{
// 			$applet_url = $data['applet_url'];
// 		}
// 		
// 		if( !$applet_url )
// 		{
// 			$errors['applet_url'] = get_string( 'invalid_applet_url', 'qtype_logic_certificate' );
// 		}
		
		return $errors;
	}
	
	/**
	 * Override this in the subclass to question type name. 
	 * 
	 * @return string       the question type name, should be the same as the name() 
	 *                      method in the question type class. 
	 */
	public function qtype()
	{
		return 'logic_certificate';
	}

}


