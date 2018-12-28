<?php
	class ToiletFloor extends BasicModel {
		protected $building_id;
		public $floor;

		static public function GET_RANDOM_ITEM(ToiletBuilding $e){
			$query = B::DB()->prepare("SELECT * FROM toilet_floor WHERE building_id = :b ORDER BY RAND() LIMIT 1");
			$query->execute([
				':b' => $e->id
			]);

			$instance = new self;
			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO toilet_floor (building_id, floor) VALUE (:b, :f)");
			$query->execute([
				':b' => $this->building_id,
				':f' => $this->floor
			]);
		}

		public function setBuildingID(ToiletBuilding $e){
			$this->building_id = $e->id;
		}
	}