<?php
	class AffiliateItem extends BasicModel {
		protected $affiliate_id;
		public $title;
		public $price;
		public $discount;
		protected $thumbnail_id;
		public $is_visible;

		public static function GET_AFFILIATE_GROUPED_COUNT(Affiliate $affiliate){
			$query = B::DB()->prepare("SELECT COUNT(id) AS `cnt` FROM affiliate_item WHERE affiliate_id = :i");
			$query->execute([
				':i' => $affiliate->id
			]);

			return intval($query->fetch(PDO::FETCH_ASSOC)['cnt']);
		}
		public static function GET_ALL_AFFILIATE_GROUPED_LIST(Affiliate $affiliate, $count = 10){
			$count = intval($count);
			$query = B::DB()->prepare("SELECT id FROM affiliate_item WHERE affiliate_id = :i ORDER BY id DESC LIMIT $count");
			$query->execute([
				':i' => $affiliate->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function GET_RANDOM_AFFILIATE_GROUPED_LIST(Affiliate $affiliate, $count = 10){
			$count = intval($count);
			$query = B::DB()->prepare("SELECT id FROM affiliate_item WHERE affiliate_id = :i ORDER BY RAND() DESC LIMIT $count");
			$query->execute([
				':i' => $affiliate->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function DELETE_ALL_AFFILIATE_GROUPED_ITEM(Affiliate $affiliate){
			$query = B::DB()->prepare("DELETE FROM affiliate_item WHERE affiliate_id = :i");
			$query->execute([
				':i' => $affiliate->id
			]);
		}

		public function __construct($id = 0) {
			$this->price = 0;
			$this->discount = 0;
			$this->is_visible = 'N';

			parent::__construct($id);
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO affiliate_item (affiliate_id, title, price, discount, thumbnail_id, is_visible) VALUE (:di, :t, :p, :d, :i, :v)");
			$query->execute([
				':di' => $this->affiliate_id,
				':t' => $this->title,
				':p' => intval($this->price),
				':d' => intval($this->discount),
				':i' => $this->thumbnail_id,
				':v' => $this->is_visible
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE affiliate_item SET title = :t, price = :p, discount = :d, thumbnail_id = :i, is_visible = :v WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':p' => intval($this->price),
				':d' => intval($this->discount),
				':i' => $this->thumbnail_id,
				':v' => $this->is_visible,
				':id' => $this->id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate_item WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getAffiliate(){
			return new Affiliate($this->affiliate_id);
		}
		public function getAffiliateID(){
			return $this->affiliate_id;
		}
		public function setAffiliate(Affiliate $affiliate){
			$this->affiliate_id = $affiliate->id;
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

		/**
		 * @return CommerceCard
		 */
		public function getCommerceCard(){
			$commerceCard = new CommerceCard;
			$commerceCard->description = (string) $this->title;
			$commerceCard->price = (int) $this->price;

			if(intval($this->discount) > 0)
				$commerceCard->discount = (int) $this->discount;

			if($this->thumbnail_id != 0 && $this->thumbnail_id != null)
				$thumbnail = new Thumbnail("http://chatbot.kunnect.net" . $this->getThumbnail()->getDownloadLinkDirectory());
			else
				$thumbnail = new DefaultThumbnail;

			$commerceCard->addThumbnail($thumbnail);
			$commerceCard->addButtons((new Button("공유하기"))->setActionShare());

			return $commerceCard;
		}
	}