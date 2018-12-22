<?php
	class ContactsController {
		public function skillViewList(){
//			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['contact_keyword']);
			$requestBody = [
				'user' => 'test',
				'utterance' => '',
				'params' => [
					'contact_keyword' => '전화번호'
				]
			];

			$skillResponse = new SkillResponse;
			$simpleMessage = new SimpleText("Hello World!");

			$skillResponse->addResponseComponent($simpleMessage);

			return json_encode($skillResponse->render());
		}
		public function skillViewDetail(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['contact_keyword', 'contact_college']);
		}
	}