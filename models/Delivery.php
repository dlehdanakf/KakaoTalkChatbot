<?php
	class Delivery extends BasicModel {
		public $title;
		public $description;
		protected $thumbnail_id;
		public $contact;

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO delivery (title, description, thumbnail_id, contact) VALUE (:t, :d, :i, :c)");
			$query->execute([
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