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

		public function __construct($label){
			$this->label = $label;
		}

		public function setWebLinkUrl($e){
			$this->action = 'webLink';
			$this->webLinkUrl = (String) $e;
		}
		public function setOsLink(Link $e){
			$this->action = 'osLink';
			$this->osLink = $e;
		}
		public function setMessageText($e){
			$this->action = 'message';
			$this->messageText = (String) $e;
		}
		public function setPhoneNumber($e){
			$this->action = 'phone';
			$this->phoneNumber = (String) $e;
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

				default:
					return null;
			}
		}
	}