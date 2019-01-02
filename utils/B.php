<?php
	class B {
		private static $ENVIRONMENT;
		private static $SETTING;
		private static $DB;

		public static function LOAD_CONFIG($filename = "../.env"){
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

		public static function LOAD_SETTING(){
			self::$SETTING = new stdClass();

			$query = self::DB()->prepare("SELECT sys_key, sys_value FROM application_setting");
			$query->execute();

			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $item){
				$key = $item['sys_key'];
				self::$SETTING->$key = $item['sys_value'];
			}
		}
		public static function GET_SETTING($e){
			if(!isset(self::$SETTING->$e))
				throw new Exception($e . " 값을 Application Setting 에서 찾을 수 없습니다.");

			return self::$SETTING->$e;
		}
		public static function GET_ACCESS_TOKEN(){
			return self::$ENVIRONMENT->ACCESS_TOKEN;
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
			$json = file_get_contents('php://input');
			$obj = json_decode($json, true);

			$return_array = [
				'user' => '',
				'utterance' => '',
				'params' => []
			];

			/** 0. 사용자 정보 확인 */
			try {
				if(!isset($obj['userRequest'])){
					throw new Exception("php://input 에서 json 형식의 userRequest 값을 찾을 수 없음");
				}

				$return_array['user'] = $obj['userRequest']['user']['properties']['plusfriendUserKey'];
				$return_array['utterance'] = $obj['userRequest']['utterance'];
			} catch(Exception $e) {
				if(self::$ENVIRONMENT->MODE === 'TEST'){
					$return_array['user'] = 'TEST';
				} else {
					throw new Exception("Skill 형식을 갖추지 못했습니다 / " . $e->getMessage());
				}
			}

			/** 1. 액션 정보 확인 */
			try {
				if(!isset($obj['action'])){
					throw new Exception("php://input 에서 json 형식의 action 값을 찾을 수 없음");
				}

				foreach($params as $i){
					if(!isset($obj['action']['params'][$i])){
						throw new Exception("Skill Entry 오류 - " . $i);
					}

					$return_array['params'][$i] = $obj['action']['params'][$i];
				}
			} catch(Exception $e) {
				throw new Exception("Skill 형식을 갖추지 못했습니다 / " . $e->getMessage());
			}

			return $return_array;
		}

		public static function VIEW(){
			$loader = new Twig_Loader_Filesystem(__DIR__ . '/../views');
			$twig = new Twig_Environment($loader, []);

			$twig->addGlobal('server_host', $_SERVER['HTTP_HOST']);

			$twig->addFunction(new Twig_SimpleFunction('image', function($name){
				return '/assets/image/' . $name;
			}));
			$twig->addFunction(new Twig_SimpleFunction('css', function($name){
				return '/assets/css/' . $name;
			}));
			$twig->addFunction(new Twig_SimpleFunction('js', function($name){
				return '/assets/js/' . $name;
			}));

			$twig->addFunction(new Twig_SimpleFunction('date_format', function($date, $format = 'Y-m-d'){
				return date($format, strtotime($date));
			}));

			$twig->addFunction(new Twig_SimpleFunction('form_token', function($lock_to = null) {
				static $csrf;
				if ($csrf === null) {
					$csrf = new AntiCSRF;
				}
				return $csrf->insertToken($lock_to, false);
			}, [
				'is_safe' => [
					'html'
				]
			]));
			return $twig;
		}
	}