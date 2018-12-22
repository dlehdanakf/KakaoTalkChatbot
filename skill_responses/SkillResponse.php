<?php
	class SkillResponse {
		private $version;
		protected $template;
		protected $context;
		protected $data;

		public function __construct(){
			$this->version = '2.0';
			$this->template = [];
			$this->context = [];
			$this->data = [];
		}

		public function addResponseComponent(SkillTemplate $e){
			if(count($this->template) > 3)
				return;

			array_push($this->template, $e);
		}

		public function render(){
			$return_array = [
				'version' => $this->version,
				'template' => [],
				'context' => $this->context,
				'data' => $this->data
			];

			foreach($this->template as $template){
				$render = $template->render();
				if($render != null)
					$return_array['template'][$template->getType()] = $render;
			}

			if(count($return_array['template']) < 1)
				throw new Exception("Response Template 갯수가 1개 미만입니다.");

			return $return_array;
		}
	}