<?php
	use TheIconic\Tracking\GoogleAnalytics\Analytics;

	class RouterFilter {
		public function beforeSkillResponse(){
			header('Content-Type: application/json');
			if(!B::GET_GA_TOKEN())
				return;

			try {
				$requestBody = B::VALIDATE_SKILL_REQUEST_BODY();
				$user_key = $requestBody['user'];
				if(strtoupper($user_key) == 'TEST' || strlen((String) $user_key) < 1)
					return;

				try {
					$member = Member::CREATE_BY_KEY($user_key);
				} catch(ModelNotFoundException $e) {
					$member = new Member;
					$member->user_key = $user_key;

					$member->save();
				}

				if($member === null)
					return;

				$url = $_SERVER['REQUEST_URI'];
				if(count($requestBody['all_params']) > 0)
					$url = $url . '?' . http_build_query($requestBody['all_params']);

				$analytics = new Analytics(true);
				$analytics
					->setProtocolVersion('1')
					->setTrackingId(B::GET_GA_TOKEN())
					->setClientId($member->user_key)
					->setDocumentPath($url)
					->setIpOverride($_SERVER['REMOTE_ADDR']);

				$analytics->sendPageview();
			} catch(Exception $e){
				throw $e;
			}
		}
	}