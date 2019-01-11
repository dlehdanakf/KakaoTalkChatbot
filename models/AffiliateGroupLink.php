<?php
	class AffiliateGroupLink {
		public $affiliate_id;
		public $group_id;
		public $register_date;

		public static function GET_GROUPED_AFFILIATES_COUNT(AffiliateGroup $group){
			$query = B::DB()->prepare("SELECT COUNT(affiliate_id) AS `cnt` FROM affiliate_group_link WHERE group_id = :g");
			$query->execute([
				':g' => $group->id
			]);

			return (int) $query->fetch(PDO::FETCH_ASSOC)['cnt'];
		}
		public static function GET_ALL_GROUPED_AFFILIATES(AffiliateGroup $group){
			$query = B::DB()->prepare("SELECT affiliate_id FROM affiliate_group_link WHERE group_id = :g ORDER BY register_date DESC");
			$query->execute([
				':g' => $group->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new Affiliate($v));
			}

			return $return_array;
		}
		public static function GET_RANDOM_GROUPED_AFFILIATES(AffiliateGroup $group, $count = 8){
			$count = intval($count);
			if($count < 1)
				$count = 1;

			$query = B::DB()->prepare("SELECT affiliate_id FROM affiliate_group_link WHERE group_id = :g ORDER BY RAND() LIMIT $count");
			$query->execute([
				':g' => $group->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new Affiliate($v));
			}

			return $return_array;
		}
		public static function GET_BELONGING_GROUPS(Affiliate $affiliate){
			$query = B::DB()->prepare("SELECT group_id FROM affiliate_group_link WHERE affiliate_id = :a ORDER BY register_date DESC");
			$query->execute([
				':a' => $affiliate->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new AffiliateGroup($v));
			}

			return $return_array;
		}

		public function __construct(Affiliate $affiliate = null, AffiliateGroup $group = null){
			if($affiliate === null || $group === null)
				return;

			$this->affiliate_id = $affiliate->id;
			$this->group_id = $group->id;
		}

		public function save(){
			if($this->isDuplicatedKey())
				throw new Exception("이미 그룹에 등록된 제휴업체입니다.");

			$query = B::DB()->prepare("INSERT INTO affiliate_group_link (affiliate_id, group_id) VALUE (:a, :g)");
			$query->execute([
				':a' => $this->affiliate_id,
				':g' => $this->group_id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate_group_link WHERE affiliate_id = :a AND group_id = :g");
			$query->execute([
				':a' => $this->affiliate_id,
				':g' => $this->group_id
			]);
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT * FROM affiliate_group_link WHERE affiliate_id = :a AND group_id = :g");
			$query->execute([
				':a' => $this->affiliate_id,
				':g' => $this->group_id
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
	}