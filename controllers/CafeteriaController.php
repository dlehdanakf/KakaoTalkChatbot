<?php
	class CafeteriaController {
		public function skillViewCafeteriaList(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";
			$skillResponse = new SkillResponse;

			/** 0. 안내멘트 추가 */
			$skillResponse->addResponseComponent(new SimpleText(
				$this->getDateFormat(date("Y-m-d")) . " 오늘의 학식메뉴를 알려드립니다." . "\n\n" .
				"👉 다음 목록에서 식당을 선택하세요." . "\n" .
				"👉 식단정보 출처 : (주)BABLABS"
			));

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
						date("Y-m-d") . " " .
						$cafeteria->title . " 식단 알려줘"
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