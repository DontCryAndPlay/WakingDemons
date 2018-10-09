<?php
class SolarSystem {
	private $db;
	private $solar_system_id;
	private $name, $security_status;
	function __construct() {
		global $db;
		$this->db = $db;
		debug("Loaded");
	}
	public function setSolarSystem($solar_system_id) {
		debug("Solar system set to " . $solar_system_id);
		$this->solar_system_id = $solar_system_id;
		$this->process();
	}
	public function getName() {
		return $this->name;
	}
	private function process() {
		$data = $this->db->query("SELECT name, security_status FROM solarsystems WHERE id='?' LIMIT 1", $this->solar_system_id);
		if(!$data) {
			$crest = new Crest();
			$crest->setUri("/universe/systems/" . $this->solar_system_id . "/");
			$data = $crest->doRequest();
			$this->name = $data['name'];
			$this->security_status = $data['security_status'];
			$this->db->query("INSERT INTO solarsystems (id, name, security_status) VALUES ('?', '?', ?)", $this->solar_system_id, $this->name, $this->security_status);
		} else {
			$this->name = $data[0]->name;
			$this->security_status = $data[0]->security_status;
		}

	}
}
?>