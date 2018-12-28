<?php
	class ToiletController {
		public function skillViewToilet(){
			$skillResponse = new SkillResponse;
			$quickReplies = [
				[ "다시추천", "다른 화장실 추천해줘" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			$skillResponse->addResponseComponent(new SimpleText("혼밥요정🧚 단무지 소! 환! 🔯🧚‍♀"));
			$skillResponse->addResponseComponent(new SimpleText(
				"화장실에서 빠르고 조용하게 한 끼를 해결하는 혼밥족에게 단무지는 씹는 소리가 크기 때문에 최대의 적이라고 볼 수 있습니다." . "\n\n" .
				"아싸 여러분이 마음껏 소리 내며 식사할 수 있는 최적의 혼밥 장소를 추천해드립니다!"
			));

			return json_encode($skillResponse->render());
		}
		public function skillViewToiletMore(){

		}
	}