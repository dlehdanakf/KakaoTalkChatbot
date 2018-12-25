<?php
	class ScheduleController {
		public function skillViewList(){
			$skillResponse = new SkillResponse;
			$carousel = new Carousel;
			$quickReplies = [
				[ "종강일 계산하기", "종강일 까지 얼마나 남았어?" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			$year = 2018;
			$months = [1, 2, 3, 4, 5, 6];
			foreach($months as $month){
				$simpleText = new SimpleText("");

				$simpleText->text = $this->getCardTitle($year, $month) . "\n";
				$simpleText->text .= "━━━━━━━━━━━━━━━━";

				$schedules = SchoolCalendar::GET_ORDERED_LIST($year, $month);
				foreach($schedules as $i => $schedule){
					if($i > 0)
						$simpleText->text .= "\n";

					$simpleText->text .= "• " . $this->getCardDay($schedule->schedule) . $schedule->title;
				}

				$carousel->addCard($simpleText);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}

		protected function getCardTitle($year, $month){
			return $year . ($month < 10 ? "년 0" : "년 ") . $month . "월";
		}
		protected function getCardDay($schedule){
			$weekList = ["월", "화", "수", "목", "금", "토", "일"];
			$week = date("w", strtotime($schedule));
			$month = date("m", strtotime($schedule));
			$date = date("d", strtotime($schedule));

			return $month . "." . $date . "(" . $weekList[$week] . ") ";
		}
	}