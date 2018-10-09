<?php
class Structure {
	private $db;
	private $structure_id;
	private $name;
	function __construct() {
		global $db;
		$this->db = $db;
		debug("Loaded");
	}
	public function setStructure($structure_id) {
		debug("Structure set to " . $structure_id);
		$this->structure_id = $structure_id;
		$this->process();
	}
	public function getName() {
		return $this->name;
	}
	private function process() {
		$data = $this->db->query("SELECT name FROM structures WHERE id='?' LIMIT 1", $this->structure_id);
		if(!$data) {
			$crest = new Crest();
			$crest->setUri("/universe/structures/" . $this->structure_id . "/");
			$data = $crest->doRequest();
			$this->name = $data['name'];
			$this->db->query("INSERT INTO structures (id, name) VALUES ('?', '?')", $this->structure_id, $this->name);
		} else {
			$this->name = $data[0]->name;
		}

	}
}
?>