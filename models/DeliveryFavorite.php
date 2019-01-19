<?php
	class DeliveryFavorite {
		public $member_id;
		public $delivery_id;
		public $register_date;

		public static function GET_FAVORITE_DELIVERIES(Member $member){
			$query = B::DB()->prepare("SELECT delivery_id FROM delivery_favorite WHERE member_id = :m LIMIT 10 ORDER BY register_date DESC");
			$query->execute([
				':m' => $member->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new DeliveryItem($v));
			}

			return $return_array;
		}
		public static function GET_FAVORITE_DELIVERY_COUNT(Member $member){
			$query = B::DB()->prepare("SELECT COUNT(delivery_id) AS `cnt` FROM delivery_favorite WHERE member_id = :m");
			$query->execute([
				':m' => $member->id
			]);

			return $query->fetch(PDO::FETCH_ASSOC)['cnt'];
		}

		public function __construct(Member $member = null, DeliveryItem $deliveryItem){
			if($member === null || $deliveryItem === null)
				return;

			$this->member_id = $member->id;
			$this->delivery_id = $deliveryItem->id;
		}

		public function save(){
			if($this->isDuplicatedKey())
				throw new Exception("이미 MY메뉴에 추가된 메뉴입니다.");

			$query = B::DB()->prepare("INSERT INTO delivery_favorite (member_id, delivery_id) VALUE (:m, :d)");
			$query->execute([
				':m' => $this->member_id,
				':d' => $this->delivery_id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM delivery_favorite WHERE member_id = :m AND delivery_id = :d");
			$query->execute([
				':m' => $this->member_id,
				':d' => $this->delivery_id
			]);
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT * FROM delivery_favorite WHERE member_id = :m AND delivery_id = :d");
			$query->execute([
				':m' => $this->member_id,
				':d' => $this->delivery_id
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
	}