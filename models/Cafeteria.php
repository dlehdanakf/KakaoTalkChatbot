<?php
	class Cafeteria extends BasicModel {
		public $title;
		public $serial;
		public $location;
		public $semester_open;
		public $vacation_open;
		public $priority;

		protected $date;
		protected $meals;

		static public function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM cafeteria ORDER BY priority, id DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}
		static public function CREATE_BY_TITLE($title){
			$query = B::DB()->prepare("SELECT * FROM cafeteria WHERE title = :t");
			$query->execute([
				':t' => $title
			]);

			$instance = new self;
			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($instance) . " 객체를 찾을 수 없습니다. title - " . $title);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$instance->$i = $v;
			}

			return $instance;
		}

		public function __construct($id = 0){
			$this->date = null;
			$this->meals = [];

			parent::__construct($id);
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO cafeteria (title, serial, location, semester_open, vacation_open, priority) VALUE (:t, :s, :l, :o, :v, :p)");
			$query->execute([
				':t' => $this->title,
				':s' => $this->serial,
				':l' => $this->location,
				':o' => $this->semester_open,
				':v' => $this->vacation_open,
				':p' => $this->priority
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function renderTodayMeal($date = null){
			if(!$date) $date = date('Y-m-d');
			$this->date = $date;

			$result = $this->fetch($date);
			switch($this->title){
				case "학생회관(아워홈)": $this->processStudentOne($result); break;
				case "학생회관(신세계)": $this->processStudentTwo($result); break;
				case "상허기념도서관": $this->processLibrary($result); break;
				default: $this->processDefault($result);
			}

			return $this->render();
		}

		protected function fetch($date){
			$snoopy = new Snoopy;
			$snoopy->read_timeout = 3;
			$snoopy->rawheaders['Accesstoken'] = B::GET_ACCESS_TOKEN();
			$snoopy->fetch('https://bablabs.com/openapi/v1/campuses/MoCQZdj1hE/stores/' . $this->serial . '?date=' . $date);

			if ($snoopy->status != 200)
				throw new Exception("식단알리미 서버가 응답하지 않습니다.");

			$result = $snoopy->results;
			$json_result = json_decode($result, true);
			if(json_last_error() !== JSON_ERROR_NONE)
				throw new Exception("식단알리미 서버의 응답을 이해할 수 없습니다. - " . $result);
			if($json_result['result']['status_code'] != 200)
				throw new Exception("식단알리미 서버의 응답에 문제가 있습니다. - " . $json_result['result']['message']);

			return $json_result['store']['menus'];
		}
		protected function render(){
			$rtn_str =
				"[ 날짜 ] : " . $this->getFormattedDate() . "\n" .
				"[ 식당 ] : " . $this->getEmoji() . "\n" .
				"(  🍽 : 요리,  👩‍🍳 : 반찬  )" . "\n"
			;

			if(count($this->meals) < 1) {
				$rtn_str .= (
					"\n" .
					"❌ 식당에서 업로드한 식단이 없습니다." . "\n" .
					"👨‍🍳 오늘은 영업일이 아닙니다." . "\n" .
					"\n" .
					"==========" . "\n" .
					"\n" .
					"⚠️ 상기 내용은 컴퓨터가 자동으로 가져온 정보로 부정확할 수 있습니다."
				);

				return $rtn_str;
			}

			foreach($this->meals as $meal){
				$rtn_str .= "\n";
				$rtn_str .= ( "【 " . $meal->corner . " 】" ); $rtn_str .= "\n";
				$rtn_str .= ( $meal->getMainDish() );
				$rtn_str .= ( $meal->getSideDish() );
			}

			$rtn_str .= "\n==========\n";
			$rtn_str .= "\n⚠️ 상기 식단은 식당 운영사정으로 인해 변경될 수 있습니다.";

			return $rtn_str;
		}

		protected function getEmoji(){
			switch($this->title){
				case "학생회관(아워홈)": return "학생회관(지하, 아워홈) 🙋‍♂️👧";
				case "학생회관(신세계)": return "학생회관(1층, 신세계) 🙋‍♀️👦";
				case "상허기념도서관": return "상허도서관(지하, 아워홈) 📚";
				case "교직원식당": return "새천년관(교직원식당) 🏢";
				case "쿨하우스(기숙사)": return "쿨하우스(기숙사) 🏘";
				default: return $this->title;
			}
		}

		private function processStudentOne($e){
			foreach($e as $i => $data){
				$description = $data['description'];
				$name = $data['name'];

				$meal = new CafeteriaMeal;
				$menu_arr = explode(" ", trim($description));
				switch($i){
					case 0:
						$meal->corner = "CORNER 1";
						$meal->main = array_slice($menu_arr, 0, 2);
						$meal->side = array_slice($menu_arr, 2);
						break;
					case 1:
						$meal->corner = "🍱 도시락";
						$meal->main = [ "양은도시락" ];
						$meal->side = [];
						break;
					case 2:
						$meal->corner = "CORNER 2";
						$meal->main = $menu_arr;
						break;
					case 3:
						$meal->corner = "KU and cook";
						$meal->main = array_slice($menu_arr, 0, 1);
						$meal->side = array_slice($menu_arr, 1);
						break;
					case 4:
						$meal->corner = "짜이찌엔 KU";
						switch(count($menu_arr)){
							case 3:
								$meal->main = [
									$menu_arr[0],
									implode(', ', [ $menu_arr[1], $menu_arr[2] ])
								];
								break;
							case 4:
								$meal->main = [
									implode(', ', [ $menu_arr[0], $menu_arr[1] ]),
									implode(', ', [ $menu_arr[2], $menu_arr[3] ])
								];
								break;
							default:
								$meal->main = $menu_arr;
						}
					case (count($data) - 1):
						continue; break;
					default:
						$meal->corner = $name;
						$meal->main = $menu_arr;
						break;
				}

				array_push($this->meals, $meal);
			}
		}
		private function processStudentTwo($e){
			$corners = [];
			foreach($e as $i => $data){
				$description = $data['description'];
				$name = $data['name'];

				$str = trim(preg_replace('/\s+/', ' ', $description));
				if($this->isAlreadyPushed($name, $corners)) continue;

				$meal = new CafeteriaMeal;
				$meal->corner = $name;
				$menu_arr = explode(" ", $str);
				switch($name){
					case '차림(10:30~)':
						$meal->main = array_slice($menu_arr, 0, 1);
						$meal->side = array_slice($menu_arr, 1);
						break;
					case '백반(08:30~)':
						$meal->corner = "백반(아침식사) 🍚🥘🥄";
						$meal->main = array_slice($menu_arr, 0, 1);
						$meal->side = array_slice($menu_arr, 1);
						break;
					case '칸소(10:30~)':
						$menu_arr = explode("\n", trim($description));
						foreach($menu_arr as $v){
							$a = implode(", ", explode(" ", $v));
							array_push($meal->main, $a);
						}
                		break;
//					case '누들(10:30~)':
//					case '뚜르(11:00~)':
//					case '일품(11:00~)':
					default:
						$meal->main = $menu_arr;
						break;
				}

				array_push($corners, $name);
				array_push($this->meals, $meal);
			}
		}
		private function processLibrary($e){
			foreach($e as $i => $data){
				$description = $data['description'];
				$name = $data['name'];

				$str = trim(str_replace(' . ', '*', $description));

				$meal = new CafeteriaMeal;
				$menu_arr = explode(" ", trim($str));
				switch($i){
					case 0:
						$meal->corner = "CORNER 1";
						$meal->main = array_slice($menu_arr, 0, 1);
						$meal->side = array_slice($menu_arr, 1);
						break;
					case 1:
						$meal->corner = "🍱 도시락";
						$meal->main = [ "양은도시락" ];
						$meal->side = [];
						break;
					case 2:
						$meal->corner = "CORNER 2";
						$meal->main = array_slice($menu_arr, 0, 2);
						$meal->side = array_slice($menu_arr, 2);
						break;
					case 3:
						$meal->corner = "CORNER 3";
						$meal->main = $menu_arr;
						break;
					case (count($data) - 1):
						continue; break;
					default:
						$meal->corner = $name;
						$meal->main = $menu_arr;
						break;
				}


				array_push($this->meals, $meal);
			}
		}
		private function processDefault($e){
			$num = ["①", "②", "③", "④", "⑤", "⑥", "⑦", "⑧", "⑨", "⑩"];

			foreach($e as $i => $data){
				$description = $data['description'];
				$name = $data['name'];

				$meal = new CafeteriaMeal;
				$menu_arr = explode(" ", trim($description));

				$meal->corner = $i < count($num) ? "메뉴 " . $num[$i] : null;
				$meal->main = [ $name ];
				$meal->side = $menu_arr;

				array_push($this->meals, $meal);
			}
		}

		private function isAlreadyPushed($e, $c){
			for($x = 0; $x < count($c); $x++)
				if($c[$x] == $e) return true;

			return false;
		}
		protected function getFormattedDate(){
			$e = strtotime($this->date);
			$weekList = ["월", "화", "수", "목", "금", "토", "일"];

			$week = date("w", $e);
			$year = date("Y", $e);
			$month = date("m", $e);
			$date = date("d", $e);

			return $year . "년 " . $month . "월 " . $date . "일(" . $weekList[$week] . ")";
		}
	}