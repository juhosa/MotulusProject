<?php
/*
+--------------------------------------------------------------------------
|
|  OMG! IT is PORN !
|
+--------------------------------------------------------------------------
*/

class motulus_utils {
	// Alustetaan luokan tarvitsemat muuttujat
	private $host;
	private $dbname;
	private $username;
	private $password;
	private $connection;
	private $query;


	function motulus_utils() {
		private $host = "";
		private $dbname = "";
		private $username = "";
		private $password = "";
	}

	// Pyrkii avaamaan ja alustamaan yhteyden.
	function sql_OpenConnection() {
		try {
		    $connection = new PDO("mysql:host=".$host.";dbname=".$dbname, $username, $password);
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}

		// Muodostetaan poikkeus jos tapahtuu virhe
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Merkistöt kuntoon
		$connection->exec("SET NAMES latin1");
	}

	function sql_NextRow() {
		if($query) {
			return $query->fetch();
		}
	}

	function sql_AllRows() {
		if($query) {
			return $query->fetchAll();
		}
	}

	function sql_AffectedRows() {
		if($query) {
			return $query->rowCount();
		}
	}

	function sql_RowCount() {
		return count($query->fetchAll());
	}

	
	function sql_Kysely1($esimekkimuuttuja) {
		try {
			$query = $connection->prepare("SELECT * FROM tuotteet WHERE hinta = ?");
			$query->execute(array(3));
		} catch (PDOException $e) {
		    die("VIRHE: " . $e->getMessage());
		}
	}

}
