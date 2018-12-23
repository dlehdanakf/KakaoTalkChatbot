<?php
	class BasicCard extends SkillTemplate {
		public $title;
		public $description;
		/**
		 * @var Thumbnail
		 */
		protected $thumbnail;
		/**
		 * @var array Button
		 */
		protected $buttons;

		public function __construct(){
			$this->title = null;
			$this->description = null;
			$this->thumbnail = null;
			$this->buttons = [];
		}

		public function setThumbnail(Thumbnail $e){ $this->thumbnail = $e; }
		public function addButton(Button $e){
			if(count($this->buttons) > 3) return;
			array_push($this->buttons, $e);
		}

		public function getType(){ return 'basicCard'; }
		public function render(){
			if(!$this->title && !$this->description && count($this->buttons) < 1)
				return null;

			$return_array = [
				'title' => $this->title,
				'description' => $this->description,
				'thumbnail' => null,
				'buttons' => []
			];

			if($this->thumbnail !== null){
				$thumbnail = $this->thumbnail->render();
				if($thumbnail !== null) $return_array['thumbnail'] = $thumbnail;
			}

			foreach($this->buttons as $button){
				$btn = $button->render();
				if($btn !== null)
					array_push($return_array['buttons'], $btn);
			}

			return $return_array;
		}
	}