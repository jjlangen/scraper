<?php
$scraper = new VuRoosterScraper();

class VuRoosterScraper {

	public function __construct() {
		if(isset($_GET['course'])) {

			$course = $_GET['course'];
			 
			// Prepare the 'all courses' query
			if($course == 'all') {

				// Load the page with the courses
				$store = VuRoosterScraper::getCourses();
				$xpath = VuRoosterScraper::loadXPath($store);

				// Select the course codes
				$entryList = $xpath->query("//select[@id='dlObject']/option/@value");

				// Save the course codes to an array
				$courses = array();
				foreach ($entryList as $entry) {
					$courses[] = $entry->nodeValue;
				}

				// Select the course names
				$entryList = $xpath->query("//select[@id='dlObject']/option");

				// Save the course names to an array
				$courseNames = array();
				foreach ($entryList as $entry) {
					$courseNames[] = $entry->nodeValue;
				}

				// Combine the two arrays: course codes are the key corresponding course names as value
				$courses = array_combine($courses, $courseNames);

			} else {
				$store = VuRoosterScraper::getTimetable($course);
				$xpath = VuRoosterScraper::loadXPath($store);

				// Select the right data
				$courseData = $xpath->query("/html/body/table[@class='spreadsheet']/tr[not(@class)]/td");

				// Save data to 2d array
				$res = array();
				$x = 0;
				$i = 0;
				$keynames = array("vakcode","begindatum","weken","start","einde","vaknaam","beschrijving","type","zalen","docent","opmerking");

				foreach($courseData as $cc) {
					if($x >= 11) {
						$res[$i] = array_combine($keynames, $res[$i]);
						$x = 0;
						$i++;
					}
					$res[$i][$x] = trim(utf8_decode($cc->nodeValue));
					$x++;
				}
				$courses = $res;
			}

			// Encode as JSON
			$output = json_encode(array('courses' => $courses));

			echo $output;
		}
	}

	public function getTimetable($coursecode) {
		$store = VuRoosterScraper::getCourses();

		// Prepare ASP header
		$postHeader = Connection::prepareASPheader($store, 'bGetTimetable');
		$postHeader = http_build_query($postHeader);
		$postHeader .= "&lbWeeks=1-22&lbWeeks=23-52";
		$postHeader .= "&dlObject=" . $coursecode;

		// Step 3: load timetable page
		$store = new Connection('https://rooster.vu.nl/Default.aspx', $postHeader);

		return $store;
	}

	public function getCourses() {
		include('connection.php');

		// Step 1: load page
		$store = new Connection('https://rooster.vu.nl/Default.aspx', '');

		// Prepare header
		$postHeader = Connection::prepareASPheader($store, 'LinkBtn_modules');

		// Step 2: load 'Vak' page
		$store = new Connection('https://rooster.vu.nl/Default.aspx', $postHeader);

		return $store;
	}

	public function loadXPath($store) {
		$dom = new DOMDocument(); 
		@$dom->loadHtml($store);
		$xpath = new DOMXPath($dom);
		return $xpath;
	}
}
?>