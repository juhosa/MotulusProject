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
				case 'hallinta':
					$this->PageCP();
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
		$this->template->PrintPage("Login", "notLoggedIn", $this->utils->CurrentUser);
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
					$this->PageLogin();
				}else {
					if($password == $row["password"]) {
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						$_SESSION['user_id'] = $username;
						$_SESSION['login_magic'] = hash('sha512', $password.$ip_address.$user_browser);
						
						header("location: ./");
					}else {

						$this->utils->sql_query_InsertFailedAttempt($username, $ip_address);
						$this->PageLogin();
					}
				}
			}else{
				$this->PageLogin();
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
		$this->template->PrintPage("Etusivu", "", $this->utils->CurrentUser);
	}

	function ActionLogout() {
		$this->utils->Login_Logout();
		header("location: ./");
	}

	// Hallinta. Ainoastaan toimistotädeille
	function PageCP() {
		if($this->utils->CurrentUser['kayttajaTaso'] == 4) {
			$content = "";
			switch($this->utils->input('subop')) {
				case 'kayttajat':
					$content = $this->PageCPkayttajat();
					break;
				case 'MuokkaaKayttajaa':
					$content = $this->PageCPkayttajaMuokkaus();
					break;
				case 'LisaaKayttaja':
					$content = $this->PageCPkayttajaLisaa();
					break;
				case 'toteutukset':
					$content = $this->PageCPtoteutukset();
					break;
				case 'kurssit':
					$content = $this->PageCPkurssit();
					break;
				case 'moduulit':
					$content = $this->PageCPmoduulit();
					break;
				case 'koulutusohjelmat':
					$content = $this->PageCPkoulutusohjelmat();
					break;
				case 'toimipisteet':
					$content = $this->PageCPtoimipisteet();
					break;
				default:
					$content = "";
					break;
			}
			$this->template->InsertContent($content);
			$this->template->PrintPage("Hallinta", "CP", $this->utils->CurrentUser);
		}else{
			header("location: ./");
		}
	}

	function PageCPkayttajat() {
		$content = "";
		$this->utils->sql_query_SelectUsers();
		while($row = $this->utils->sql_NextRow()) {
			$tyyppi = "Oppilas";
			if($row['kayttajaTaso'] == 2) { $tyyppi = "Opettaja"; }
			else if ($row['kayttajaTaso'] == 3) { $tyyppi = "Koulutusalavastaava"; }
			else if ($row['kayttajaTaso'] == 4) { $tyyppi = "Toimistotäti"; }
			$content .= "
			<tr>
				<td>".$row['sukunimi']."</td>
				<td>".$row['etunimi']."</td>
				<td>".$row['email']."</td>
				<td>".$row['puhelin']."</td>
				<td>".$tyyppi."</td>
				<td><a href='index.php?op=hallinta&subop=MuokkaaKayttajaa&id=".$row['henkiloId']."' title=''>Muokkaa</a></td>
			</tr>";
		}
			$content = <<<EOF
		<table>
$content
		</table>
		<a href='index.php?op=hallinta&subop=LisaaKayttaja' title=''>Lisaa uusi</a>
EOF;
			return $content;
	}

	function PageCPkayttajaMuokkaus() {
		$content = "";
		$userid = intval($this->utils->input('id'));
		if($this->utils->input('save', 'post') == "yes" && $userid > 0) {
			$this->utils->sql_query_SelectUser($userid);
			$row = $this->utils->sql_NextRow();
			$valuesForSql[] = $this->utils->input('etunimi', 'post');
			$valuesForSql[] = $this->utils->input('sukunimi', 'post');
			$valuesForSql[] = $this->utils->input('puhelin', 'post');
			$valuesForSql[] = $this->utils->input('kayttajaTaso', 'post');
			$valuesForSql[] = $this->utils->input('email', 'post');
			$valuesForSql[] = $this->utils->input('toimipisteId', 'post');
			$valuesForSql[] = $row['etunimi'];
			$valuesForSql[] = $row['sukunimi'];
			$valuesForSql[] = $row['puhelin'];
			$valuesForSql[] = $row['kayttajaTaso'];
			$valuesForSql[] = $row['email'];
			$valuesForSql[] = $row['toimipisteId'];
			$valuesForSql[] = $userid;

			$this->utils->sql_query_UpdateUser($valuesForSql);
			$content = "TALLENNETTU!!!<br/>";
		}
		if($userid > 0) {
			$this->utils->sql_query_SelectUser($userid);
			$row = $this->utils->sql_NextRow();
			$content .= '<form name="input" action="index.php?op=hallinta&subop=MuokkaaKayttajaa&id='.$userid.'" method="POST"><table>';
			$content .= '<tr><td>Etunimi</td><td><input type="text" value="'.$row['etunimi'].'" name="etunimi" /></td></tr>';
			$content .= '<tr><td>Sukunimi</td><td><input type="text" value="'.$row['sukunimi'].'" name="sukunimi" /></td></tr>';
			$content .= '<tr><td>email</td><td><input type="text" value="'.$row['email'].'" name="email" /></td></tr>';
			$content .= '<tr><td>puhelin</td><td><input type="text" value="'.$row['puhelin'].'" name="puhelin" /></td></tr>';
			$content .= '<tr><td>kayttajaTaso</td><td><input type="text" value="'.$row['kayttajaTaso'].'" name="kayttajaTaso" /></td></tr>';
			$content .= '<tr><td>toimipisteId</td><td><input type="text" value="'.$row['toimipisteId'].'" name="toimipisteId" /></td></tr>';
			$content .= '<tr><td colspan="2"><input type="hidden" name="save" value="yes"/><input type="submit" value="TALLENNA" /></td></tr>';
			$content .= '</table></form>';
		}
		return $content;
	}

	function PageCPkayttajaLisaa() {
		$content = "";
		if($this->utils->input('save', 'post') == "yes") {
			$password = $this->utils->input('salasana', 'post'); 
			$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			// Create salted password (Careful not to over season)
			$password = hash('sha512', $password.$random_salt);

			$henkiloValues[] = $this->utils->input('etunimi', 'post');
			$henkiloValues[] = $this->utils->input('sukunimi', 'post');
			$henkiloValues[] = $this->utils->input('puhelin', 'post');
			$henkiloValues[] = $this->utils->input('kayttajaTaso', 'post');
			$henkiloValues[] = $this->utils->input('email', 'post');
			$henkiloValues[] = $this->utils->input('toimipisteId', 'post');
			$loginValues[] = $password;
			$loginValues[] = $random_salt;

			$newID = $this->utils->sql_query_AddUser($henkiloValues, $loginValues);
			$loc = "Location: index.php?op=hallinta&subop=MuokkaaKayttajaa&id=".$newID;
			header($loc);
		}else{
			$this->utils->sql_query_SelectUser($userid);
			$row = $this->utils->sql_NextRow();
			$content .= '<form name="input" action="index.php?op=hallinta&subop=LisaaKayttaja" method="POST"><table>';
			$content .= '<tr><td>Etunimi</td><td><input type="text" value="" name="etunimi" /></td></tr>';
			$content .= '<tr><td>Sukunimi</td><td><input type="text" value="" name="sukunimi" /></td></tr>';
			$content .= '<tr><td>email</td><td><input type="text" value="" name="email" /></td></tr>';
			$content .= '<tr><td>puhelin</td><td><input type="text" value="" name="puhelin" /></td></tr>';
			$content .= '<tr><td>kayttajaTaso</td><td><input type="text" value="" name="kayttajaTaso" /></td></tr>';
			$content .= '<tr><td>toimipisteId</td><td><input type="text" value="" name="toimipisteId" /></td></tr>';
			$content .= '<tr><td>Salasana</td><td><input type="text" value="" name="salasana" /></td></tr>';
			$content .= '<tr><td colspan="2"><input type="hidden" name="save" value="yes"/><input type="submit" value="TALLENNA" /></td></tr>';
			$content .= '</table></form>';
		}
		return $content;
	}
}

new Motulus();

?>
