<?php
/*
+--------------------------------------------------------------------------
|
|  Tänne tulee suurinosa koodista
|
+--------------------------------------------------------------------------
*/

class Motulus {
	private $utils;

	function Motulus() {
		require "utils.php";
		$this->utils = new utils();
		$this->utils->GetUserdate();

		if($this->utils->CurrentUser == null) {
			$this->PageLogin();
		}else{
			switch($utils->input['op']) {
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
}

new Motulus();
