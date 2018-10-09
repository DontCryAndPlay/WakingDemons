<?php
class Mail {
	private $crest;
	private $crestFailure = false;
	private $newMail = false;
	private $db;
	function __construct() {
		global $crest, $db;
		$this->crest = $crest;
		$this->db = $db;
	}
	public function process() {
		if(isset($_SESSION['crest']['mail']['cooldown']) && time() < $_SESSION['crest']['mail']['cooldown'])
			return true;

		$this->processLabels();

		$this->crest->setUri("/characters/" . $_SESSION['uid'] . "/mail/");
		$data = $this->crest->doRequest();
		$_SESSION['crest']['mails']['cooldown'] = time() + 30;
		if(!$data || $data == "") {
			$this->crestFailure = true;
			return false;
		}

		foreach($data as $k=>$v) {
			if(!is_array($v['labels']) || count($v['labels']) == 0)
				$v['labels'] = array(1);
			$_SESSION['crest']['mail'][$v['mail_id']]['subject'] = $v['subject'];
			$_SESSION['crest']['mail'][$v['mail_id']]['timestamp'] = date_format(date_create($v['timestamp']), "d/m/Y H:i:s");
			$_SESSION['crest']['mail'][$v['mail_id']]['is_read'] = $v['is_read'];
			$_SESSION['crest']['mail'][$v['mail_id']]['labels'] = $v['labels'];

			$dbdata = $this->db->query("SELECT count(*) as n FROM evemails WHERE mail_id='?' LIMIT 1", $v['mail_id']);
			if($dbdata[0]->n > 0) {
				$dbdata2 = $this->db->query("SELECT name, body, `timestamp`  FROM evemails LEFT JOIN userInformation on(userInformation.id = evemails.from) WHERE mail_id='?' LIMIT 1", $v['mail_id']);
				$_SESSION['crest']['mail'][$v['mail_id']]['from'] = $dbdata2[0]->name;
				$_SESSION['crest']['mail'][$v['mail_id']]['body'] = $dbdata2[0]->body;
				$_SESSION['crest']['mail'][$v['mail_id']]['timestamp'] = date_format(date_create($dbdata2[0]->timestamp), "d/m/Y H:i:s");
				continue;
			}

			if(!$this->newMail && $v['labels'][0] != 2)
				$this->newMail = true;

			$dbdata = $this->db->query("SELECT name FROM userInformation WHERE id='?'", $v['from']);
			if(!$dbdata || !isset($dbdata[0]->name) || $dbdata[0]->name == "") {
				$this->crest->setUri("/characters/" . $v['from'] . "/");
				$data2 = $this->crest->doRequest();
				if(isset($data2['name'])) {
					$from = $data2['name'];
					$this->db->query("INSERT INTO userInformation(id, name) VALUES ('?','?')", $v['from'], $from);
					$_SESSION['crest']['mail'][$v['mail_id']]['from'] = $from;
				}
				else
					$v['from'] = 0;
			}
			$this->crest->setUri("/characters/" . $_SESSION['uid'] . "/mail/" . $v['mail_id'] . "/");
			$data2 = null;//$this->crest->doRequest();
			if(!$data2 || $data2 == "") {
				$this->crestFailure = true;
				$body = null;
			} else {
				$body = $data2['body'];
			}

			$_SESSION['crest']['mail'][$v['mail_id']]['body'] = $body;
			$this->db->query("INSERT INTO evemails (mail_id, `from`, `timestamp`, subject, body, is_read) VALUES ('?','?','?','?','?','?')", $v['mail_id'], $v['from'], $v['timestamp'], $v['subject'], $body, $v['is_read']? 1:0);

			$dbdata = $this->db->query("SELECT count(*) as n FROM evemails_recipients WHERE mail_id='?' LIMIT 1", $v['mail_id']);
			if($dbdata[0]->n == 0) {
				$insert = array();
				foreach($v['recipients'] as $x)
					$insert[] = "('" . $v['mail_id'] . "', '" . $x['recipient_id'] . "', '" . $x['recipient_type'] . "')";
				$insert = implode(",", $insert);
				$this->db->query("INSERT INTO evemails_recipients (mail_id, recipient_id, recipient_type) VALUES " . $insert);
			}
			foreach($v['labels'] as $x) {
				$dbdata = $this->db->query("SELECT count(*) as n FROM evemails_labels WHERE mail_id='?' AND label_id='?'", $v['mail_id'], $x);
				if($dbdata[0]->n == 0)
					$this->db->query("INSERT INTO evemails_labels(mail_id, label_id) VALUES ('?','?')", $v['mail_id'], $x);
			}
		}
		krsort($_SESSION['crest']['mail']); //Sort emails...
		return true;
	}
	private function processLabels() {
		$this->crest->setUri("/characters/" . $_SESSION['uid'] . "/mail/labels/");
		$data = $this->crest->doRequest();
		if(!$data || $data == "") {
			$this->crestFailure = true;
			return false;
		}

		foreach($data['labels'] as $k=>$v) {

			if(!is_array($v)) continue;

			$dbdata = $this->db->query("SELECT * FROM evemails_label_data WHERE uid='?' AND label_id='?'", $_SESSION['uid'], $v['label_id']);


			if(!isset($v['unread_count']))
				$v['unread_count'] = 0;

			if(count($dbdata) == 0 || ($dbdata[0]->name != $v['name'] && $dbdata[0]->unread_count != $v['unread_count'] && $dbdata[0]->color != $v['color']))

				$this->db->query("INSERT INTO evemails_label_data (uid, label_id, name, unread_count, color) VALUES ('?','?','?','?','?') ON DUPLICATE KEY update name=VALUES(name), unread_count=VALUES(unread_count), color=VALUES(color)", $_SESSION['uid'], $v['label_id'], $v['name'], $v['unread_count'], $v['color']);

			$_SESSION['crest']['mail_labels'][$v['label_id']]['name'] = $v['name'];
			$_SESSION['crest']['mail_labels'][$v['label_id']]['color'] = $v['color'];
			$_SESSION['crest']['mail_labels'][$v['label_id']]['unread_count'] = $v['unread_count'];
		}
	}
	public function hasNewMails() {
		return $this->newMail;
	}
}