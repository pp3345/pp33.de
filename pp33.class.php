<?php

	/**
	 * index.php
	 *
	 * @uses Pancake
	 * @package pp33.de
	 * @link pp33.de
	 * @author Yussuf Khalil
	 */
	
	// We need the Pancake CodeCache for in memory URL storage
	if(PHP_SAPI != "pancake") {
		die('Unsupported SAPI');
	}
	
	const DEBUG_PP33 = false;

	/** 
	 * pp33 Main Class
	 * 
	 * @author Yussuf Khalil
	 */
	class pp33 {
		const DATABASE_PATH = "pp33.sqlite";
		/**
		 * 
		 * @var SQLite3
		 */
		private $database = null;
		private $inMemoryURLStorage = array();
		
		/**
		 * Creates a new pp33 object. Opens SQLite database.
		 */
		public function __construct() {
			try {
				// Load database
				$this->database = new SQLite3(self::DATABASE_PATH);
			} catch(Exception $e) {
				die((string) $e);
			}
		}
		
		/**
		 * Returns the shortened URL for a given URL
		 * 
		 * @param string $url Original URL
		 * @return string Shortened URL
		 */
		public function getShortURL($url) {
			// Try in-memory URL lookup
			if(isset($this->inMemoryURLStorage[$url])) {
				return $this->inMemoryURLStorage[$url];
			}
			
			// URL not stored in-memory, fetch from database
			$stmt = $this->database->prepare('SELECT short FROM urls WHERE long = :long');
			$stmt->bindValue(':long', $url, SQLITE3_TEXT);
			$result = $stmt->execute();
			if($result === false) {
				die('SQL Error: ' . $this->database->lastErrorMsg());
			}
			
			// Get result array and free result
			$resultArray = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();
			
			// Unknown URL, create new one
			if(!$result->numColumns() || !$result->columnType(0)) {
				return $this->makeShortURL($url);
			}

			// URL exists, load into in-memory storage and return
			$this->inMemoryURLStorage[$url] = $resultArray['short'];
			return $resultArray['short'];
			
		}
		
		/**
		 * Generates a new short URL
		 * 
		 * @param string $url Original URL
		 * @return string Shortened URL
		 */
		private function makeShortURL($url) {
			do {
				mt_srand();
				$shortURL = substr(base64_encode(mt_rand()), 0, 5);
			} while(in_array($shortURL, $this->inMemoryURLStorage)); // Protect from generating the same URL several times
			
			// Store new short URL in-memory
			$this->inMemoryURLStorage[$url] = $shortURL;
			
			// Save URL to database
			$stmt = $this->database->prepare('INSERT INTO urls (short, long) VALUES (:short, :long)');
			$stmt->bindValue(':short', $shortURL, SQLITE3_TEXT);
			$stmt->bindValue(':long', $url, SQLITE3_TEXT);
			$stmt->execute();
			
			// Return new short URL
			return $shortURL;
		}
		
		/**
		 * Gets the long URL for a given short URL
		 * 
		 * @param string $shortURL
		 * @return string|boolean Returns false in case short URL is unknown
		 */
		public function getLongURL($shortURL) {
			// Lookup URL in in-memory storage
			if($url = array_search($shortURL, $this->inMemoryURLStorage))
				return $url;
			
			// Lookup URL in database
			$stmt = $this->database->prepare('SELECT long FROM urls WHERE short = :short');
			$stmt->bindValue(':short', $shortURL, SQLITE3_TEXT);
			$result = $stmt->execute();
			
			if($result == false) {
				die('SQL Error: ' . $this->database->lastErrorMsg());
			}
			
			// Get result array and free result
			$resultArray = $result->fetchArray(SQLITE3_ASSOC);
			$result->finalize();
			
			// Unknown URL
			if(!$result->numColumns() || !$result->columnType(0)) {
				return false;
			}
			
			// URL exists, load into in-memory storage and return
			$this->inMemoryURLStorage[$resultArray['long']] = $shortURL;
			return $resultArray['long'];
		}
	}

	$pp33 = new pp33;	
?>