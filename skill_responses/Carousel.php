<?php
	class Carousel extends SkillTemplate {
		protected $type;
		/**
		 * @var array SkillTemplate
		 */
		protected $items;

		public function __construct(){
			$this->type = null;
			$this->items = [];
		}

		public function addCard(SkillTemplate $e){
			if(count($this->items) > 10)
				return;

			if(!in_array($e->getType(), ['simpleText', 'basicCard', 'commerceCard']))
				return;

			foreach($this->items as $card){
				if($card->getType() != $e->getType())
					return;
			}

			$this->type = $e->getType();
			array_push($this->items, $e);
		}

		public function getType(){ return 'carousel'; }
		public function render(){
			$return_array = [
				'type' => $this->type,
				'items' => []
			];

			foreach($this->items as $card){
				$render = $card->render();
				if($render != null)
					array_push($return_array['items'], $render);
			}

			if(count($return_array['items']) < 1)
				return null;

			return $return_array;
		}
	}