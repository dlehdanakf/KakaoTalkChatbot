<?php
	class Delivery extends BasicModel {
		const PROMOTION_DISCOUNT = 1;
		const PROMOTION_DRINK = 2;
		const PROMOTION_FREE = 3;

		const CONTRACT_FREE = 1;
		const CONTRACT_PAID = 2;

		public $title;
		public $description;
		protected $thumbnail_id;
		public $contact;
		public $contract;
		public $promotion;

		public function __construct($id = 0) {
			$this->contract = 0;
			$this->promotion = 0;

			parent::__construct($id);
		}

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO delivery (title, description, thumbnail_id, contact, contract, promotion) VALUE (:t, :d, :i, :c, :r, :p)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':c' => $this->contact,
				':r' => $this->contract,
				':p' => $this->promotion
			]);

			$this->id = $pdo->lastInsertId();
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
			return DeliveryGroupLink::GET_BELONGING_GROUPS($this);
		}
	}