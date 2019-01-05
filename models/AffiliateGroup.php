<?php
	class AffiliateGroup extends BasicModel {
		public $title;
		public $description;
		protected $thumbnail_id;
		public $priority;

		public static function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM affiliate_group ORDER BY priority, id DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO affiliate_group (title, description, thumbnail_id, priority) VALUE (:t, :d, :i, :p)");
			$query->execute([
				':t' => $this->title,
				':d' => $this->description,
				':i' => $this->thumbnail_id,
				':p' => $this->priority
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM affiliate_group WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);
		}

		public function getThumbnail(){
			if(!$this->thumbnail_id)
				return null;

			return Attachment::CREATE_BY_MYSQLID($this->thumbnail_id);
		}
		public function setThumbnail(Attachment $attachment){
			$this->thumbnail_id = $attachment->id;
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