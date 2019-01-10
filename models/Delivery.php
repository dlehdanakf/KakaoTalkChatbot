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

		public static function CREATE_BY_TITLE($title){
			$query = B::DB()->prepare("SELECT * FROM delivery WHERE title = :t");
			$query->execute([
				':t' => $title
			]);

			$instance = new self;
			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($instance) . " 객체를 찾을 수 없습니다. label - " . $title);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

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
		public function update(){
			$query = B::DB()->prepare("UPDATE delivery SET title = :t, description = :d, thumbnail_id = :i, contact = :c, contract = :r, promotion = :p WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':c' => $this->contact,
				':r' => $this->contract,
				':p' => $this->promotion,
				':id' => $this->id
			]);
		}
		public function delete(){
			$this->deleteAllItems();

			$query = B::DB()->prepare("DELETE FROM delivery WHERE id = :i");
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
			return DeliveryGroupLink::GET_BELONGING_GROUPS($this);
		}
		public function releaseAllBelongingGroups(){
			DeliveryGroupLink::DELETE_ALL_BELONGING_GROUPS($this);
		}

		public function getItemCount(){
			return DeliveryItem::GET_DELIVERY_GROUPED_COUNT($this);
		}
		public function getAllItems($count = 999){
			return DeliveryItem::GET_ALL_DELIVERY_GROUPED_LIST($this, $count);
		}
		public function getRandomItems($count = 10){
			return DeliveryItem::GET_RANDOM_DELIVERY_GROUPED_LIST($this, $count);
		}
		public function deleteAllItems(){
			DeliveryItem::DELETE_ALL_DELIVERY_GROUPED_ITEM($this);
		}

		public function getBasicCard(){
			$basicCard = new BasicCard;
			$basicCard->title = $this->title;
			$basicCard->description = $this->getBasicCardDescription();

			if($this->thumbnail_id != 0 && $this->thumbnail_id != null)
				$thumbnail = new Thumbnail("http://chatbot.kunnect.net" . $this->getThumbnail()->getDownloadLinkDirectory());
			else
				$thumbnail = new DefaultThumbnail;

			$basicCard->setThumbnail($thumbnail);
			if($this->getItemCount() > 0)
				$basicCard->addButton((new Button("대표메뉴"))->setBlockID('5c3596ce384c5518d1200851', $this->title ." 대표메뉴"));
			$basicCard->addButton((new Button("배달 주문하기"))->setPhoneNumber($this->contact));
			$basicCard->addButton((new Button("공유하기"))->setActionShare());

			return $basicCard;
		}

		protected function getBasicCardDescription(){
			if($this->description)
				return $this->description;

			$items = DeliveryItem::GET_RANDOM_DELIVERY_GROUPED_LIST($this, 4);
			if(count($items) > 0){
				$description = "";
				foreach($items as $i => $item){
					if($i > 0) $description .= ", ";
					$description .= $item->title;
				}

				return $description;
			}

			return " ";
		}
	}