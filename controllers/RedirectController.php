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
	}