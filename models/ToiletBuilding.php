<?php
	class ToiletBuilding extends BasicModel {
		public $title;

		static public function GET_RANDOM_ITEM(){
			$query = B::DB()->prepare("SELECT * FROM toilet_building ORDER BY RAND() LIMIT 1");
			$query->execute();

			$instance = new self;
			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO toilet_building (title) VALUE (:t)");
			$query->execute([
				':t' => $this->title
			]);

			$this->id = $pdo->lastInsertId();
		}

		public function getRandomFloor(){
			return ToiletFloor::GET_RANDOM_ITEM($this);
		}
	}