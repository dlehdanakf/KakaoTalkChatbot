<?php
	class DeliveryItem extends BasicModel {
		protected $delivery_id;
		public $title;
		public $price;
		public $discount;
		protected $thumbnail_id;
		public $is_visible;

		public static function GET_DELIVERY_GROUPED_COUNT(Delivery $delivery){
			$query = B::DB()->prepare("SELECT COUNT(id) AS `cnt` FROM delivery_item WHERE delivery_id = :i");
			$query->execute([
				':i' => $delivery->id
			]);

			return intval($query->fetch(PDO::FETCH_ASSOC)['cnt']);
		}
		public static function GET_ALL_DELIVERY_GROUPED_LIST(Delivery $delivery, $count = 10){
			$count = intval($count);
			$query = B::DB()->prepare("SELECT id FROM delivery_item WHERE delivery_id = :i ORDER BY id DESC LIMIT $count");
			$query->execute([
				':i' => $delivery->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function GET_RANDOM_DELIVERY_GROUPED_LIST(Delivery $delivery, $count = 10){
			$count = intval($count);
			$query = B::DB()->prepare("SELECT id FROM delivery_item WHERE delivery_id = :i ORDER BY RAND() DESC LIMIT $count");
			$query->execute([
				':i' => $delivery->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function DELETE_ALL_DELIVERY_GROUPED_ITEM(Delivery $delivery){
			$query = B::DB()->prepare("DELETE FROM delivery_item WHERE delivery_id = :i");
			$query->execute([
				':i' => $delivery->id
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
			$query = $pdo->prepare("INSERT INTO delivery_item (delivery_id, title, price, discount, thumbnail_id, is_visible) VALUE (:di, :t, :p, :d, :i, :v)");
			$query->execute([
				':di' => $this->delivery_id,
				':t' => $this->title,
				':p' => intval($this->price),
				':d' => intval($this->discount),
				':i' => $this->thumbnail_id,
				':v' => $this->is_visible
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE delivery_item SET title = :t, price = :p, discount = :d, thumbnail_id = :i, is_visible = :v WHERE id = :id");
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
			$query = B::DB()->prepare("DELETE FROM delivery_item WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getDelivery(){
			return new Delivery($this->delivery_id);
		}
		public function getDeliveryID(){
			return $this->delivery_id;
		}
		public function setDelivery(Delivery $delivery){
			$this->delivery_id = $delivery->id;
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
				$thumbnail = new Thumbnail($this->getThumbnailURL());
			else
				$thumbnail = new DefaultThumbnail;

			$commerceCard->addThumbnail($thumbnail);
			$commerceCard->addButtons((new Button("자주 찾는 메뉴로 등록하기"))->setActionShare());
			$commerceCard->addButtons((new Button("공유하기"))->setActionShare());

			return $commerceCard;
		}
		public function getThumbnailURL(){
			return B::GET_IMAGE_URL() . "/delivery/item/" . $this->id;
		}
	}