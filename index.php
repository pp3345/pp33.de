<?php

	/**
	 * index.php
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

?>
<!doctype html>
<html>
	<head>
		<title>pp33.de URL Shortener</title>
	</head>
	<body>
		<h1>pp33.de URL Shortener</h1>
		<?php 
			if(isset($_POST['longURL'])) {
				$shortURL = $pp33->getShortURL($_POST['longURL']);
		?>
		Congratulations! <a href="<?=$_POST['longURL']?>"><?=$_POST['longURL']?></a> was shortened to 
		<a href="http://pp33.de/<?=$shortURL?>">http://pp33.de/<?=$shortURL?></a>
		<br/>
		<br/>
		<?php
			}
		?>
		Shorten URL:
		<br/>
		<form method="POST" action="index.php">
			<input type="url" autofocus placeholder="Enter your URL" name="longURL" />
			<input type="submit" value="Shorten!" />
		</form>
	</body>
</html>