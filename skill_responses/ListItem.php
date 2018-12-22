<?php
	class ListItem {
		public $title;
		public $description;
		public $imageUrl;
		public $link;

		public function render(){
			$title = $this->title;
			if(strlen((string) $title) < 1)
				$title = '(텍스트없음)';

			return [
				'title' => $title,
				'description' => $this->description,
				'imageUrl' => $this->imageUrl,
				'link' => $this->link
			];
		}
	}