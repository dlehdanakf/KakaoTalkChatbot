<?php
	class AdminController {
		public function viewIndexPage(){
			return B::VIEW()->render('admin.index.html');
		}

		public function adminViewServiceThumbnail(){
			$cardList = [
				[ 'title' => '학식', 'thumbnail' => $this->getCardThumbnail(1) ],
				[ 'title' => '제휴업체', 'thumbnail' => $this->getCardThumbnail(2) ],
				[ 'title' => '이벤트', 'thumbnail' => $this->getCardThumbnail(3) ],
				[ 'title' => '학사일정', 'thumbnail' => $this->getCardThumbnail(4) ],
				[ 'title' => '건대신문', 'thumbnail' => $this->getCardThumbnail(5) ]
			];

			return $this->adminView()->render('admin.service.thumbnail.html', [
				'sub_title' => "메인섹션 썸네일 설정",
				'active_title' => "메인섹션 썸네일 설정",
				'card_list' => $cardList
			]);
		}

		public function processUpdateServiceThumbnail(){
			B::PARAMETER_CHECK(['thumbnail-1', 'thumbnail-2', 'thumbnail-3', 'thumbnail-4', 'thumbnail-5']);

			for($i = 1; $i < 6; $i++){
				if(intval($_REQUEST['thumbnail-' . $i]) > 0)
					$this->setCardThumbnail($i, Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail-' . $i]));
				else
					$this->setCardThumbnail($i, null);
			}

			header('Location: /admin/service/thumbnail');
		}

		public function apiGetMapCoordinate(){
			if(!B::PARAMETER_CHECK(['q'], true)){
				B::DIE_MESSAGE(403, "파라미터 에러 - q");
			}

			$snoopy = new Snoopy;
			$snoopy->_httpmethod = 'GET';
			$snoopy->read_timeout = 3;
			$snoopy->rawheaders['Authorization'] = 'KakaoAK ' . B::GET_KAKAO_TOKEN();
			$snoopy->submit('https://dapi.kakao.com/v2/local/search/address.json', [
				'query' => $_REQUEST['q'],
				'size' => 15
			]);

			$result = $snoopy->results;
			$json_result = json_decode($result, true);
			if(json_last_error() !== JSON_ERROR_NONE)
				B::DIE_MESSAGE(500, "카카오톡 지도검색 API에 문제가 있습니다.");

			B::DIE_MESSAGE(200,'성공', [
				'location' => $json_result['documents']
			]);
		}

		/**
		 * @return Twig_Environment
		 */
		protected function adminView(){
			$twig = B::VIEW();
			$twig->addGlobal('sub_section_title', '서비스 관리');
			$twig->addGlobal('sub_nav', [
				[
					"href" => "/admin/service/thumbnail",
					"label" => "메인섹션 썸네일 설정"
				]
			]);

			return $twig;
		}
		protected function getCardThumbnail($int){
			try {
				$key = 'main_card_' . $int;
				$value = B::GET_SETTING($key);

				return Attachment::CREATE_BY_MYSQLID($value);
			} catch(Exception $e) {
				return null;
			}
		}
		protected function setCardThumbnail($int, Attachment $attachment = null){
			$key = 'main_card_' . $int;

			$applicationSetting = new ApplicationSetting;
			$applicationSetting->sys_key = $key;
			$applicationSetting->sys_value = $attachment === null ? 0 : $attachment->id;

			$applicationSetting->save();
		}
	}