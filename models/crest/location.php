<?php
class Location {
	private $crest;
	private $crestFailure = false;
	private $newLocation = false;
	private $newShip = false;
	function __construct() {
		global $crest;
		$this->crest = $crest;
	}
	public function process() {
		if(isset($_SESSION['crest']['location']['cooldown']) && time() < $_SESSION['crest']['location']['cooldown']) {
			return true;
		}

		$oldData = isset($_SESSION['crest']['location']) ? $_SESSION['crest']['location'] : null;

		unset($_SESSION['crest']['location']);

		$_SESSION['crest']['location']['cooldown'] = time() + 5;

		$this->crest->setUri("/characters/" . $_SESSION['uid'] . "/location/");
		$data = $this->crest->doRequest();

		if($data == "") {
			$this->crestFailure = true;
			return false;
		}

		foreach($data as $k=>$v)
			$_SESSION['crest']['location'][$k] = $v;


		if(isset($oldData['solar_system_id']) && $oldData['solar_system_id'] == $_SESSION['crest']['location']['solar_system_id']) { //no changes
			$_SESSION['crest']['location']['solar_system_name'] = $oldData['solar_system_name'];
		} else if(isset($_SESSION['crest']['location']['solar_system_id']) && $_SESSION['crest']['location']['solar_system_id'] != "") { 
			$this->newLocation = true;
			$solarsystem = new Model("ucp/SolarSystem");
			$solarsystem->setSolarSystem($_SESSION['crest']['location']['solar_system_id']);
			$_SESSION['crest']['location']['solar_system_name'] = $solarsystem->getName();
		}
	
		if(isset($_SESSION['crest']['location']['structure_id'])) {

			if(isset($oldData['structure_id']) && $_SESSION['crest']['location']['structure_id'] == $oldData['structure_id']) { //no changes
				$_SESSION['crest']['location']['structure_name'] = $oldData['structure_name'];
			} else {
				$structure = new Model("ucp/Structure");
				$structure->setStructure($_SESSION['crest']['location']['structure_id']);
				$_SESSION['crest']['location']['structure_name'] = $structure->getName();
			}
		} else if (isset($_SESSION['crest']['location']['station_id'])) {
			//TODO: station code goes here...
		} else
			$_SESSION['crest']['location']['structure_name'] = "<i>In spaaaaaace!</i>"; // TODO: move "in space" message to translations...

		if(isset($oldData['structure_name']) && $oldData['structure_name'] != $_SESSION['crest']['location']['structure_name'])
		$this->newLocation = true;

		$this->crest->setUri("/characters/" . $_SESSION['uid'] . "/ship/");
		$data = $this->crest->doRequest();
	
		if(!isset($data['ship_type_id'])) {
			$this->crestFailure = true;
			return false;
		}

		foreach($data as $k=>$v)
			$_SESSION['crest']['location']['ship'][$k] = $v;

		if(isset($oldData['ship']['ship_type_id']) && $oldData['ship']['ship_type_id'] == $_SESSION['crest']['location']['ship']['ship_type_id']) // no changes
			$_SESSION['crest']['location']['ship']['ship_type_name'] = $oldData['ship']['ship_type_name'];
		else {
			$ship = new Model("ucp/Ship");
			$ship->setShip($data['ship_type_id']);
			$_SESSION['crest']['location']['ship']['ship_type_name'] = $ship->getName();
			$this->newShip = true;
		}
		return true;
	}
	public function hasNewLocation() {
		return $this->newLocation;
	}
	public function hasNewShip() {
		return $this->newShip;
	}
	public function isCrestFailure() {
		return $this->crestFailure;
	}
}