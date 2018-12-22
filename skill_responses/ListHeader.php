<?php
	class ListHeader extends ListItem {
		public function render(){
			if($this->imageUrl){
				return [
					'title' => null,
					'description' => null,
					'imageUrl' => $this->imageUrl,
					'link' => null
				];
			}

			$title = $this->title;
			if(strlen((string) $title) < 1)
				$title = '(텍스트없음)';

			return [
				'title' => $title,
				'description' => $this->description,
				'imageUrl' => $this->imageUrl,
				'link' => $this->link !== null ? $this->link->render() : null
			];
		}
	}