<?php
	use Fusonic\OpenGraph\Consumer;

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

				$link = new Link;
				$link->web = (string) $item->link;
				$listCardItem->setLink($link);

				$openGraph = $this->parseOpenGraph($link->web);
				if($openGraph['image'] && $openGraph['image'] != 'http://popkon.konkuk.ac.kr/image2006/logo.jpg')
					$listCardItem->imageUrl = $openGraph['image'];

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