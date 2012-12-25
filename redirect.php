<?php

	/**
	 * redirect.php
	 *
	 * @uses Pancake
	 * @package pp33.de
	 * @link pp33.de
	 * @author Yussuf Khalil
	 */

	if(!isset($pp33)) {
		apache_child_terminate();
		exit;
	} else if(DEBUG_PP33) {
		apache_child_terminate();
	}
	
	$longURL = $pp33->getLongURL($_GET['url']);
	
	if($longURL === false) {
		die('Unknown URL.');
	}
	
	header('Location: ' . $longURL);
?>
