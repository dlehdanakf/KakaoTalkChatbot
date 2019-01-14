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
				if(strtoupper($user_key) == 'TEST')
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

				$analytics = new Analytics(true);
				$analytics
					->setProtocolVersion('1')
					->setTrackingId(B::GET_GA_TOKEN())
					->setClientId($member->user_key)
					->setDocumentPath($_SERVER['REQUEST_URI'])
					->setIpOverride($_SERVER['REMOTE_ADDR']);

				$analytics->sendPageview();
			} catch(Exception $e){
				throw $e;
			}
		}
	}