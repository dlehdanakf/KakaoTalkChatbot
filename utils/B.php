<?php
	class B {
		private static $ENVIRONMENT;
		private static $DB;

		public static function LOAD_CONFIG($filename = ".env"){
			self::$ENVIRONMENT = new stdClass();
			if(!file_exists($filename)){
				return false;
			}
			$config_file = fopen($filename, 'r');
			if(!$config_file){
				return false;
			}
			while(($buffer = fgets($config_file)) !== false){
				$arr = explode('=', trim($buffer));
				if(isset($arr[0]) && isset($arr[1])){
					$key = $arr[0];
					$val = $arr[1];
					self::$ENVIRONMENT->$key = $val;
				}
			}
			return true;
		}

		public static function OPEN_DB(){
			self::$DB = new PDO(
				"mysql:host=" . self::$ENVIRONMENT->DB_HOST . ";dbname=" . self::$ENVIRONMENT->DB_DATABASE . ";port=" . self::$ENVIRONMENT->DB_PORT,
				self::$ENVIRONMENT->DB_USERNAME,
				self::$ENVIRONMENT->DB_PASSWORD,
				[
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
				]
			);
			self::$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		public static function CLOSE_DB(){
			self::$DB = null;
		}

		/**
		 *	@return PDO
		 */
		public static function DB(){
			return self::$DB;
		}

		/**
		 * @param array $e
		 * @param bool $z
		 * @return bool
		 * @throws Exception
		 */
		public static function PARAMETER_CHECK($e = [], $z = false){
			for($i = 0; $i < count($e); $i++){
				if(!isset($_REQUEST[$e[$i]])){
					if($z){
						return false;
					} else {
						throw new Exception("호출 파라미터 오류 - " . $e[$i]);
					}
				}
			}

			return true;
		}

		public static function VALIDATE_SKILL_REQUEST_BODY($params = []){
			$return_array = [
				'user' => '',
				'utterance' => '',
				'params' => []
			];

			/** 0. 사용자 정보 확인 */
			try {
				self::PARAMETER_CHECK(['userRequest']);

				$return_array['user'] = $_REQUEST['userRequest']['user']['properties']['plusfriendUserKey'];
				$return_array['utterance'] = $_REQUEST['userRequest']['utterance'];
			} catch(Exception $e) {
				if(self::$ENVIRONMENT->MODE === 'TEST'){
					$return_array['user'] = 'TEST';
				} else {
					throw new Exception("Skill 형식을 갖추지 못했습니다 / " . $e->getMessage());
				}
			}

			/** 1. 액션 정보 확인 */
			try {
				self::PARAMETER_CHECK(['action']);

				foreach($params as $i){
					if(!isset($_REQUEST['action']['params'][$i])){
						throw new Exception("Skill Entry 오류 - " . $params[$i]);
					}

					$return_array['params'][$i] = $_REQUEST['action']['params'][$i];
				}
			} catch(Exception $e) {
				throw new Exception("Skill 형식을 갖추지 못했습니다 / " . $e->getMessage());
			}

			return $return_array;
		}
	}