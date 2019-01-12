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
		public static function CREATE_BY_LABEL($label){
			$query = B::DB()->prepare("SELECT * FROM affiliate_group WHERE label = :l");
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

		public function getAffiliateCount(){
			return AffiliateGroupLink::GET_GROUPED_AFFILIATES_COUNT($this);
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

		public function getBasicCard(){
			$basicCard = new BasicCard;
			$basicCard->title = $this->title;
			$basicCard->description = $this->description;

			if($this->thumbnail_id != 0 && $this->thumbnail_id != null)
				$thumbnail = new Thumbnail("http://chatbot.kunnect.net" . $this->getThumbnail()->getDownloadLinkDirectory());
			else
				$thumbnail = new DefaultThumbnail;

			$basicCard->setThumbnail($thumbnail);
			$basicCard->addButton((new Button("목록보기"))->setBlockID('5c389f6b5f38dd44d86a5805', $this->label . " 목록"));

			return $basicCard;
		}
		public function getUtterance(){
			switch($this->category){
				case 'F': return "학교주변 맛집 알려줘";
				case 'P': return "학교주변 놀거리 추천해줘";
			}
		}
	}