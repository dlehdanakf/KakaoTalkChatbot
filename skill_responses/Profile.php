<?php
	class Profile {
		public $imageUrl;
		public $nickname;

		public function __construct($nickname){
			$this->nickname = $nickname;
		}

		public function setThumbnail(Thumbnail $thumbnail){
			$this->imageUrl = $thumbnail->imageUrl;
			return $this;
		}
		public function setNickname($nickname){
			$this->nickname = (String) $nickname;
			return $this;
		}

		public function render(){
			return [
				'imageUrl' => $this->imageUrl,
				'nickname' => $this->nickname
			];
		}
	}