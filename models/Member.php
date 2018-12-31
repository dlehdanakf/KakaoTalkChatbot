<?php
	class Member extends BasicModel {
		public $user_key;
		public $username;
		public $phone;
		public $seed;

		public function save(){
			if($this->isDuplicatedKey())
				$this->update();

			$this->insert();
		}
		public function generateSeed(){
			$query = B::DB()->prepare("UPDATE member SET seed = :s WHERE user_key = :k");
			$query->execute([
				':s' => (rand() * 100) % 100
			]);
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
			$query = $pdo->prepare("INSERT INTO member (user_key, username, phone, seed) VALUE (:k, :n, :p, :s)");
			$query->execute([
				':k' => $this->user_key,
				':n' => $this->username,
				':p' => $this->phone,
				':s' => (rand() * 100) % 100
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