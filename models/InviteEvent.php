<?php
	class InviteEvent extends BasicModel {
		const PROCESS_SELECTION = 0;
		const PROCESS_LOTTERY = 1;
		const PROCESS_ORDERING = 2;

		const STATUS_CLOSED = 1;
		const STATUS_OPEN = 0;

		public $title;
		public $start_date;
		public $end_date;
		public $announce_date;
		public $person;
		public $location;
		public $content;
		public $process_type;
		public $status;

		public static function GET_ORDERED_LIST(){
			$query = B::DB()->prepare("SELECT id FROM invite_event ORDER BY status, id DESC LIMIT 1, 8");
			$query->execute();

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $v){
				array_push($return_array, new self($v));
			}

			return $return_array;
		}

		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("
				INSERT INTO invite_event 
				(title, start_date, end_date, announce_date, person, location, content, process_type, status) 
				VALUE (:t, :s, :e, :a, :p, :l, :c, :t, :u)
			");
			$query->execute([
				':t' => $this->title,
				':s' => $this->start_date,
				':e' => $this->end_date,
				':a' => $this->announce_date,
				':p' => $this->person,
				':l' => $this->location,
				':c' => $this->content,
				':t' => $this->process_type,
				':u' => $this->status
			]);

			$this->id = $pdo->lastInsertId();
		}

		public function makeStatusClosed(){
			$this->status = self::STATUS_CLOSED;
			$this->save();
		}
		public function makeStatusOpen(){
			$this->status = self::STATUS_OPEN;
			$this->save();
		}

		public function addParticipant(Member $member, $content){
			$participant = new InviteEventParticipant;
			$participant->event_id = $this->id;
			$participant->member_id = $member->id;
			$participant->content = $content;

			$participant->save();
		}
		public function getParticipantList(){
			return InviteEventParticipant::GET_PARTICIPANT($this);
		}
		public function getParticipantCount(){
			return InviteEventParticipant::GET_PARTICIPANT_AMOUNT($this);
		}

		public function addWinner(Member $member){
			$winner = new InviteEventWinner;
			$winner->event_id = $this->id;
			$winner->member_id = $member->id;
			$winner->status = InviteEventWinner::STATUS_SELECTED;

			$winner->save();
		}
		public function getWinnerList(){
			return InviteEventWinner::GET_WINNER($this);
		}
	}