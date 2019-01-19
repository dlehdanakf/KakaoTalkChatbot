<?php
	class CommerceCard extends SkillTemplate {
		public $description;
		public $price;
		protected $currency;
		public $discount;
		/**
		 * @var array Thumbnail
		 */
		protected $thumbnails;
		/**
		 * @var Profile
		 */
		protected $profile;
		/**
		 * @var array Buttons
		 */
		protected $buttons;

		public function __construct(){
			$this->description = null;
			$this->price = 0;
			$this->currency = '₩';
			$this->discount = 0;
			$this->thumbnails = [];
			$this->profile = null;
			$this->buttons = [];
		}

		public function setProfile(Profile $profile = null){
			$this->profile = $profile;
		}
		public function addThumbnail(Thumbnail $e){
			if(count($this->thumbnails) > 3) return;
			array_push($this->thumbnails, $e);
		}
		public function addButtons(Button $e){
			if(count($this->buttons) > 3) return;
			array_push($this->buttons, $e);
		}

		public function getType(){ return 'commerceCard'; }
		public function render(){
			$description = $this->description;
			if(strlen($description) < 1) $description = '(설명없음)';
			if(count($this->thumbnails) < 1) return null;

			$return_array = [
				'description' => $description,
				'price' => (int) $this->price,
				'currency' => $this->currency,
				'thumbnails' => [],
				'buttons' => []
			];

			if(intval($this->discount) > 0)
				$return_array['discount'] = (int) $this->discount;

			foreach($this->thumbnails as $thumbnail){
				$tnl = $thumbnail->render();
				if($tnl !== null)
					array_push($return_array['thumbnails'], $tnl);
			}
			foreach($this->buttons as $button){
				$btn = $button->render();
				if($btn !== null)
					array_push($return_array['buttons'], $btn);
			}

			if($this->profile !== null)
				$return_array['profile'] = $this->profile->render();

			return $return_array;
		}
	}