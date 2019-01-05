<?php
	class Affiliate extends BasicModel {
		const PROMOTION_DISCOUNT = 1;
		const PROMOTION_DRINK = 2;
		const PROMOTION_FREE = 3;

		public $title;
		public $description;
		protected $thumbnail_id;
		public $map_x;
		public $map_y;
		public $contact;
		public $promotion;

		public function __construct($id = 0) {
			$this->promotion = 0;

			parent::__construct($id);
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO affiliate (title, description, thumbnail_id, map_x, map_y, contact, promotion) VALUE (:t, :d, :i, :x, :y, :c, :p)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':x' => $this->map_x,
				':y' => $this->map_y,
				':c' => $this->contact,
				':p' => $this->promotion
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getThumbnail(){
			if(!$this->thumbnail_id)
				return null;

			return Attachment::CREATE_BY_MYSQLID($this->thumbnail_id);
		}
		public function setThumbnail(Attachment $attachment){
			$this->thumbnail_id = $attachment->id;
		}

		public function getBelongingGroups(){
			return AffiliateGroupLink::GET_BELONGING_GROUPS($this);
		}
	}