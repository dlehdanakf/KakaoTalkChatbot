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
			$skillResponse->addResponseComponent(new SimpleText("부서 목록에서 선택하세요!"));

			$carousel = new Carousel;
			$contactGroups = ContactGroup::GET_ORDERED_LIST();
			foreach($contactGroups as $contactGroup){
				$basicCard = new BasicCard;
				$basicCard->title = $contactGroup->title;

				$contactColleges = $contactGroup->getAllColleges();
				foreach($contactColleges as $contactCollege){
					$button = new Button($contactCollege->title);
					$button->setMessageText($contactCollege->title . " 전화번호 알려줘");

					$basicCard->addButton($button);
				}

				$carousel->addCard($basicCard);
			}
			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewDetail(){
//			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['contact_keyword', 'contact_college']);

			$skillResponse = new SkillResponse;
			$simpleMessage = new SimpleText("Hello World!!@!@");

			$skillResponse->addResponseComponent($simpleMessage);

			return json_encode($skillResponse->render());
		}
	}