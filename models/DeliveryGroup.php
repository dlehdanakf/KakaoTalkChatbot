<?php
	class DeliveryGroup extends BasicModel {
		public $title;
		public $label;
		public $description;
		protected $thumbnail_id;
		public $priority;

		public static function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM delivery_group ORDER BY priority DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function CREATE_BY_LABEL($label){
			$query = B::DB()->prepare("SELECT * FROM delivery WHERE label = :l");
			$query->execute([
				':l' => $label
			]);

			$instance = new self;
			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($instance) . " 객체를 찾을 수 없습니다. label - " . $label);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO delivery_group (title, label, description, thumbnail_id, priority) VALUE (:t, :l, :d, :i, :p)");
			$query->execute([
				':t' => $this->title,
				':l' => $this->label,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':p' => $this->priority
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM delivery_group WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
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

		public function getAllDeliveries(){
			return DeliveryGroupLink::GET_ALL_GROUPED_DELIVERIES($this);
		}
		public function getRandomDeliveries($count = 10){
			return DeliveryGroupLink::GET_RANDOM_GROUPED_DELIVERIES($this, $count);
		}
		public function addAffiliate(Delivery $delivery){
			$deliveryLink = new DeliveryGroupLink($delivery, $this);
			$deliveryLink->save();
		}
		public function deleteAffiliate(Delivery $delivery){
			$deliveryLink = new DeliveryGroupLink($delivery, $this);
			$deliveryLink->delete();
		}
	}