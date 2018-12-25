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
				$basicCard = new BasicCard;

				$basicCard->title = $this->getCardTitle($year, $month) . "/n";
				$basicCard->title .= "━━━━━━━━━━━━━━━━";

				$schedules = SchoolCalendar::GET_ORDERED_LIST($year, $month);
				foreach($schedules as $schedule){
					$basicCard->title .= "\n📌 " . $schedule->title;
				}

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}

		protected function getCardTitle($year, $month){
			if($month < 10) return $year . "년 0" . $month . "월";
			return $year . "년 " . $month . "월";
		}
	}