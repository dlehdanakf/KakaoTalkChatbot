<?php
	class DeliveryGroupCategory extends BasicModel {
		public $title;
		public $description;
		protected $thumbnail_id;
		protected $group1_id;
		protected $group2_id;
		protected $group3_id;
		public $priority;

		public static function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM delivery_group_category ORDER BY priority DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		public static function CREATE_BY_LABEL($label){
			$query = B::DB()->prepare("SELECT * FROM delivery_group_category WHERE label = :l");
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
			$query = $pdo->prepare("INSERT INTO delivery_group_category (title, description, thumbnail_id, group1_id, group2_id, group3_id, priority) VALUE (:t, :d, :i, :g1, :g2, :g3, :p)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':g1' => $this->group1_id,
				':g2' => $this->group2_id,
				':g3' => $this->group3_id,
				':p' => $this->priority
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE delivery_group_category SET title = :t, description = :d, thumbnail_id = :i, group1_id = :g1, group2_id = :g2, group3_id = :g3, priority = :p WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':g1' => $this->group1_id,
				':g2' => $this->group2_id,
				':g3' => $this->group3_id,
				':p' => $this->priority,
				':id' => $this->id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM delivery_group_category WHERE id = :i");
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
		public function removeThumbnail(){
			$this->thumbnail_id = null;
		}

		public function setDeliveryGroup($int, DeliveryGroup $group){
			switch($int){
				case 1: $this->group1_id = $group->id; return;
				case 2: $this->group2_id = $group->id; return;
				case 3: $this->group3_id = $group->id; return;
			}
		}
		public function getDeliveryGroupID($int){
			switch($int){
				case 1: return $this->group1_id;
				case 1: return $this->group2_id;
				case 1: return $this->group3_id;
			}
		}
		public function getDeliveryGroup($int){
			switch($int){
				case 1: if($this->group1_id) return new DeliveryGroup($this->group1_id); else return null;
				case 2: if($this->group2_id) return new DeliveryGroup($this->group2_id); else return null;
				case 3: if($this->group3_id) return new DeliveryGroup($this->group3_id); else return null;
			}
		}
		public function releaseDeliveryGroup($int){
			switch($int){
				case 1: $this->group1_id = null; return;
				case 2: $this->group2_id = null; return;
				case 3: $this->group3_id = null; return;
			}
		}

		public function getDeliveryGroupLabel($title = false){
			$return_array = [];
			$int = [1, 2, 3];
			foreach($int as $i){
				$group = $this->getDeliveryGroup($i);
				if($group === null) {
					if($title) array_push($return_array, null);
					else array_push($return_array, "-");

					continue;
				}

				if($title) array_push($return_array, $group->title);
				else array_push($return_array, $group->label);
			}

			return $return_array;
		}

		public function getBasicCard(){
			$basicCard = new BasicCard;
			$basicCard->title = $this->description;

			if($this->thumbnail_id != 0 && $this->thumbnail_id != null)
				$thumbnail = new Thumbnail("http://chatbot.kunnect.net" . $this->getThumbnail()->getDownloadLinkDirectory());
			else
				$thumbnail = new DefaultThumbnail;

			$basicCard->setThumbnail($thumbnail);

			$this->setBasicCardButton(1, $basicCard);
			$this->setBasicCardButton(2, $basicCard);
			$this->setBasicCardButton(3, $basicCard);

			return $basicCard;
		}

		protected function setBasicCardButton($int, BasicCard &$basicCard){
			$group = $this->getDeliveryGroup($int);
			if($group === null) return;

			$basicCard->addButton((new Button($group->title))->setBlockID('5c30a7de384c5518d11fec0b', $group->label . " 배달음식점 목록"));
		}
	}