<?php
	class QuickReply {
		public $label;
		protected $action;
		protected $messageText;
		protected $blockId;
		protected $extra;

		public function __construct($label){
			$this->label = (string) $label;
			$this->extra = [];
		}

		public function setMessageText($text){
			$this->action = 'message';
			$this->messageText = (string) $text;

			return $this;
		}
		public function setBlockID($id, $extra = []){
			$this->action = 'block';
			$this->blockId = $id;
			if(is_array($extra))
				$this->extra = $extra;

			return $this;
		}

		public function render(){
			if(!$this->label || !$this->action)
				return null;

			if($this->action === 'block'){
				$return_array = [
					'label' => $this->label,
					'action' => $this->action,
					'blockId' => $this->blockId
				];

				if(is_array($this->extra) && count($this->extra) > 0)
					$return_array['extra'] = $this->extra;

				return $return_array;
			}

			return [
				'label' => $this->label,
				'action' => $this->action,
				'messageText' => $this->messageText
			];
		}
	}