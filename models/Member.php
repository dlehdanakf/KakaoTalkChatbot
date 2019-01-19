<?php
	class Member extends BasicModel {
		public $user_key;
		public $username;
		public $phone;
		public $seed;

		public static function CREATE_BY_KEY($key){
			$query = B::DB()->prepare("SELECT * FROM member WHERE user_key = :k");
			$query->execute([
				':k' => $key
			]);

			$instance = new self;
			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($instance) . " 객체를 찾을 수 없습니다. key - " . $key);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function save(){
			if($this->isDuplicatedKey()){
				$this->update();
				return;
			}

			$this->insert();
		}
		public function generateSeed(){
			$query = B::DB()->prepare("UPDATE member SET seed = :s WHERE user_key = :k");
			$query->execute([
				':s' => (rand() * 100) % 100
			]);
		}

		public function getFavoriteDeliveryItems(){
			return DeliveryFavorite::GET_FAVORITE_DELIVERIES($this);
		}
		public function addFavoriteDeliveryItem(DeliveryItem $deliveryItem){
			if(DeliveryFavorite::GET_FAVORITE_DELIVERY_COUNT($this) >= 10)
				throw new Exception("MY메뉴는 10개이상 등록하실 수 없습니다.");

			$deliveryFavorite = new DeliveryFavorite($this, $deliveryItem);
			$deliveryFavorite->save();
		}
		public function deleteFavoriteDeliveryItem(DeliveryItem $deliveryItem){
			$deliveryFavorite = new DeliveryFavorite($this, $deliveryItem);
			$deliveryFavorite->delete();
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT id FROM member WHERE user_key = :k");
			$query->execute([
				':k' => $this->user_key
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
		protected function insert(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO member (user_key, username, phone, seed) VALUE (:k, :n, :p, :s)");
			$query->execute([
				':k' => $this->user_key,
				':n' => $this->username,
				':p' => $this->phone,
				':s' => (rand() * 100) % 100
			]);

			$this->id = $pdo->lastInsertId();
		}
		protected function update(){
			$query = B::DB()->prepare("UPDATE member SET username = :n, phone = :p WHERE user_key = :k");
			$query->execute([
				':k' => $this->user_key,
				':n' => $this->username,
				':p' => $this->phone
			]);
		}
	}