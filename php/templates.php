<?php
/*
+--------------------------------------------------------------------------
|
|	Tänne kaikki teeman palaset
|
+--------------------------------------------------------------------------
*/

class motulus_templates {
	private $content;

	function Header($title) {
		echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Motulus - $title</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" type="text/css" href="styles/screen.css" media="screen" />
</head>
 
<body> 

EOF;
	}

	function Navigation($currentMenu) {
		echo <<<EOF
<div id="header">
	<div id="header-menu">
		<img src="logo.png" alt="Motulus" />
		<a href="index.html" title="">Etusivu</a>
		<a href="hops.html" title="">HOPS</a>
		<a href="ilmoittautuminen.html" title="ilmoittautuminen">ilmoittaudu</a>
		<a href="asetukset.html" title="">Asetukset</a>
	</div>
</div>

<div class="colmask rightmenu">
	<div class="colleft">  
EOF;
		if($currentMenu == "HOPS") {
			echo <<<EOF
		<div class="col1">
			<!-- Column 1 start -->
			 
			<!-- Column 1 end -->
		</div>
		<div class="col2">
			<!-- Column 2 start -->
			 
			<!-- Column 2 end -->
		</div> 
EOF;
		}

		echo <<<EOF
	</div>
</div> 
EOF;
	}

	function Content() {
		echo $content;
	}

	function $footer() {
		echo <<<EOF
<div id="footer">
	<p>&copy; codename Motulus 2011</p>
</div>
</body>
</html> 
EOF;
	}

	function PrintPage($title, $currentMenu) {
		$this->Header($title);
		$this->Navigation($currentMenu);
		$this->Content();
		$this->Footer();
	}

	function PrintContent() {
		echo $content;
	}

}
