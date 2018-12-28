<?php
	class CalculatorController {
		public function skillViewDDay(){
			return $this->skillViewDDayEnd();
		}

		protected function skillViewDDayStart(){
			/**
			 *	개강일 디데이
			 */
		}
		protected function skillViewDDayEnd(){
			/**
			 *	종강일 디데이
			 */
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"📆 이번학기 종강일은? 😆🎉" . "\n" .
				"2018년 12월 14일(금)" . " 입니다!!"
			));

			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail)));
			$basicCard->title = (
				"종강까지 앞으로 【 14일 】 남았습니다." . "\n\n" .
				"⚠️ 본 계산결과는 행정효력이 없으며 학사일정상 변동될 수 있습니다."
			);

			$skillResponse->addResponseComponent($basicCard);

			return json_encode($skillResponse->render());
		}
	}