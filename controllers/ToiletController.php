<?php
	class ToiletController {
		public function skillViewToilet(){
			$skillResponse = new SkillResponse;
			$skillResponse->addQuickReplies((new QuickReply("다른추천"))->setBlockID("5c25bca65f38dd44d86a098b"));
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));

			$skillResponse->addResponseComponent(new SimpleText("혼밥요정👼 단무지 소! 환! 🔯📡"));
			$skillResponse->addResponseComponent(new SimpleText(
				"화장실에서 빠르고 조용하게 한 끼를 해결하는 혼밥족에게 단무지는 씹는 소리가 크기 때문에 최대의 적이라고 볼 수 있습니다." . "\n\n" .
				"아싸 여러분이 마음껏 소리 내며 식사할 수 있는 최적의 혼밥 장소를 추천해드립니다!"
			));
			$skillResponse->addResponseComponent($this->getRandomToiletMessageCard());

			return json_encode($skillResponse->render());
		}
		public function skillViewToiletMore(){
			$skillResponse = new SkillResponse;
			$skillResponse->addQuickReplies((new QuickReply("다른추천"))->setBlockID("5c25bca65f38dd44d86a098b"));
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));

			$skillResponse->addResponseComponent($this->getRandomToiletMessageCard());

			return json_encode($skillResponse->render());
		}

		protected function getRandomToiletMessageCard(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$building = ToiletBuilding::GET_RANDOM_ITEM();
			$place = $building->getRandomFloor();

			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail)));
			$basicCard->title =
				"👼 단무지가 추천하는 혼밥 🚽💩🚰" . "\n\n" .
				"🍱 【 " . $building->title . " " . $place->floor . " 화장실 】 🍙" . "\n\n" .
				"혼밥은 부끄러운게 아닙니다❗" . "\n" .
				"아싸는 부끄러운게 아닙니다❗" . "\n" .
				"자매품, 오이피클 🥒"
			;

			return $basicCard;
		}
	}