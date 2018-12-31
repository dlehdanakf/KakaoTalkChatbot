<?php
	class Member extends BasicModel {
		public $user_key;
		public $username;
		public $phone;

		public function save(){
			if($this->isDuplicatedKey())
				$this->update();

			$this->insert();
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT id FROM member WHERE user_key = :k");
			$query->execute([
				':k' => $this->user_key
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
		protected function insert(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO member (user_key, username, phone) VALUE (:k, :n, :p)");
			$query->execute([
				':k' => $this->user_key,
				':n' => $this->username,
				':p' => $this->phone
			]);

			$this->id = $pdo->lastInsertId();
		}
		protected function update(){
			$query = B::DB()->prepare("UPDATE member SET username = :n, phone = :p WHERE user_key = :k");
			$query->execute([
				':k' => $this->user_key,
				':n' => $this->username,
				':p' => $this->phone
			]);
		}
	}