<?php
	class ApplicationSetting extends BasicModel {
		public $sys_key;
		public $sys_value;

		public function save() {
			if($this->isDuplicatedKey())
				$this->update();

			$this->insert();
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT id FROM application_setting WHERE sys_key = :k");
			$query->execute([
				':k' => $this->sys_key
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
		protected function insert(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO application_setting (sys_key, sys_value) VALUE (:k, :v)");
			$query->execute([
				':k' => $this->sys_key,
				':v' => $this->sys_value
			]);

			$this->id = $pdo->lastInsertId();
		}
		protected function update(){
			$query = B::DB()->prepare("UPDATE application_setting SET sys_value = :v WHERE sys_key = :k");
			$query->execute([
				':k' => $this->sys_key,
				':v' => $this->sys_value
			]);
		}
	}