<?php
	class Affiliate extends BasicModel {
		const PROMOTION_DISCOUNT = 1;
		const PROMOTION_DRINK = 2;
		const PROMOTION_FREE = 3;

		public $title;
		public $description;
		protected $thumbnail_id;
		public $location;
		public $map_x;
		public $map_y;
		public $contact;
		public $contract;
		public $promotion;

		public static function CREATE_BY_TITLE($title){
			$query = B::DB()->prepare("SELECT * FROM affiliate WHERE title = :t");
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

		public function __construct($id = 0) {
			$this->promotion = 0;

			parent::__construct($id);
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO affiliate (title, description, thumbnail_id, location, map_x, map_y, contact, contract, promotion) VALUE (:t, :d, :i, :l, :x, :y, :c, :a, :p)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':l' => $this->location,
				':x' => $this->map_x,
				':y' => $this->map_y,
				':c' => $this->contact,
				':a' => $this->contract,
				':p' => $this->promotion
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE affiliate SET title = :t, description = :d, thumbnail_id = :i, location = :l, map_x = :x, map_y = :y, contact = :c, contract = :a, promotion = :p WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':l' => $this->location,
				':x' => $this->map_x,
				':y' => $this->map_y,
				':c' => $this->contact,
				':a' => $this->contract,
				':p' => $this->promotion,
				':id' => $this->id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getContract(){
			switch($this->contract){
				case 0: return "임의";
				case self::CONTRACT_FREE: return "무료";
				case self::CONTRACT_PAID: return "유료";
			}
		}
		public function getPromotion(){
			switch($this->promotion){
				case 0: return "없음";
				case self::PROMOTION_DISCOUNT: return "할인";
				case self::PROMOTION_DRINK: return "음료수";
				case self::PROMOTION_FREE: return "무료";
			}
		}

		public function getThumbnail(){
			if(!$this->thumbnail_id)
				return null;

			return Attachment::CREATE_BY_MYSQLID($this->thumbnail_id);
		}
		public function getThumbnailID(){
			return $this->thumbnail_id;
		}
		public function setThumbnail(Attachment $attachment){
			$this->thumbnail_id = $attachment->id;
		}
		public function removeThumbnail(){
			$this->thumbnail_id = null;
		}

		public function getBelongingGroups(){
			return AffiliateGroupLink::GET_BELONGING_GROUPS($this);
		}
		public function releaseAllBelongingGroups(){
			AffiliateGroupLink::DELETE_ALL_BELONGING_GROUPS($this);
		}
	}