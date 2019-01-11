<?php
	class AffiliateGroup extends BasicModel {
		const CATEGORY_FOOD = 'F';
		const CATEGORY_PLAY = 'P';

		public $title;
		public $label;
		public $description;
		protected $thumbnail_id;
		public $priority;
		public $category;

		public static function GET_ORDERED_LIST($category){
			$query = B::DB()->prepare("SELECT id FROM affiliate_group WHERE category = :c ORDER BY priority, id DESC");
			$query->execute([
				':c' => $category
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO affiliate_group (title, label, description, thumbnail_id, priority, category) VALUE (:t, :l, :d, :i, :p, :c)");
			$query->execute([
				':t' => $this->title,
				':l' => $this->label,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':p' => $this->priority,
				':c' => $this->category
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function update(){
			$query = B::DB()->prepare("UPDATE affiliate_group SET title = :t, label = :l, description = :d, thumbnail_id = :i, priority = :p, category = :c WHERE id = :id");
			$query->execute([
				':t' => $this->title,
				':l' => $this->label,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':p' => $this->priority,
				':c' => $this->category,
				':id' => $this->id
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate_group WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getCategory(){
			switch($this->category){
				case self::CATEGORY_FOOD: return '음식';
				case self::CATEGORY_PLAY: return '문화';
				default: return '기타';
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

		public function getAllAffiliates(){
			return AffiliateGroupLink::GET_ALL_GROUPED_AFFILIATES($this);
		}
		public function getRandomAffiliates($count = 10){
			return AffiliateGroupLink::GET_RANDOM_GROUPED_AFFILIATES($this, $count);
		}
		public function addAffiliate(Affiliate $affiliate){
			$affiliateLink = new AffiliateGroupLink($affiliate, $this);
			$affiliateLink->save();
		}
		public function deleteAffiliate(Affiliate $affiliate){
			$affiliateLink = new AffiliateGroupLink($affiliate, $this);
			$affiliateLink->delete();
		}
	}