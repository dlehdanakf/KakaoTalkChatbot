<?php
	class DeliveryController {
		public function skillViewDeliveryGroups(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"교내에서 배달주문할 땐?" . "\n\n" .
				"건학정식과대학생활 배달음식 주문하기!"
			));

			$carousel = new Carousel;
			$groups = DeliveryGroup::GET_ORDERED_LIST();
			if(count($groups) < 1)
				throw new Exception("식당 그룹을 가져오는데 오류가 발생했습니다.\n잠시 후 다시시도 부탁드려요 ㅠㅠ");

			foreach($groups as $group){
				$basicCard = new BasicCard;
				$basicCard->title = $group->title;
				$basicCard->description = $group->description;

				$basicCard->setThumbnail(new Thumbnail($temporary_thumbnail));
				$basicCard->addButton((new Button("식당목록"))->setWebLinkUrl("https://m.naver.com"));

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
	}