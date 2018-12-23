<?php
	class QuickReply {
		public $label;
		protected $action;
		protected $messageText;
		protected $blockId;
		protected $extra;

		public function __construct($label){
			$this->label = (string) $label;
		}

		public function setMessageText($text){
			$this->action = 'message';
			$this->messageText = (string) $text;

			return $this;
		}
		public function setBlockID($id){
			$this->action = 'block';
			$this->blockId = $id;

			return $this;
		}

		public function render(){
			if(!$this->label || !$this->action)
				return null;

			if($this->action === 'block'){
				return [
					'label' => $this->label,
					'action' => $this->action,
					'blockId' => $this->blockId,
					'extra' => null
				];
			}

			return [
				'label' => $this->label,
				'action' => $this->action,
				'messageText' => $this->messageText
			];
		}
	}