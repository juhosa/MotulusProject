<?php
/*
+--------------------------------------------------------------------------
|
|	Tänne kaikki teeman palaset
|
+--------------------------------------------------------------------------
*/

class templates {
	private $content;

	function Headeri($title) {
		echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Motulus - $title</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" type="text/css" href="styles/screen.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="styles/login-style.css" media="screen" />
</head>
 
<body> 

EOF;
	}

	function Navigation($currentMenu, $currentUser) {
		if($currentUser['kayttajaTaso'] != 0) {
			$hallinta = "";
			if($currentUser['kayttajaTaso'] == 4) {
				$hallinta = '<a href="index.php?op=hallinta" title="">Hallinta</a>';
			}
			echo <<<EOF
<div id="header">
	<div id="header-menu">
		<img src="logo.png" alt="Motulus" />
		<a href="index.html" title="">Etusivu</a>
		<a href="hops.html" title="">HOPS</a>
		<a href="ilmoittautuminen.html" title="ilmoittautuminen">ilmoittaudu</a>
		<a href="asetukset.html" title="">Asetukset</a>
		$hallinta
		<a href="index.php?op=logout" title="">ULOS</a>
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
			}elseif($currentMenu == "CP") {
				echo $this->NavigationCP();
			}

			echo <<<EOF
	</div>
</div> 
EOF;
		}
	}

	function NavigationCP() {
		return <<<EOF
		<div class="col1">
			<a href="index.php?op=hallinta&subop=kayttajat" title="">Käyttäjät</a><br/>
			<a href="index.php?op=hallinta&subop=toteutukset" title="">Toteutukset</a><br/>
			<a href="index.php?op=hallinta&subop=kurssit" title="">Kurssit</a><br/>
			<a href="index.php?op=hallinta&subop=moduulit" title="">Moduulit</a><br/>
			<a href="index.php?op=hallinta&subop=koulutusohjelmat" title="">Koulutusohjelmat</a><br/>
			<a href="index.php?op=hallinta&subop=toimipisteet" title="">Toimipisteet</a><br/>
		</div>
EOF;
	}

	function Content() {
		echo $this->content;
	}

	function Footer() {
		echo <<<EOF
<div id="footer">
	<p>&copy; codename Motulus 2011</p>
</div>
</body>
</html> 
EOF;
	}

	function PrintPage($title, $currentMenu, $currentUser) {
		$this->Headeri($title);
		$this->Navigation($currentMenu, $currentUser);
		$this->Content();
		$this->Footer();
	}

	function Login() {
		$this->content .= <<<EOF
<div id="main">
		<img src="images/logo.png" id="logo" />
		<form name="input" action="index.php?op=login" method="POST">
			<input type="hidden" name="tryLogin" value="ofc" />
			<div class="input-bg">
				<input type="text" size="39" name="username" value="K&auml;ytt&auml;j&auml;tunnus" />
			</div>
			<div class="input-bg">
				<input type="text" size="39" name="password" value="Salasana" />
			</div>

			<div id="submit-bg">
				<input type="submit" value="">
			</div>
		</div>
	</div>
EOF;
	}

	function InsertContent($content) {
		$this->content .= <<<EOF
$content
EOF;
	}

}
