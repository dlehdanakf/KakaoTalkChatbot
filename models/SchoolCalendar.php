<?php
	class SchoolCalendar extends BasicModel {
		public $title;
		public $schedule;

		static public function GET_ORDERED_LIST($year, $month){
			$query = B::DB()->prepare("SELECT title, schedule, DATE(schedule) AS `date` FROM school_calendar WHERE YEAR(schedule) = :y AND MONTH(schedule) = :m ORDER BY `date` ASC");
			$query->execute([
				':y' => $year,
				':m' => $month
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $v){
				$instance = new self;
				foreach($v as $i => $k){
					$instance->$i = $k;
				}

				array_push($return_array, $instance);
			}

			return $return_array;
		}

		public function save() {
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO school_calendar (title, schedule) VALUE (:t, :s)");
			$query->execute([
				':t' => $this->title,
				':s' => $this->schedule
			]);

			$this->id = $pdo->lastInsertId();
		}
	}