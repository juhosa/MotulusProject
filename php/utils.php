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
			$this->query = $this->connection->prepare("SELECT LD.password, LD.salt, H.etunimi, H.sukunimi, H.kayttajaTaso FROM motulus.LoginData as LD JOIN motulus.Henkilo as H ON LD.id = H.henkiloId WHERE LD.id = ?");
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

	//////////////////////////////////////////////////
	//	CP:n tarvitsemat kyselyt
	/////////////////////////////////////////////////
	function sql_query_SelectUsers() {
		try {
			$this->query = $this->connection->prepare("SELECT henkiloId, etunimi, sukunimi, puhelin, kayttajaTaso, email, toimipisteId  FROM motulus.Henkilo ORDER BY sukunimi, etunimi");
			$this->query->execute();
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}

	function sql_query_SelectUser($id) {
		try {
			$this->query = $this->connection->prepare("SELECT henkiloId, etunimi, sukunimi, puhelin, kayttajaTaso, email, toimipisteId  FROM motulus.Henkilo WHERE henkiloId = ?");
			$this->query->execute(array($id));
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}

	function sql_query_UpdateUser($values) {
		try {
			$this->query = $this->connection->prepare("UPDATE motulus.Henkilo SET etunimi = ?, sukunimi = ?, puhelin = ?, kayttajaTaso = ?, email = ?, toimipisteId = ? WHERE etunimi = ? AND sukunimi = ? AND puhelin = ? AND kayttajaTaso = ? AND email = ? AND toimipisteId = ? AND henkiloId = ?");
			$this->query->execute($values);
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}

	function sql_query_AddUser($values1, $values2) {
		try {
			$this->query = $this->connection->prepare("SELECT MAX(henkiloId) + 1 as ID FROM motulus.Henkilo");
			$this->query->execute();
		} catch (PDOException $e) {
			die("VIRHE1: " . $e->getMessage());
		}
		$row = $this->sql_NextRow();
		array_unshift($values1, $row['ID']);
		array_unshift($values2, $row['ID']);
		try {
			$this->query = $this->connection->prepare("INSERT INTO motulus.Henkilo ( henkiloId, etunimi, sukunimi, puhelin, kayttajaTaso, email, toimipisteId) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$this->query->execute($values1);
		} catch (PDOException $e) {
			die("VIRHE2: " . $e->getMessage());
		}

		try {
			$this->query = $this->connection->prepare("INSERT INTO motulus.LoginData ( id, password, salt ) VALUES (?, ?, ?)");
			$this->query->execute($values2);
		} catch (PDOException $e) {
			die("VIRHE3: " . $e->getMessage());
		}

		return $row['ID'];
		
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
		$this->CurrentUser['etunimi'] = null;
		$this->CurrentUser['sukunimi'] = null;
		$this->CurrentUser['nimi'] = null;
		$this->CurrentUser['kayttajaTaso'] = 0;
		if(isset($_SESSION['user_id'], $_SESSION['login_magic'])) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_magic'];
			$ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
			$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
			$this->sql_query_SelectLoginPasswordSalt($user_id);
			$row = $this->sql_NextRow();
			if($row["password"]) {
				if(hash('sha512', $row["password"].$ip_address.$user_browser) == $login_string) {
					$this->CurrentUser['etunimi'] = $row['etunimi'];
					$this->CurrentUser['sukunimi'] = $row['sukunimi'];
					$this->CurrentUser['nimi'] = $row['etunimi'] . " " . $row['sukunimi'];
					$this->CurrentUser['kayttajaTaso'] = $row['kayttajaTaso'];
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
