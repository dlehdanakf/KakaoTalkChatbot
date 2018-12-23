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
				$basicCard->description = $contactGroup->description ? $contactGroup->description : null;

				$contactColleges = $contactGroup->getAllColleges();
				foreach($contactColleges as $contactCollege){
					$button = new Button($contactCollege->title);
					$button->setMessageText($contactCollege->title . " 전화번호부 보여줘");

					$basicCard->addButton($button);
				}

				$carousel->addCard($basicCard);
			}
			$skillResponse->addResponseComponent($carousel);

			$quickReplies = [
				[ "찾는 부서가 없습니다", "교내 전화번호부 사이트 주소 알려줘" ],
				[ "정보오류 신고", "전화번호부 오류 신고할래" ],
				[ "메인으로", "메인으로" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

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