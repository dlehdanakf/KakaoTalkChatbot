<?php
	class RedirectController {
		public function facebookAppRedirect($fb_page){
			$routes = [
				'official' => [
					'mobile' => 'fb://page/245197089485141',
					'android' => 'https://web.facebook.com/konkuk.meal',
					'ios' => 'https://web.facebook.com/konkuk.meal'
				],
				'kunnect' => [
					'mobile' => 'fb://page/1967018396922579',
					'android' => 'https://www.facebook.com/kunnectTimetable',
					'ios' => 'https://www.facebook.com/kunnectTimetable'
				],
				'kung' => [
					'mobile' => 'fb://page/744284529048284',
					'android' => 'https://www.facebook.com/konkukkukung',
					'ios' => 'https://www.facebook.com/konkukkukung'
				],
				'press' => [
					'mobile' => 'fb://page/1691499987751546',
					'android' => 'https://www.facebook.com/kkpressb',
					'ios' => 'https://www.facebook.com/kkpressb'
				]
			];

			if(!isset($routes[$fb_page]))
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			return B::VIEW()->render('redirect.facebook.html', [
				'fb_page' => $routes[$fb_page]
			]);
		}
		public function kakaoMapRedirect(){
			if(!B::PARAMETER_CHECK(['a'], true))
				throw new Exception("필수 파라미터 없음 - a");

			try {
				$affiliate = new Affiliate($_REQUEST['a']);

				if(!$affiliate->map_x || !$affiliate->map_y)
					throw new Exception($affiliate->title . " 업체의 지도 위치가 등록되어있지 않습니다.");

				return B::VIEW()->render('redirect.kakaomap.html', [
					'kakao_map' => [
						'mobile' => 'daummaps://look?p=' . $affiliate->map_y . ',' . $affiliate->map_x,
						'web' => 'http://map.daum.net/link/map/' . implode(',', [$affiliate->title, $affiliate->map_y, $affiliate->map_x])
					]
				]);
			} catch(ModelNotFoundException $e) {
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();
			}
		}
	}