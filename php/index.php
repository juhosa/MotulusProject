<?php
date_default_timezone_set('Europe/Helsinki');
/*
+--------------------------------------------------------------------------
|
|	Tänne lähes kaikki koodi
|
+--------------------------------------------------------------------------
*/

class Motulus {
	private $utils;
	private $template;

	function __construct() {
		// Sisällytetään utils. Avataan yhteydet ja otetaan käyttäjän tiedot
		require "utils.php";
		$this->utils = new utils();
		$this->utils->Login_StartSession();
		$this->utils->SQL_OpenConnection();
		$this->utils->GetUserdata();

		// Sisällytetään templatet
		require "templates.php";
		$this->template = new templates(); 
		
		// Käsitellään käyttäjät, jotka eivät ole kirjautuneina
		if($this->utils->loggedIn == false) {
			if($this->utils->input('tryLogin', 'post')) {
				$this->ActionLoginHandler();
			}else{
				$this->PageLogin();
			}
		}
		// Heitellään kirjautuneet oikeisiin paikkoihin
		else{
			switch($this->utils->input('op')) {
				case 'hops':
					$this->PageHops();
					break;
				case 'logout':
					$this->ActionLogout();
					break;
				default:
					$this->PageFrontpage();
					break;
			}
		}
	}

	function PageLogin() {
		$this->template->Login();
		$this->template->PrintPage("Login", "notLoggedIn");
	}

	function ActionLoginHandler() {
		$username = intval($this->utils->input('username', 'post'));
		$password = $this->utils->input('password', 'post');
		if($username && $password) {
			// Haetaan salasana ja suola kannasta
			$this->utils->sql_query_SelectLoginPasswordSalt($username);
			$row = $this->utils->sql_NextRow();

			// Tarkastetaan, että löydettiin käyttäjä kannasta
			if($row["password"] && $row["salt"]) {
				$password = hash('sha512', $password.$row["salt"]);
				$ip_address = $_SERVER['REMOTE_ADDR']; 
				if($this->ActionLoginCheckBrute($ip_address)) {
					// Locked
				}else {
					if($password == $row["password"]) {
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						$_SESSION['user_id'] = $username;
						$_SESSION['login_magic'] = hash('sha512', $password.$ip_address.$user_browser);
						
						$this->PageFrontpage();
					}else {

						$this->utils->sql_query_InsertFailedAttempt($username, $ip_address);
						// Väärä salsasana
					}
				}
			}else{
				// Väärä username.
			}

		}else{
			$this->PageLogin();
		}
	}

	function ActionLoginCheckBrute($ip) {
		$this->utils->sql_query_SelectFailedAttempts($ip);
		$row = $this->utils->sql_NextRow();
		if($row['times'] < 3) {
			return false;
		}
		return true;
	}

	function PageFrontpage() {
		$this->template->InsertContent("Sisällä ollaan");
		$this->template->PrintPage("Etusivu", "");
	}

	function ActionLogout() {
		$this->utils->Login_Logout();
		header("location: ./");
	}
}

new Motulus();

?>
