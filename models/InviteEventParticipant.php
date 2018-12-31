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

		public function __construct(InviteEvent $event = null, Member $member = null){
			if($event === null || $member === null)
				return;

			$this->event_id = $event->id;
			$this->member_id = $member->id;
		}

		public function save(){
			$query = B::DB()->prepare("INSERT INTO invite_event_participant (event_id, member_id, content) VALUE (:e, :m, :c)");
			$query->execute([
				':e' => $this->event_id,
				':m' => $this->member_id,
				':c' => $this->content
			]);
		}
	}