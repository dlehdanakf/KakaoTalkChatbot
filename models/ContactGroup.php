<?php
	class ContactGroup extends BasicModel {
		public $id;
		public $title;
		public $description;
		public $priority;
		public $register_date;

		static public function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM contact_group ORDER BY priority, id DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO contact_group (title, description) VALUE (:t, :d)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function getAllColleges(){
			return ContactCollege::GET_ORDERED_LIST($this);
		}
	}