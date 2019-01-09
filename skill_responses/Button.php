<?php
	class Button {
		public $label;
		protected $action;
		public $webLinkUrl;
		/**
		 * @var Link
		 */
		public $osLink;
		public $messageText;
		public $phoneNumber;
		public $blockId;
		public $extra;

		public function __construct($label){
			$this->label = $label;
			$this->extra = [];
		}

		public function setWebLinkUrl($e){
			$this->action = 'webLink';
			$this->webLinkUrl = (String) $e;

			return $this;
		}
		public function setOsLink(Link $e){
			$this->action = 'osLink';
			$this->osLink = $e;

			return $this;
		}
		public function setMessageText($e){
			$this->action = 'message';
			$this->messageText = (String) $e;

			return $this;
		}
		public function setPhoneNumber($e){
			$this->action = 'phone';
			$this->phoneNumber = (String) $e;

			return $this;
		}
		public function setActionShare(){
			$this->action = 'share';

			return $this;
		}
		public function setBlockID($block_id, $messageText, $extra = []){
			$this->action = 'block';
			$this->blockId = $block_id;
			$this->messageText = (String) $messageText;
			if(is_array($extra)) $this->extra = $extra;

			return $this;
		}

		public function render(){
			$label = $this->label;
			if(strlen($label) < 1) $label = '(제목없음)';

			switch($this->action){
				case 'webLink':
					if(strlen($this->webLinkUrl) < 1) return null;
					return [
						'action' => 'webLink',
						'label' => $label,
						'webLinkUrl' => $this->webLinkUrl
					];

				case 'osLink':
					$link = $this->osLink->render();
					if($link == null) return null;
					return [
						'action' => 'osLink',
						'label' => $label,
						'osLink' => $link
					];

				case 'message':
					if(strlen($this->messageText) < 1) return null;
					return [
						'action' => 'message',
						'label' => $label,
						'messageText' => $this->messageText
					];

				case 'phone':
					if(strlen($this->phoneNumber) < 1) return null;
					return [
						'action' => 'phone',
						'label' => $label,
						'phoneNumber' => $this->phoneNumber
					];

				case 'share':
					return [
						'action' => 'share',
						'label' => $label
					];

				case 'block':
					$return_array = [
						'action' => 'block',
						'blockId' => $this->blockId
					];
					if($this->messageText)
						$return_array['messageText'] = $this->messageText;
					if(is_array($this->extra) && count($this->extra) > 0)
						$return_array['extra'] = $this->extra;

					return $return_array;

				default:
					return null;
			}
		}
	}