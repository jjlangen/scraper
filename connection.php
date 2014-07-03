<?
// This class connects to the specified domain using the POST string and returns the HTML from the page

class Connection {
	protected $_html;

	// Start here when a Connection object is created
	public function __construct($dataUrl, $postData) {

		// Initiate curl
		$ch = curl_init();

		// Set the URL to work with
		curl_setopt($ch, CURLOPT_URL, $dataUrl);

		// Enable HTTP POST
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the post parameters
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		// Give the cookie a unique name
		$urlAbbrevation = explode('/', $dataUrl);
		$urlAbbrevation = substr($urlAbbrevation[2], 0, 6);
		$cookieName = 'cookie_' . $urlAbbrevation . '.txt';

		// Handle cookies for the login
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieName);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieName);
		 
		// Return the full page instead of true/false
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		// Execute the login request
		curl_exec($ch);

		$this->_html = curl_exec($ch);

		curl_close($ch);


	}
	
	// When a Connection object is cast to a string return the HTML page
	public function __toString() {
		if(!empty($this->_html)) {
			return $this->_html;
		} else {
			echo "Empty page";
		}
	}

	public function prepareASPheader($html, $target) {
		// ASP.net crap
		preg_match('~<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" />~', $html, $viewstate);
		preg_match('~<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" />~', $html, $eventValidation);
		$viewstate = $viewstate[1];
		$eventValidation = $eventValidation[1];

		// Prepare header
		$params = array(
			'__EVENTTARGET' => $target,
			'__EVENTARGUMENT' => '',
			'__LASTFOCUS' => '',
			'__VIEWSTATE' => $viewstate,
			'__EVENTVALIDATION' => $eventValidation
			);

		return $params;
	}

}

?>