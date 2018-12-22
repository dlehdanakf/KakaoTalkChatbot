<?php
	class ListCard extends SkillTemplate {
		/**
		 * @var ListItem
		 */
		protected $header;
		/**
		 * @var array ListItem
		 */
		protected $items;
		/**
		 * @var array Button
		 */
		protected $buttons;

		public function __construct(){
			$this->header = new ListItem;
			$this->items = [];
			$this->buttons = [];
		}

		public function setHeader(ListItem $e){ $this->header = $e; }
		public function addListItem(ListItem $e){
			if(count($this->items) > 5)
				return;

			array_push($this->items, $e);
		}
		public function addButton(Button $e){
			if(count($this->buttons) > 2)
				return;

			array_push($this->buttons, $e);
		}

		public function getType(){ return 'listCard'; }
		public function render(){
			$header = $this->header->render();
			if($header == null) return null;

			$return_array = [
				'header' => $header,
				'items' => [],
				'buttons' => []
			];

			foreach($this->items as $item){
				$render = $item->render();
				if($render != null)
					array_push($return_array['items'], $item);
			}
			foreach($this->buttons as $button){
				$render = $button->render();
				if($render != null)
					array_push($return_array['buttons'], $button);
			}

			if(count($return_array['items']) < 1)
				return null;

			return $return_array;
		}
	}