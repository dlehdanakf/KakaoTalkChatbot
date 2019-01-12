<?php
	class CalculatorController {
		public function skillViewDDay(){
			$d_day = B::GET_SETTING('calculator_date');
			$datetime1 = new DateTime($d_day);
			$datetime2 = new DateTime(date("Y-m-d"));

			$diff = $datetime1->diff($datetime2);
			$diffCount = $diff->format('%R%a days');

			if(B::GET_SETTING('calculator_mode') == 'SemesterEnd')
				return $this->skillViewDDayEnd($d_day, $diffCount);
			
			return $this->skillViewDDayStart($d_day, $diffCount);
		}

		protected function skillViewDDayStart($d_day, $diffCount){
			/**
			 *	개강일 디데이
			 */
			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"📆 다음학기 개강일은? 😱💣" . "\n" .
				$this->getDateFormat($d_day) . " 입니다!!"
			));

			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($this->getThumbnailURL($diffCount, 'vacation'))));
			$basicCard->title = (
				"개강까지 앞으로 【 " . abs($diffCount) . "일 】 남았습니다." . "\n\n" .
				"⚠️ 본 계산결과는 행정효력이 없으며 학사일정상 변동될 수 있습니다."
			);

			$skillResponse->addResponseComponent($basicCard);

			$quickReplies = [
				[ "전체 학사일정", "이번학기 학사일정 알려줘" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			return json_encode($skillResponse->render());
		}
		protected function skillViewDDayEnd($d_day, $diffCount){
			/**
			 *	종강일 디데이
			 */
			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"📆 이번학기 종강일은? 😆🎉" . "\n" .
				$this->getDateFormat($d_day) . " 입니다!!"
			));

			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($this->getThumbnailURL($diffCount, 'semester'))));
			$basicCard->title = (
				"종강까지 앞으로 【 " . abs($diffCount) . "일 】 남았습니다." . "\n\n" .
				"⚠️ 본 계산결과는 행정효력이 없으며 학사일정상 변동될 수 있습니다."
			);

			$skillResponse->addResponseComponent($basicCard);

			$quickReplies = [
				[ "전체 학사일정", "이번학기 학사일정 알려줘" ],
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
		protected function getThumbnailURL($diffCount = 0, $mode = 'semester'){
			$type = 'plus';
			if($diffCount <= 0)
				$type = 'minus';

			$rand = (int) random() * 1000 % 100;

			return "http://chatbot.kunnect.net:8080/c?c=" . abs($diffCount) . "&m=$mode&t=$type&r=$rand";
		}
	}