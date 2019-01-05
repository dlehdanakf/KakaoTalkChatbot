<?php
	class DeliveryGroupLink {
		public $delivery_id;
		public $group_id;
		public $register_date;

		public static function GET_ALL_GROUPED_AFFILIATES(DeliveryGroup $group){
			$query = B::DB()->prepare("SELECT delivery_id FROM delivery_group_link WHERE group_id = :g ORDER BY register_date DESC");
			$query->execute([
				':g' => $group->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new Delivery($v));
			}

			return $return_array;
		}
		public static function GET_RANDOM_GROUPED_AFFILIATES(DeliveryGroup $group, $count = 8){
			$count = intval($count);
			if($count < 1)
				$count = 1;

			$query = B::DB()->prepare("SELECT delivery_id FROM delivery_group_link WHERE group_id = :g ORDER BY RAND() LIMIT $count");
			$query->execute([
				':g' => $group->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new Delivery($v));
			}

			return $return_array;
		}
		public static function GET_BELONGING_GROUPS(Delivery $delivery){
			$query = B::DB()->prepare("SELECT group_id FROM delivery_group_link WHERE delivery_id = :d ORDER BY register_date DESC");
			$query->execute([
				':d' => $delivery->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new DeliveryGroup($v));
			}

			return $return_array;
		}

		public function __construct(Delivery $delivery = null, DeliveryGroup $group = null){
			if($delivery === null || $group === null)
				return;

			$this->delivery_id = $delivery->id;
			$this->group_id = $group->id;
		}

		public function save(){
			if($this->isDuplicatedKey())
				throw new Exception("이미 그룹에 등록된 제휴업체입니다.");

			$query = B::DB()->prepare("INSERT INTO delivery_group_link (delivery_id, group_id) VALUE (:d, :g)");
			$query->execute([
				':d' => $this->delivery_id,
				':g' => $this->group_id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM delivery_group_link WHERE delivery_id = :d AND group_id = :g");
			$query->execute([
				':d' => $this->delivery_id,
				':g' => $this->group_id
			]);
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT * FROM delivery_group_link WHERE delivery_id = :d AND group_id = :g");
			$query->execute([
				':d' => $this->delivery_id,
				':g' => $this->group_id
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
	}