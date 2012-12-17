<?php
/*
+--------------------------------------------------------------------------
|
|	OMG! IT is PORN ! Just Joking. Kaikki apu funktiot yms tännepäin.
|
+--------------------------------------------------------------------------
*/

class utils {
	// Alustetaan luokan tarvitsemat muuttujat
	private $host;
	private $dbname;
	private $username;
	private $password;
	private $connection;
	private $query;
	public $CurrentUser;
	public $loggedIn;


	function __construct() {
		$this->host = "localhost";
		$this->dbname = "Motulus";
		$this->username = "Motulus";
		$this->password = "tt091337";
		$this->loggedIn = false;
	}

	// Pyrkii avaamaan ja alustamaan yhteyden.
	function sql_OpenConnection() {
		try {
		    $this->connection = new PDO("sqlsrv:server=".$this->host."; Database=".$this->dbname, $this->username, $this->password);
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}

		// Muodostetaan poikkeus jos tapahtuu virhe
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
	}

	function sql_NextRow() {
		if($this->query) {
			return $this->query->fetch();
		}
	}

	function sql_AllRows() {
		if($this->query) {
			return $this->query->fetchAll();
		}
	}

	function sql_AffectedRows() {
		if($this->query) {
			return $this->query->rowCount();
		}
	}

	function sql_LastInsertedId() {
		return $this->query->lastInsertId();
	}


	function sql_Kysely1($esimekkimuuttuja) {
		try {
			$this->query = $this->connection->prepare("SELECT * FROM tuotteet WHERE hinta = ?");
			$this->query->execute(array($esimekkimuuttuja));
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}
	
	//////////////////////////////////////////////////
	//	Loginin tarvitsemat kyselyt
	/////////////////////////////////////////////////
	function sql_query_SelectLoginPasswordSalt($id) {
		try {
			$this->query = $this->connection->prepare("SELECT password, salt FROM motulus.LoginData WHERE id = ?");
			$this->query->execute(array($id));
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}

	function sql_query_InsertFailedAttempt($id, $ip) {
		$now = time();
		try {
			$this->query = $this->connection->prepare("INSERT INTO motulus.LoginAttemps (userid, time, ip) VALUES (?, ?, ?)");
			$this->query->execute(array($id, $now, $ip));
		} catch (PDOException $e) {
			die("VIRHE: " . $e->getMessage());
		}
	}

	function sql_query_SelectFailedAttempts($ip) {
		$now = time();
		$valid_attempts = $now - (15 * 60); // 15min 
		try {
			$this->query = $this->connection->prepare("SELECT COUNT(*) as times FROM motulus.loginAttemps WHERE ip = ? AND time < ? AND time > ?");
			$this->query->execute(array($ip, $now, $valid_attempts));
		} catch (PDOException $e) {
			die("VIRHE: " . $e->getMessage());
		}
	}

	function input($name, $type = "get") {
		if($type == "get") {
			return $_GET[$name];
		}else {
			return $_POST[$name];
		}
	}

	// Tarkastaa onko käyttäjä kirjautunut ja asettaa käyttäjän tiedot johonkin muuttujaan.
	function GetUserdata() {
		if(isset($_SESSION['user_id'], $_SESSION['login_magic'])) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_magic'];
			$ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
			$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
			$this->sql_query_SelectLoginPasswordSalt($user_id);
			$row = $this->sql_NextRow();
			if($row["password"]) {
				if(hash('sha512', $row["password"].$ip_address.$user_browser) == $login_string) {
					$this->CurrentUser = $user_id;
					$this->loggedIn = true;
				}
			}
		}
	}

	function Login_StartSession() {
		$session_name = 'MotulusLoginSession'; // Set a custom session name
        $secure = false; // Set to true if using https.
        $httponly = true; // This stops javascript being able to access the session id. 
		$cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
        session_name($session_name); // Sets the session name to the one set above.
        session_start(); // Start the php session
        session_regenerate_id(true); // regenerated the session, delete the old one.
	}

	function Login_Logout() {
		// Unset all session values
		$_SESSION = array();
		// get session parameters 
		$params = session_get_cookie_params();
		// Delete the actual cookie.
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		// Destroy session
		session_destroy();
	}

}
