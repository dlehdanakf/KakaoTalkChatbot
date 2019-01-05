<?php
	class Delivery extends BasicModel {
		public $label;
		public $title;
		public $description;
		protected $thumbnail_id;
		public $contact;

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

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO delivery (label, title, description, thumbnail_id, contact) VALUE (:l, :t, :d, :i, :c)");
			$query->execute([
				':l' => $this->label,
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':c' => $this->contact
			]);

			$this->id = $pdo->lastInsertId();
		}

		public function getThumbnail(){
			if(!$this->thumbnail_id)
				return null;

			return Attachment::CREATE_BY_MYSQLID($this->thumbnail_id);
		}
		public function setThumbnail(Attachment $attachment){
			$this->thumbnail_id = $attachment->id;
		}
	}