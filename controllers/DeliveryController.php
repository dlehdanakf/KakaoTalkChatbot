<?php
	class DeliveryController {
		public function skillViewDeliveryGroups(){
			$skillResponse = new SkillResponse;
			$skillResponse->addResponseComponent(new SimpleText(
				"교내로 배달이 가능한 업체만 모아놨어요!" . "\n" .
				"다음 목록에서 주문하고싶은 음식의 분류를 선택해주세요."
			));

			$carousel = new Carousel;
			$categories = DeliveryGroupCategory::GET_ORDERED_LIST();
			if(count($categories) < 1)
				throw new Exception("배달업체 카테고리을 가져오는데 오류가 발생했습니다.");


			if(true){
				try {
					$yasik = DeliveryGroup::CREATE_BY_LABEL("야식");
					$carousel->addCard($yasik->getBasicCard());
				} catch(Exception $e) {}
			}

			foreach($categories as $category){
				$carousel->addCard($category->getBasicCard());
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewDeliveryList(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['delivery_category']);

			$groupLabel = $requestBody['params']['delivery_category'];
			try {
				$deliveryGroup = DeliveryGroup::CREATE_BY_LABEL($groupLabel);
			} catch(ModelNotFoundException $e) {
				throw new Exception($groupLabel . " 배달업체 그룹을 찾을 수 없습니다.");
			}

			$skillResponse = new SkillResponse;
			$count = $deliveryGroup->getDeliveryCount();
			if($count > 10)
				$skillResponse->addQuickReplies((new QuickReply("더보기"))->setBlockID("5c35e4c6384c5518d1200aa4", [
					'delivery_category' => $requestBody['params']['delivery_category']
				]));
			$skillResponse->addQuickReplies((new QuickReply("돌아가기"))->setMessageText("배달음식점 목록 보여줘"));
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));

			$deliveries = $deliveryGroup->getRandomDeliveries(10);
			if(count($deliveries) < 1){
				$skillResponse->addResponseComponent(new SimpleText(
					"🚫 우리학교 주변에 등록된 【 $deliveryGroup->label 】 배달업체를 찾을 수 없습니다."
				));

				return json_encode($skillResponse->render());
			}

			if($requestBody['utterance'] != "더보기")
				$skillResponse->addResponseComponent(new SimpleText(
					"【 " . $groupLabel . " 】" . "\n\n" .
					"우리학교 주변 배달업체 목록을 랜덤으로 보여드려요." . "\n" .
					"더보기 버튼을 누르시면 계속해서 다른 업체도 볼 수 있습니다."
				));

			$carousel = new Carousel;
			foreach($deliveries as $delivery){
				$carousel->addCard($delivery->getBasicCard());
			}

			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewDeliveryItemList(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY();

			$utteranceArray = explode(' ', $requestBody['utterance']);
			if($utteranceArray[count($utteranceArray) - 1] != "대표메뉴")
				throw new Exception("채팅봇이 질의 내용을 이해하지 못했습니다.");

			$utterance = implode(' ', array_slice($utteranceArray, 0, count($utteranceArray) - 1));

			$skillResponse = new SkillResponse;
			$skillResponse->addQuickReplies((new QuickReply("돌아가기"))->setMessageText("배달음식점 목록 보여줘"));
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));

			try {
				$delivery = Delivery::CREATE_BY_TITLE($utterance);
				$items = $delivery->getRandomItems();

				if(count($items) < 1){
					$skillResponse->addResponseComponent(new SimpleText(
						"🚫 배달업체 【 $delivery->title 】 에 등록된 대표메뉴가 없습니다."
					));

					return json_encode($skillResponse->render());
				}

				$skillResponse->addResponseComponent(new SimpleText(
					"【 " . $delivery->title . " 】" . "\n\n" .
					"배달업체의 대표메뉴(최대 10개)를 보여드려요." . "\n" .
					"공유하기 버튼을 통해 친구에게 전달할 수 있습니다."
				));

				$carousel = new Carousel;
				foreach($items as $item){
					$carousel->addCard($item->getCommerceCard());
				}

				$skillResponse->addResponseComponent($carousel);

				return json_encode($skillResponse->render());
			} catch(ModelNotFoundException $e) {
				$skillResponse->addResponseComponent(new SimpleText(
					$utterance . " 업체를 찾을 수 없습니다."
				));

				return json_encode($skillResponse->render());
			}
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
		public function adminViewDeliveryCategory(){
			$categories = DeliveryGroupCategory::GET_LIST();

			return $this->adminView()->render('admin.delivery.category.html', [
				'sub_title' => "배달업체 카테고리",
				'active_title' => "배달업체 카테고리",
				'category_list' => $categories
			]);
		}
		public function adminViewDeliveryCategoryInfo($category_id){
			$category = new DeliveryGroupCategory($category_id);
			$groups = DeliveryGroup::GET_LIST();

			return $this->adminView()->render('admin.delivery.category.edit.html', [
				'sub_title' => "배달업체 카테고리 정보",
				'active_title' => "배달업체 카테고리",
				'category' => $category,
				'thumbnail' => $category->getThumbnail(),
				'title_list' => $category->getDeliveryGroupLabel(true),
				'group_list' => $groups,
				'button_1' => $category->getDeliveryGroup(1),
				'button_2' => $category->getDeliveryGroup(2),
				'button_3' => $category->getDeliveryGroup(3)
			]);
		}

		public function processAddDeliveryGroup(){
			B::PARAMETER_CHECK(['title', 'description', 'label']);

			$group = new DeliveryGroup;
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];

			if(intval($_REQUEST['thumbnail']) > 0)
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

			/***
			 *	TODO - label 칼럼 중복 검사
			 */

			$group->save();

			header('Location: /admin/delivery/groups');
		}
		public function processUpdateDeliveryGroup($group_id){
			B::PARAMETER_CHECK(['title', 'description', 'label']);

			$group = new DeliveryGroup($group_id);
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];

			if(intval($_REQUEST['thumbnail']) > 0)
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$group->removeThumbnail();

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
				$deliveryItem->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

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

			if(intval($_REQUEST['thumbnail']) > 0)
				$deliveryItem->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$deliveryItem->removeThumbnail();

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
		public function processUpdateDeliveryCategory($category_id){
			B::PARAMETER_CHECK(['title', 'description', 'priority', 'thumbnail']);

			$category = new DeliveryGroupCategory($category_id);
			$category->title = $_REQUEST['title'];
			$category->description = $_REQUEST['description'];
			$category->priority = (int) $_REQUEST['priority'];

			if(intval($_REQUEST['thumbnail']) > 0)
				$category->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$category->removeThumbnail();

			if(B::PARAMETER_CHECK(['group_1'], true) && intval($_REQUEST['group_1']) > 0)
				$category->setDeliveryGroup(1, new DeliveryGroup($_REQUEST['group_1']));
			else
				$category->releaseDeliveryGroup(1);

			if(B::PARAMETER_CHECK(['group_2'], true) && intval($_REQUEST['group_2']) > 0)
				$category->setDeliveryGroup(2, new DeliveryGroup($_REQUEST['group_2']));
			else
				$category->releaseDeliveryGroup(2);

			if(B::PARAMETER_CHECK(['group_3'], true) && intval($_REQUEST['group_3']) > 0)
				$category->setDeliveryGroup(3, new DeliveryGroup($_REQUEST['group_3']));
			else
				$category->releaseDeliveryGroup(3);

			$category->update();

			header("Location: /admin/delivery/category/" . $category->id);
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
				], [
					"href" => "/admin/delivery/category",
					"label" => "배달업체 카테고리"
				]
			]);

			return $twig;
		}
	}