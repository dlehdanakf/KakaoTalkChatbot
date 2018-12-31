<?php
	class InviteEventWinner {
		const STATUS_SELECTED = 1;
		const STATUS_CANCELED = 2;

		public $event_id;
		public $member_id;
		public $status;
		public $register_date;

		public static function GET_WINNER(InviteEvent $event){
			$query = B::DB()->prepare("SELECT * FROM invite_event_winner WHERE event_id = :e");
			$query->execute([
				':e' => $event->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $item){
				$instance = new stdClass;
				$instance->member = new Member(intval($item['member_id']));
				foreach($item as $i => $v){
					$instance->$i = $v;
				}

				array_push($return_array, $instance);
			}

			return $return_array;
		}

		public function __construct(InviteEvent $event = null, Member $member = null){
			if($event === null || $member === null)
				return;

			$this->event_id = $event->id;
			$this->member_id = $member->id;
			$this->status = self::STATUS_SELECTED;
		}

		public function save(){
			if($this->isDuplicatedKey())
				throw new Exception("이미 이벤트에 당첨/취소된 사람은 중복하여 추가할 수 없습니다.");

			$query = B::DB()->prepare("INSERT INTO invite_event_winner (event_id, member_id, status) VALUE (:e, :m, :c)");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id,
				':c' => $this->status
			]);
		}
		public function delete(){
			$query = B::DB()->prepare("DELETE FROM invite_event_winner WHERE event_id = :e AND member_id = :m");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id
			]);
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT * FROM invite_event_winner WHERE event_id = :e AND member_id = :m");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
	}