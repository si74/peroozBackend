<?php 

/*General purpose sanitizer class*/
/*Checks emails,sanitizes html, etc*/

class cleaner{

	protected $input;

	/*Constructor for the class*/
	/*full cleaner type true indicates this is a full scale cleaning and the html purifier will be required*/
	function __construct($input, $full_cleaner){
		$this->$input = $input;
		if ($full_cleaner){
			$this->purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
		}

	}

	/*Email Validation*/
	/*Returns string of email if true. Boolean false if not valid email*/
	public function check_email(){
		$result = filter_var($this->input, FILTER_VALIDATE_EMAIL);
		return $result;
	}

	/*URL Validation*/
	public function check_url(){
		$result = filter_var($this->input, FILTER_VALIDATE_URL);
		return $result;
	}

	/*Escape mysql characters*/
	/*Using PDO instead for this*/
	public function mysql_escape(){
	}

	/*Simple html sanitizer with html special entities*/
	public function simple_html_sanitize(){
		$safeHtmlresult = htmlentities($this->input, ENT_QUOTES, 'UTF-8');
		return $safeHtmlresult;
	}

	/*More complex html sanitizer using html purifier*/
	public function complex_html_sanitize(){
		$cleanHtml = $this->purifier->purify($this->input);
		return $cleaHtml;
	}

	/*Clean up data for presentation purposes*/
	public function html_for_display(){
		$cleanHtml= htmlspecialchars($this->input, ENT_QUOTES);
		return $cleaHtml;
	}

	
}

?>