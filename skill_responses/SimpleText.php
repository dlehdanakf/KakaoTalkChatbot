<?php
	class SimpleText extends SkillTemplate {
		public $text;

		public function __construct($text){
			$this->text = (String) $text;
		}

		public function getType(){ return 'simpleText'; }
		public function render(){
			$text = $this->text;
			if(gettype($text) !== gettype("String") || strlen($text) < 1)
				$text = '(내용없음)';

			return [
				'text' => $text
			];
		}
	}