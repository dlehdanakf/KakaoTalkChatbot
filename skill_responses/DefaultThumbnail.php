<?php
	class DefaultThumbnail extends Thumbnail {
		public function __construct(){
			$url = B::GET_SERVICE_URL() . "/assets/images/default.png";
			parent::__construct($url);
		}
	}