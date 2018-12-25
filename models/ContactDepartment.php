<?php
	class ContactDepartment extends BasicModel {
		protected $college_id;
		public $title;
		public $contact;
		public $priority;

		static public function GET_ORDERED_LIST(ContactCollege $contactCollege){
			$query = B::DB()->prepare("SELECT id FROM contact_department WHERE college_id = :g ORDER BY priority, id DESC");
			$query->execute([
				':g' => $contactCollege->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}

		public function save(){
			$query = B::DB()->prepare("INSERT INTO contact_department (college_id, title, contact) VALUE (:c, :t, :o)");
			$query->execute([
				':c' => $this->college_id,
				':t' => $this->title,
				':o' => $this->contact
			]);
		}

		public function setCollegeID(ContactCollege $e){
			$this->college_id = $e->id;
		}
	}