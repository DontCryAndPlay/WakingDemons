<?php
class ship {
	private $db;
	private $ship;
	private $name, $graphic_id;
	function __construct() {
		global $db;
		$this->db = $db;
		debug("Loaded");
	}
	public function setShip($ship) {
		debug("Ship set to " . $ship);
		$this->ship = $ship;
		$this->process();
	}
	public function getName() {
		return $this->name;
	}
	private function process() {
		$data = $this->db->query("SELECT * FROM ships WHERE id='?' LIMIT 1", $this->ship);
		if(!$data) {
			$crest = new Crest();
			$crest->setUri("/universe/types/" . $this->ship . "/");
			$data = $crest->doRequest();
			$this->name = $data['name'];
			$this->graphic_id = $data['graphic_id'];
			$this->db->query("INSERT INTO ships (id, name, graphic_id) VALUES ('?', '?', '?')", $this->ship, $this->name, $this->graphic_id);
		} else {
			$this->name = $data[0]->name;
			$this->graphic_id = $data[0]->graphic_id;
		}

	}
}
?>