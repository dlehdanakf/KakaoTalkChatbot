<?php
	class DeliveryController {
		public function skillViewDeliveryGroups(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"교내에서 배달주문할 땐?" . "\n\n" .
				"건학정식과대학생활 배달음식 주문하기!"
			));

			$carousel = new Carousel;
			$groups = DeliveryGroup::GET_ORDERED_LIST();
			if(count($groups) < 1)
				throw new Exception("식당 그룹을 가져오는데 오류가 발생했습니다.\n잠시 후 다시시도 부탁드려요 ㅠㅠ");

			foreach($groups as $group){
				$basicCard = new BasicCard;
				$basicCard->title = $group->title;
				$basicCard->description = $group->description;

				$basicCard->setThumbnail(new Thumbnail($temporary_thumbnail));
				$basicCard->addButton((new Button("식당목록"))->setWebLinkUrl("https://m.naver.com"));

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewDeliveryList(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"우리학교 주변 【 분식 】 배달업체" . "\n\n" .
				"모르겠다"
			));

			$carousel = new Carousel;
		}

		public function adminViewDeliveryGroupList(){
			$groups = DeliveryGroup::GET_LIST();

			return $this->adminView()->render('admin.delivery.groups.html', [
				'sub_title' => "배달업체 그룹 목록",
				'active_title' => "배달업체 그룹 목록",
				'group_list' => $groups
			]);
		}
		public function adminViewDeliveryGroupAdd(){
			return $this->adminView()->render('admin.delivery.groups.add.html', [
				'sub_title' => "배달업체 그룹 추가",
				'active_title' => "배달업체 그룹 추가"
			]);
		}
		public function adminViewDeliveryGroupInfo($group_id){
			$group = new DeliveryGroup($group_id);

			return $this->adminView()->render('admin.delivery.groups.edit.html', [
				'sub_title' => "배달업체 그룹 정보",
				'active_title' => "배달업체 그룹 목록",
				'group' => $group,
				'thumbnail' => $group->getThumbnail()
			]);
		}

		public function processAddDeliveryGroup(){
			B::PARAMETER_CHECK(['title', 'description', 'label', 'thumbnail', 'priority']);

			$group = new DeliveryGroup;
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];
			$group->priority = intval($_REQUEST['priority']);

			if(intval($_REQUEST['thumbnail']) > 0) {
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			}

			/***
			 *	TODO - label 칼럼 중복 검사
			 */

			$group->save();

			header('Location: /admin/delivery/groups');
		}

		/**
		 * @return Twig_Environment
		 */
		protected function adminView(){
			$twig = B::VIEW();
			$twig->addGlobal('sub_nav', [
				[
					"href" => "/admin/delivery/add",
					"label" => "배달업체 추가"
				], [
					"href" => "/admin/delivery",
					"label" => "배달업체 목록"
				], [
					"href" => "/admin/delivery/groups/add",
					"label" => "배달업체 그룹 추가"
				], [
					"href" => "/admin/delivery/groups",
					"label" => "배달업체 그룹 목록"
				]
			]);

			return $twig;
		}
	}