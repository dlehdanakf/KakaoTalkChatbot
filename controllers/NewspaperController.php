<?php
	class NewspaperController {
		public function skillViewNews(){
			$popkon_url = 'http://popkon.konkuk.ac.kr/rss/allArticle.xml';
			$rss = Feed::loadRss($popkon_url);

			$response = new SkillResponse;
			$listCard = new ListCard;

			/** 0. Add ListCard Header */
			$listCardHeader = new ListItem;
			$listCardHeader->title = '건대신문';
			$listCardHeader->description = '건국대학교 신문';

			/** 1. Add Latest News */
			$count = 0;
			foreach($rss->item as $item){
				if($count >= 5) continue;

				$listCardItem = new ListItem;
				$listCardItem->title = (string) $item->title;
				$listCardItem->description = (string) $item->author;
				$listCardItem->link = (string) $item->link;

				$listCard->addListItem($listCardItem);
				$count += 1;
			}

			/** 2. Add ListCard Footer */
			$button = new Button('공식 홈페이지');
			$button->setWebLinkUrl('http://popkon.konkuk.ac.kr');

			/** 3. Attach header and footer to listCard */
			$listCard->setHeader($listCardHeader);
			$listCard->addButton($button);

			$response->addResponseComponent($listCard);

			return json_encode($response->render());
		}
	}