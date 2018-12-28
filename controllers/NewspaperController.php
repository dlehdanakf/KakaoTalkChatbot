<?php
	class NewspaperController {
		public function skillViewNews(){
			$skillResponse = new SkillResponse;
			$quickReplies = [
				[ "더보기", "건대신문 최신기사 2면 보여줘" ],
				[ "메인으로", "메인으로 돌아가기" ]
			];
			foreach($quickReplies as $quickReply){
				$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
			}

			try {
				$listCard = $this->retchLatestNews(1);
				$skillResponse->addResponseComponent($listCard);
			} catch(FeedException $e) {
				throw new Exception("건대신문 최신기사를 받아오던 도중 오류가 발생했습니다.");
			}

			return json_encode($skillResponse->render());
		}
		public function skillViewNewsMore(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['sys_number']);
			$page = json_decode($requestBody['params']['sys_number'], true)["amount"];

			$skillResponse = new SkillResponse;
			$quickReplies = [
				[ "메인으로", "메인으로 돌아가기" ]
			];

			if($page < 1 || $page > 5){
				$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));
				$skillResponse->addResponseComponent((new SimpleText($page)));
				return json_encode($skillResponse->render());
			}

			try {
				if($page < 5){
					$skillResponse->addQuickReplies((new QuickReply("더보기"))->setMessageText("건대신문 최신기사 " . $page . "면 보여줘"));
				}
				foreach($quickReplies as $quickReply){
					$skillResponse->addQuickReplies((new QuickReply($quickReply[0]))->setMessageText($quickReply[1]));
				}

				$listCard = $this->retchLatestNews($page);
				$skillResponse->addResponseComponent($listCard);
			} catch(FeedException $e) {
				throw new Exception("건대신문 최신기사를 받아오던 도중 오류가 발생했습니다.");
			}

			return json_encode($skillResponse->render());
		}

		/**
		 * @param int $page
		 * @param int $count
		 * @return ListCard
		 * @throws FeedException
		 */
		protected function retchLatestNews($page = 1, $count = 5){
			$popkon_url = 'http://popkon.konkuk.ac.kr/rss/allArticle.xml';
			$rss = Feed::loadRss($popkon_url);

			$listCard = new ListCard;

			/** 0. Add ListCard Header */
			$listCardHeader = new ListItem;
			$listCardHeader->title = $page == 1 ? '건대신문 최신기사' : '건대신문 최신기사 (' . $page . '면)';
			$listCardHeader->imageUrl = 'http://kung.kr/files/attach/images/247/689/026/006/706e2ebd610ca0c24673d3b0b27f1f15.jpg';

			/** 1. Add Latest News */
			$start = ( $page - 1 ) * $count;
			$end = $page * $count - 1;
			$index = 0;
			foreach($rss->item as $item){
				if($index < $start || $index > $end) {
					$index += 1;
					continue;
				}

				$listCardItem = new ListItem;
				$listCardItem->title = (string) $item->title;
				$listCardItem->description = (string) $item->author;

				$link = new Link;
				$link->web = (string) $item->link;
				$listCardItem->setLink($link);

				$openGraph = $this->parseOpenGraph($link->web);
				if($openGraph['image'] && $openGraph['image'] != 'http://popkon.konkuk.ac.kr/image2006/logo.jpg')
					$listCardItem->imageUrl = $openGraph['image'];

				$listCard->addListItem($listCardItem);
				$index += 1;
			}

			/** 2. Add ListCard Footer */
			$button = new Button('공식 홈페이지');
			$button->setWebLinkUrl('http://popkon.konkuk.ac.kr');

			/** 3. Attach header and footer to listCard */
			$listCard->setHeader($listCardHeader);
//			$listCard->addButton($button);

			return $listCard;
		}
		protected function parseOpenGraph($url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

			$html= curl_exec($ch);
			curl_close($ch);

			$openGraph = OpenGraphParser::parse($html);

			return [
				'host' => parse_url($url, PHP_URL_HOST),
				'title' => $openGraph['og']['og:title'],
				'description' => $openGraph['og']['og:description'],
				'image' => $openGraph['og']['og:image']
			];
		}
	}