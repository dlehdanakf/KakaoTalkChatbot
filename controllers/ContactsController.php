<?php
	class ContactsController {
		public function skillViewList(){
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
				[ "찾는 부서가 없습니다", "학교 전화번호부 사이트 알려줘" ],
				[ "정보오류 제보", "전화번호부 오류 제보하기" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			return json_encode($skillResponse->render());
		}
		public function skillViewDetail(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['contact_keyword', 'contact_college']);

			$skillResponse = new SkillResponse;
			$carousel = new Carousel;
			$contactDepartments = ContactCollege::CREATE_BY_TITLE($requestBody['params']['contact_college'])->getAllDepartments();
			$quickReplies = [
				[ "찾는 부서가 없습니다", "학교 전화번호부 사이트 알려줘" ],
				[ "정보오류 제보", "전화번호부 오류 제보하기" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			if(count($contactDepartments) < 1){
				/**
				 *	연락처 목록이 없을 경우
				 */
				$skillResponse->addResponseComponent(
					new SimpleText("'" . $requestBody['params']['contact_college'] . "'에 대해 검색된 전화번호가 없습니다.")
				);

				return json_encode($skillResponse->render());
			}

			/**
			 *	학과 리스트를 3개씩 묶어서 새로운 Array 를 생성
			 */
			$contactGroupedDepartments = [];
			$temp = [];
			foreach($contactDepartments as $i => $contactDepartment){
				array_push($temp, $contactDepartment);

				if(($i + 1) % 3 == 0 || $i == count($contactDepartments) - 1){
					array_push($contactGroupedDepartments, $temp);
					$temp = [];
				}
			}

			foreach($contactGroupedDepartments as $contactGroupedDepartment){
				$basicCard = new BasicCard;
				$title = '';

				foreach($contactGroupedDepartment as $i => $contactDepartment){
					if($i !== 0) $title .= "\n";
					$contact = $this->getLastPhoneNumber($contactDepartment->contact);
					$title .= ( "• " . $contactDepartment->title . " (☎️ " . $contact . ")" );

					$basicCard->addButton(
						(new Button($contactDepartment->title . " 전화하기"))->setPhoneNumber($contactDepartment->contact)
					);
				}

				$basicCard->title = $title;
				$basicCard->description = ' ';

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent(new SimpleText(
				"🏢 [ " . $requestBody['params']['contact_college'] . " ] 전화번호입니다." . "\n\n" .
				"☎️ 내선번호 국번 안내️" . " \n" .
				"👉 3, 4000번대 ▶ 02-450" . " \n" .
				"👉 6000번대 ▶ 02-2049" . " \n" .
				"【 ex) 02-450-4071 】" . " \n\n" .
				"🛠️ 제공된 전회번호 정보가 잘못되었을 경우 제보부탁드려요!!"
			));
			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}

		protected function getLastPhoneNumber($phone){
			$arr = explode('-', $phone);
			return ($arr[count($arr) - 1]);
		}
	}