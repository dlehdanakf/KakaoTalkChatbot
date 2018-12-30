<?php
	class CafeteriaController {
		public function skillViewCafeteriaList(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY();
			$skillResponse = new SkillResponse;

			/** 0. 안내멘트 추가 */
			if($requestBody['utterance'] == "오늘의 학식메뉴 알려줘") {
				$skillResponse->addResponseComponent(new SimpleText($this->getDateFormat(date("Y-m-d")) . " 오늘의 학식메뉴를 알려드립니다."));
				$skillResponse->addResponseComponent(new SimpleText("다음 목록에서 학생식당을 선택해주세요."));
			}

			/** 1. 식당리스트 추가 */
			$carousel = new Carousel;
			$cafeterias = Cafeteria::GET_ORDERED_LIST();
			foreach($cafeterias as $cafeteria){
				$basicCard = new BasicCard;
				$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail)));
				$basicCard->title =
					$cafeteria->title . "\n" .
					"🏢 " . $cafeteria->location . "\n" .
					"🕑 " . $cafeteria->semester_open
				;
				$basicCard->addButton(
					(new Button("오늘의 식단 보기"))->setMessageText(
						$cafeteria->title . " 오늘의 식단 알려줘"
					)
				);

				$carousel->addCard($basicCard);
			}
			$skillResponse->addResponseComponent($carousel);

			/** 3. 응답버튼 추가 */
			$quickReplies = [
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			return json_encode($skillResponse->render());
		}
		public function skillViewTodayMeal(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['meal_cafeteria', 'meal_keyword']);
			$skillResponse = new SkillResponse;

			try {
				$cafeteria = Cafeteria::CREATE_BY_TITLE($requestBody['params']['meal_cafeteria']);

				$messageText = $cafeteria->renderTodayMeal();
				$skillResponse->addResponseComponent(new SimpleText($messageText));
			} catch (ModelNotFoundException $e){
				throw new Exception("식당 이름을 채팅봇이 알아들을 수 없습니다.\n다른 이름으로 다시 시도해주세요.");
			}

			$quickReplies = [
				[ "정보오류 제보", "학식메뉴 오류 제보하기" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			$skillResponse->addQuickReplies((new QuickReply("돌아가기"))->setBlockID('5c2778fe5f38dd44d86a0e9b'));
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			return json_encode($skillResponse->render());
		}

		protected function getDateFormat($e){
			$weekList = ["일", "월", "화", "수", "목", "금", "토"];
			$strTime = strtotime($e);

			$year = date("Y", $strTime);
			$month = date("m", $strTime);
			$date = date("d", $strTime);
			$weekNum = date("w", $strTime);

			return $year . "년 " . $month . "월 " . $date . "일(" . $weekList[$weekNum] . ")";
		}
	}