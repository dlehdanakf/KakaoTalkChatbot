<?php
	class Cafeteria extends BasicModel {
		public $title;
		public $serial;
		public $location;
		public $semester_open;
		public $vacation_open;
		public $priority;

		static public function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM cafeteria ORDER BY priority, id DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		static public function CREATE_BY_TITLE($title){
			$query = B::DB()->prepare("SELECT * FROM cafeteria WHERE title = :t");
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

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO cafeteria (title, serial, location, semester_open, vacation_open, priority) VALUE (:t, :s, :l, :o, :v, :p)");
			$query->execute([
				':t' => $this->title,
				':s' => $this->serial,
				':l' => $this->location,
				':o' => $this->semester_open,
				':v' => $this->vacation_open,
				':p' => $this->priority
			]);

			$this->id = $pdo->lastInsertId();
		}
	}