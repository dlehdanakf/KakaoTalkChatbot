<?php
	class SimpleImage extends SkillTemplate {
		public $imageUrl;
		public $altText;

		public function __construct($url, $alt){
			$this->imageUrl = null;
			$this->altText = null;

			if($url) $this->imageUrl = (String) $url;
			if($alt) $this->altText = (String) $alt;
		}

		public function getType(){ return 'simpleImage'; }
		public function render(){
			if($this->imageUrl === null)
				return null;

			$url = $this->imageUrl;
			$alt = $this->altText;

			if(!$url) $url = 'https://placehold.it/800x600';
			if(!$alt) $alt = '(이미지 소개 없음)';

			return [
				'imageUrl' => $url,
				'altText' => $alt
			];
		}
	}