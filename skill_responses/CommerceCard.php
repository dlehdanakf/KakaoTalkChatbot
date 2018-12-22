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
		 * @var array Buttons
		 */
		protected $buttons;

		public function __construct(){
			$this->description = null;
			$this->price = 0;
			$this->currency = '₩';
			$this->discount = 0;
			$this->thumbnails = [];
			$this->buttons = [];
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
				'price' => $this->price,
				'currency' => $this->currency,
				'discount' => $this->discount,
				'thumbnails' => [],
				'buttons' => []
			];

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

			return $return_array;
		}
	}