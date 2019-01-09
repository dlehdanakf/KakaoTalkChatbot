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
				throw new Exception("🛠️ 식당 그룹을 가져오는데 오류가 발생했습니다.");

			foreach($groups as $group){
				$basicCard = new BasicCard;
				$basicCard->title = $group->title;
				$basicCard->description = $group->description;

				$basicCard->setThumbnail(new Thumbnail($temporary_thumbnail));
				$basicCard->addButton((new Button("식당목록"))->setMessageText($group->label . " 배달음식점 목록 보여줘"));

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewDeliveryList(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/200/696/028/006/7e4144e56eb58481a3ede39b2215b75e.jpg";

			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['delivery_category']);

			$groupLabel = $requestBody['params']['delivery_category'];
			try {
				$deliveryGroup = DeliveryGroup::CREATE_BY_LABEL($groupLabel);
			} catch(ModelNotFoundException $e) {
				throw new Exception($groupLabel . " 배달업체 그룹을 찾을 수 없습니다.");
			}

			$skillResponse = new SkillResponse;
			$skillResponse->addQuickReplies((new QuickReply("돌아가기"))->setMessageText("배달음식점 목록 보여줘"));
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));

			$deliveries = $deliveryGroup->getRandomDeliveries(10);
			if(count($deliveries) < 1){
				$skillResponse->addResponseComponent(new SimpleText(
					"우리학교 주변에 등록된 【 $deliveryGroup->label 】 배달업체를 찾을 수 없습니다."
				));

				return json_encode($skillResponse->render());
			}

			$skillResponse->addResponseComponent(new SimpleText(
				"우리학교 주변 【 $deliveryGroup->label 】 배달업체 목록" . "\n\n" .
				"문구 추가해야함"
			));

			$carousel = new Carousel;
			foreach($deliveries as $delivery){
				$basicCard = new BasicCard;
				$basicCard->title = $delivery->title;
				$basicCard->description = $delivery->description;

				$thumbnail = $delivery->getThumbnail();
				$thumbnailLink = $temporary_thumbnail;
				if($thumbnail != null)
					$thumbnailLink = "http://chatbot.kunnect.net" . $thumbnail->getDownloadLinkDirectory();

				$basicCard->setThumbnail(new Thumbnail($thumbnailLink));
				$basicCard->addButton((new Button("상세메뉴 확인하기"))->setWebLinkUrl('https://www.naver.com'));
				$basicCard->addButton((new Button("공유하기"))->setActionShare());
				$basicCard->addButton((new Button("배달 주문하기"))->setPhoneNumber($delivery->contact));

				$carousel->addCard($basicCard);
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
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
		public function adminViewDeliveryList(){
			$deliveries = Delivery::GET_LIST();

			return $this->adminView()->render('admin.delivery.html', [
				'sub_title' => "배달업체 목록",
				'active_title' => "배달업체 목록",
				'delivery_list' => $deliveries
			]);
		}
		public function adminViewDeliveryAdd(){
			$groups = DeliveryGroup::GET_LIST();

			return $this->adminView()->render('admin.delivery.add.html', [
				'sub_title' => "배달업체 추가",
				'active_title' => "배달업체 추가",
				'group_list' => $groups
			]);
		}
		public function adminViewDeliveryInfo($delivery_id){
			$delivery = new Delivery($delivery_id);
			$groups = DeliveryGroup::GET_LIST();
			$items = $delivery->getAllItems();

			return $this->adminView()->render('admin.delivery.edit.html', [
				'sub_title' => "배달업체 정보",
				'active_title' => "배달업체 목록",
				'delivery' => $delivery,
				'group_list' => $groups,
				'item_list' => $items,
				'belonging' => $delivery->getBelongingGroups(),
				'thumbnail' => $delivery->getThumbnail()
			]);
		}
		public function adminViewAddDeliveryItem($delivery_id){
			$delivery = new Delivery($delivery_id);

			return $this->adminView()->render('admin.delivery.item.add.html', [
				'sub_title' => "배달업체 아이템 추가",
				'active_title' => "배달업체 목록",
				'delivery' => $delivery
			]);
		}
		public function adminViewDeliveryItem($delivery_id, $item_id){
			$delivery = new Delivery($delivery_id);
			$item = new DeliveryItem($item_id);

			if($item->getDeliveryID() !== $delivery->id)
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			return $this->adminView()->render('admin.delivery.item.edit.html', [
				'sub_title' => "배달업체 아이템 정보",
				'active_title' => "배달업체 목록",
				'delivery' => $delivery,
				'deliveryItem' => $item,
				'thumbnail' => $item->getThumbnail()
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
		public function processUpdateDeliveryGroup($group_id){
			B::PARAMETER_CHECK(['title', 'description', 'label', 'thumbnail', 'priority']);

			$group = new DeliveryGroup($group_id);
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];
			$group->priority = intval($_REQUEST['priority']);

			if(intval($_REQUEST['thumbnail']) > 0) {
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			} else {
				$group->removeThumbnail();
			}

			/***
			 *	TODO - label 칼럼 중복 검사
			 */

			$group->update();

			header('Location: /admin/delivery/groups/' . $group->id);
		}
		public function processDeleteDeliveryGroup($group_id){
			$group = new DeliveryGroup($group_id);
			$group->delete();

			header('Location: /admin/delivery/groups');
		}
		public function processAddDelivery(){
			B::PARAMETER_CHECK(['title', 'description', 'contact', 'thumbnail', 'contract', 'promotion', 'groups']);

			$delivery = new Delivery;
			$delivery->title = $_REQUEST['title'];
			$delivery->description = $_REQUEST['description'];
			$delivery->contact = $_REQUEST['contact'];

			if(in_array($_REQUEST['contract'], [1, 2])) $delivery->contract = $_REQUEST['contract'];
			if(in_array($_REQUEST['promotion'], [1, 2, 3])) $delivery->promotion = $_REQUEST['promotion'];
			if(intval($_REQUEST['thumbnail']) > 0) $delivery->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

			$delivery->save();

			foreach($_REQUEST['groups'] as $group_id){
				$group = new DeliveryGroup(intval($group_id));
				$group->addDelivery($delivery);
			}

			header('Location: /admin/delivery');
		}
		public function processUpdateDelivery($delivery_id){
			B::PARAMETER_CHECK(['title', 'description', 'contact', 'thumbnail', 'contract', 'promotion', 'groups']);

			$delivery = new Delivery($delivery_id);
			$delivery->title = $_REQUEST['title'];
			$delivery->description = $_REQUEST['description'];
			$delivery->contact = $_REQUEST['contact'];

			if(in_array($_REQUEST['contract'], [1, 2])) $delivery->contract = $_REQUEST['contract'];
			if(in_array($_REQUEST['promotion'], [1, 2, 3])) $delivery->promotion = $_REQUEST['promotion'];
			if(intval($_REQUEST['thumbnail']) > 0)
				$delivery->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$delivery->removeThumbnail();

			$delivery->update();

			$delivery->releaseAllBelongingGroups();
			foreach($_REQUEST['groups'] as $group_id){
				$group = new DeliveryGroup(intval($group_id));
				$group->addDelivery($delivery);
			}

			header('Location: /admin/delivery/' . $delivery->id);
		}
		public function processDeleteDelivery($delivery_id){
			$group = new Delivery($delivery_id);
			$group->delete();

			header('Location: /admin/delivery');
		}
		public function processAddDeliveryItem($delivery_id){
			B::PARAMETER_CHECK(['title', 'price', 'discount', 'thumbnail', 'is_visible']);
			$delivery = new Delivery($delivery_id);

			$deliveryItem = new DeliveryItem;
			$deliveryItem->title = $_REQUEST['title'];
			$deliveryItem->price = $_REQUEST['price'];
			$deliveryItem->discount = $_REQUEST['discount'];

			$deliveryItem->is_visible = 'Y';
			if(in_array($_REQUEST['is_visible'], ['Y', 'N']))
				$deliveryItem->is_visible = $_REQUEST['is_visible'];

			$deliveryItem->setDelivery($delivery);
			if(intval($_REQUEST['thumbnail']) > 0)
				$delivery->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

			$deliveryItem->save();

			header('Location: /admin/delivery/' . $delivery->id);
		}
		public function processUpdateDeliveryItem($delivery_id, $item_id){
			B::PARAMETER_CHECK(['title', 'price', 'discount', 'thumbnail', 'is_visible']);
			$delivery = new Delivery($delivery_id);
			$deliveryItem = new DeliveryItem($item_id);

			if($deliveryItem->getDeliveryID() !== $delivery->id)
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			$deliveryItem->title = $_REQUEST['title'];
			$deliveryItem->price = $_REQUEST['price'];
			$deliveryItem->discount = $_REQUEST['discount'];

			$deliveryItem->is_visible = 'Y';
			if(in_array($_REQUEST['is_visible'], ['Y', 'N']))
				$deliveryItem->is_visible = $_REQUEST['is_visible'];

			$deliveryItem->setDelivery($delivery);
			if(intval($_REQUEST['thumbnail']) > 0)
				$delivery->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$delivery->removeThumbnail();

			$deliveryItem->update();

			header('Location: /admin/delivery/' . $delivery->id . '/item/' . $deliveryItem->id);
		}
		public function processDeleteDeliveryItem($delivery_id, $item_id){
			$delivery = new Delivery($delivery_id);
			$deliveryItem = new DeliveryItem($item_id);

			if($deliveryItem->getDeliveryID() !== $delivery->id)
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			$deliveryItem->delete();

			header('Location: /admin/delivery/' . $delivery->id);
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