<?php
	abstract class BasicModel {
		public $id;
		public $register_date;

		static public function GET_LIST(){
			function fromCamelCase($str) {
				$str[0] = strtolower($str[0]);
				$func = create_function('$c', 'return "_" . strtolower($c[1]);');

				return preg_replace_callback('/([A-Z])/', $func, $str);
			}

			$class = get_called_class();
			$table = fromCamelCase($class);

			$query = B::DB()->prepare("SELECT id FROM $table ORDER BY id DESC");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new $class($v));
			}

			return $return_array;
		}

		public function __construct($id = 0){
			if($id < 1)
				return;

			$this->id = $id;
			$this->fetchDataFromDB();
		}
		abstract public function save();
		public function getBasicInformation(){
			return get_object_vars($this);
		}

		protected function fetchDataFromDB(){
			$table = $this->fromCamelCase(get_class($this));

			$pdo = B::DB();
			$query = $pdo->prepare("SELECT * FROM $table WHERE id = :i");
			$query->execute([
				':i' => $this->id
			]);

			if($query->rowCount() < 1)
				throw new ModelNotFoundException(get_class($this) . " 객체를 찾을 수 없습니다. id - " . $this->id);

			$result = $query->fetch(PDO::FETCH_ASSOC);
			foreach($result as $i => $v){
				$this->$i = $v;
			}
		}

		private function fromCamelCase($string) {
			return strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $string));
		}
		private function toCamelCase($str, $capitalise_first_char = false) {
			if($capitalise_first_char) {
				$str[0] = strtoupper($str[0]);
			}
			$func = create_function('$c', 'return strtoupper($c[1]);');
			return preg_replace_callback('/_([a-z])/', $func, $str);
		}
	}