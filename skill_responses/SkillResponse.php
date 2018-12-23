<?php
	class SkillResponse {
		private $version;
		protected $outputs;
		protected $quickReplies;
		protected $context;
		protected $data;

		public function __construct(){
			$this->version = '2.0';
			$this->outputs = [];
			$this->quickReplies = [];
			$this->context = [];
			$this->data = [];
		}

		public function addResponseComponent(SkillTemplate $e){
			if(count($this->outputs) > 3)
				return;

			array_push($this->outputs, $e);
		}
		public function addQuickReplies(QuickReply $e){
			if(count($this->quickReplies) > 10)
				return;

			array_push($this->quickReplies, $e);
		}

		public function render(){
			$return_array = [
				'version' => $this->version,
				'template' => [
					'outputs' => [],
					'quickReplies' => []
				]
			];

			foreach($this->outputs as $template){
				$render = $template->render();
				if($render != null)
					array_push($return_array['template']['outputs'], [
						$template->getType() => $render
					]);
			}
			foreach($this->quickReplies as $quickReply){
				$render = $quickReply->render();
				if($render != null)
					array_push($return_array['template']['quickReplies'], $render);
			}

			if(count($return_array['template']['outputs']) < 1)
				throw new Exception("응답 말풍선 갯수가 1개 미만입니다.");

			return $return_array;
		}
	}