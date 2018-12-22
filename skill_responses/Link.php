<?php
	class Link {
		public $mobile;
		public $ios;
		public $android;
		public $pc;
		public $mac;
		public $win;
		public $web;

		public function __construct($url = null){
			if($url === null) return;
			$this->web = (String) $url;
		}
		public function render(){
			$is_null = true;
			$return_array = [];
			$category = ["mobile", "ios", "android", "pc", "mac", "win", "web"];
			foreach($category as $c){
				if($this->$c){
					$is_null = false;
					$return_array[$c] = $this->$c;
				}
			}

			if($is_null === true)
				return null;

			return $return_array;
		}
	}