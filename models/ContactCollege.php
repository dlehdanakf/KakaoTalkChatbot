<?php
	class ContactCollege extends BasicModel {
		public $id;
		protected $group_id;
		public $title;
		public $priority;
		public $register_date;

		static public function GET_ORDERED_LIST(ContactGroup $contactGroup){
			$query = B::DB()->prepare("SELECT id FROM contact_college WHERE group_id = :g ORDER BY priority, id DESC");
			$query->execute([
				':g' => $contactGroup->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		static public function CREATE_BY_TITLE($title){
			$query = B::DB()->prepare("SELECT * FROM contact_college WHERE title = :t");
			$query->execute([
				':t' => $title
			]);

			$instance = new self;
			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($instance) . " 객체를 찾을 수 없습니다. title - " . $title);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function save(){
			$query = B::DB()->prepare("INSERT INTO contact_college (group_id, title) VALUE (:g, :t)");
			$query->execute([
				':g' => $this->group_id,
				':t' => $this->title
			]);
		}

		public function setGroupID(ContactGroup $e){
			$this->group_id = $e->id;
		}

		public function getAllDepartments(){
			return ContactDepartment::GET_ORDERED_LIST($this);
		}
	}