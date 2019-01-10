<?php
	class DeliveryGroup extends BasicModel {
		public $title;
		public $label;
		public $description;

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
			$query = B::DB()->prepare("SELECT * FROM delivery_group WHERE label = :l");
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
			$query = $pdo->prepare("INSERT INTO delivery_group (title, label, description) VALUE (:t, :l, :d)");
			$query->execute([
				':t' => $this->title,
				':l' => $this->label,
				':d' => $this->description
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE delivery_group SET title = :t, label = :l, description = :d WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':l' => $this->label,
				':d' => $this->description,
				':id' => $this->id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM delivery_group WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getDeliveryCount(){
			return DeliveryGroupLink::GET_GROUPED_DELIVERIES_COUNT($this);
		}
		public function getAllDeliveries(){
			return DeliveryGroupLink::GET_ALL_GROUPED_DELIVERIES($this);
		}
		public function getRandomDeliveries($count = 10){
			return DeliveryGroupLink::GET_RANDOM_GROUPED_DELIVERIES($this, $count);
		}
		public function addDelivery(Delivery $delivery){
			$deliveryLink = new DeliveryGroupLink($delivery, $this);
			$deliveryLink->save();
		}
		public function deleteDelivery(Delivery $delivery){
			$deliveryLink = new DeliveryGroupLink($delivery, $this);
			$deliveryLink->delete();
		}
		public function deleteAllDeliveries(){
			DeliveryGroupLink::DELETE_ALL_DELIVERIES($this);
		}

		/**
		 * @return BasicCard
		 */
		public function getBasicCard(){
			$basicCard = new BasicCard;
			$basicCard->title = $this->title;
			$basicCard->description = $this->description;
			$basicCard->addButton((new Button("식당목록"))->setMessageText($this->label . " 배달음식점 목록 보여줘"));

			return $basicCard;
		}
	}