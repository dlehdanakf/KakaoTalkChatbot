<?php
	class InviteEventParticipant {
		public $event_id;
		public $member_id;
		public $content;
		public $register_date;

		public static function GET_PARTICIPANT_AMOUNT(InviteEvent $event){
			$query = B::DB()->prepare("SELECT COUNT(member_id) AS `amount` FROM invite_event_participant WHERE event_id = :e");
			$query->execute([
				':e' => $event->id
			]);

			return intval($query->fetch(PDO::FETCH_ASSOC)['amount']);
		}
		public static function GET_PARTICIPANT_LIST(InviteEvent $event){
			$query = B::DB()->prepare("SELECT member_id FROM invite_event_participant WHERE event_id = :e");
			$query->execute([
				':e' => $event->id
			]);

			$return_array = [];
			$result = $query->fetchAll(PDO::FETCH_COLUMN);
			foreach($result as $item){
				array_push($return_array, new Member(intval($item)));
			}

			return $return_array;
		}
		public static function GET_PARTICIPANT(InviteEvent $event){
			$query = B::DB()->prepare("SELECT * FROM invite_event_participant WHERE event_id = :e");
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
		}

		public function save(){
			if($this->isDuplicatedKey())
				throw new Exception("이미 이벤트에 신청된 사람은 중복하여 추가할 수 없습니다.");

			$query = B::DB()->prepare("INSERT INTO invite_event_participant (event_id, member_id, content) VALUE (:e, :m, :c)");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id,
				':c' => $this->content
			]);
		}

		protected function isDuplicatedKey(){
			$query = B::DB()->prepare("SELECT * FROM invite_event_participant WHERE event_id = :e AND member_id = :m");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id
			]);

			if($query->rowCount() > 0)
				return true;

			return false;
		}
	}