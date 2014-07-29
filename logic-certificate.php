<?php
/**
 * Logic Certificate class.
 * 
 * @package	   qtype
 * @subpackage certificate
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


 
/**
 * Decodes a certificate to determine if it is valid.
 * 
 * @copyright  2014 Crystal Barton (cbarto11@uncc.edu)
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * Usage:
 * 
 * $certificate = '0738-9107-9958-7661';
 * $username = 'cbarto11';
 * $applet_id = 30;
 * $is_valid = Logic_Certificate::validate( $certificate, $applet_id, $username );
 * 
 * OR
 * 
 * $certificate = '0738-9107-9958-7661';
 * $username = 'cbarto11';
 * $applet_id = 30;
 * $cert = new Logic_Certificate( $certificate );
 * $cert->print_data();
 * $is_valid = $cert->is_valid( $applet_id, $username );
 */
class Logic_Certificate
{

	private $certificate;
	private $is_valid;
	private $key;
	private $applet_id;
	private $username;
	
	
	/**
	 * Default Constructor.
	 * Decodes the certificate and stores the information.
	 * 
	 * @param string $certificate  The logic applet's certificate code.
	 */
	public function __construct( $certificate )
	{
		$this->certificate = $certificate;
		$this->decode();
	}
	
	
	/**
	 * Determines if the certificate code is valid.  
	 * If the applet id and student username is included, the decoded information is 
	 * checked to ensure that the data matches.
	 * 
	 * @param  string  $certificate The logic applet's certificate code.
	 * @param  string  $applet_id   The logic applet's id.
	 * @param  string  $username    The username of the student that earned certificate.
	 * @return boolean              True if the certificate is valid, else false.
	 */
	public static function validate( $certificate, $applet_id = null, $username = null )
	{
		$cert = new Logic_Certificate( $certificate );
		return $cert->is_valid( $applet_id, $username );
	}
	
	
	/**
	 * Determines if the certificate code is valid.  
	 * If the applet id and student username is included, the decoded information is 
	 * checked to ensure that the data matches.
	 * 
	 * @param  string  $applet_id   The logic applet's id.
	 * @param  string  $username    The username of the student that earned certificate.
	 * @return boolean              True if the certificate is valid, else false.
	 */
	public function is_valid( $applet_id = null, $username = null )
	{
		$is_valid = $this->is_valid;

		if( ($applet_id !== null) && ($this->applet_id != $applet_id) )
		{
			file_put_contents( dirname(__FILE__).'/test.log', "INVALID APPLET ID\n", FILE_APPEND );
			$is_valid = false;
		}
		
		if( ($username !== null) && (strtoupper(substr($username, 0, 3)) !== strtoupper($this->username)) )
		{
			$is_valid = false;
		}

		return $is_valid;
	}
	
	
	/**
	 * Prints the certificate's data, such as applet information and username, in HTML
	 * format.
	 */
	public function print_data()
	{
		echo '<div class="applet-data">';
		
		echo '<div class="certificate">Certificate: '.$this->certificate.'</div>';
		echo '<div class="is-valid">'.
			($this->is_valid() ? 
			   'This is a valid certificate.' : 
			   'Not a valid certificate.').
			'</div>';
		if( $this->is_valid() )
		{
			echo '<div class="applet">Applet ID: '.$this->applet_id.'</div>';
			echo '<div class="username">Username: '.$this->username.'</div>';
		}
		
		echo '</div>';
	}
	
	
	/**
	 * Attempts to decode the certificate and populate the data.
	 */
	private function decode()
	{
		$this->is_valid = false;
		$this->key = -1;
		$this->applet_id = -1;
		$this->username = '';
		
		switch( strlen($this->certificate) )
		{
			case 19:
				$this->is_valid = $this->test_standard_certificate();
				break;
			case 29:
				$this->is_valid = $this->test_custom_certificate();
				break;
		}
	}
	
	
	/**
	 * Attempts to decode the "standard" certificate and populate the data.
	 *
	 * @return boolean  True if the certificate is valid, else false.
	 */
	private function test_standard_certificate()
	{
		if( !preg_match('"([A-Z0-9]{4})\-([A-Z0-9]{4})\-([A-Z0-9]{4})\-([A-Z0-9]{4})"', $this->certificate) )
			return false;

		$this->key = intval( substr($this->certificate, 0, 2) );
	
		if( $this->key < 36 )
		{
			$this->applet_id = intval( substr($this->certificate, 2, 2) ) - $this->key;			

			$id1 = intval( substr($this->certificate, 12, 2) ) + $this->key;
			$id2 = intval( substr($this->certificate, 15, 2) ) + $this->key;
			$id3 = intval( substr($this->certificate, 17, 2) ) + $this->key;
		}
		else
		{
			$this->applet_id = intval( substr($this->certificate, 2, 2), 36 ) - $this->key;

			$id1 = intval( substr($this->certificate, 12, 2), 36 ) + $this->key;
			$id2 = intval( substr($this->certificate, 15, 2), 36 ) + $this->key;
			$id3 = intval( substr($this->certificate, 17, 2), 36 ) + $this->key;
		}

		$this->username = chr($id1) . chr($id2) . chr($id3);
	
		return true;
	}
	
	
	/**
	 * Attempts to decode the "custom" certificate and populate the data.
	 * 
	 * @return boolean  True if the certificate is valid, else false.
	 */
	private function test_custom_certificate()
	{
		if( !preg_match('"([0-9]{5})\-([0-9]{5})\-([0-9]{5})\-([0-9]{5})\-([0-9]{5})"', $this->certificate) )
			return false;

		$this->key = intval( substr($this->certificate, 0, 1) );
		$this->applet_id = intval( substr($this->certificate, 6, 1) ) - $this->key;
		
		$id1 = intval( substr($this->certificate, 22, 1) . substr($this->certificate, 24, 1) ) + $this->key;
		$id2 = intval( substr($this->certificate, 25, 2) ) + $this->key;
		$id3 = intval( substr($this->certificate, 27, 2) ) + $this->key;
		
		$this->username = chr($id1) . chr($id2) . chr($id3);
		
		return true;
	}

}


