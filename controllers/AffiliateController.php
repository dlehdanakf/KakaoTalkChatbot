<?php
	class AffiliateController {
		public function skillViewAffiliateGroups(){
			$requestBody = B::VALIDATE_SKILL_REQUEST_BODY(['affiliate_category']);
			$category = AffiliateGroup::CATEGORY_FOOD;
			if($requestBody['params']['affiliate_category'] == '문화시설')
				$category = AffiliateGroup::CATEGORY_PLAY;

			$skillResponse = new SkillResponse;
			$skillResponse->addQuickReplies((new QuickReply("메인으로"))->setMessageText("메인으로 돌아가기"));
			$groups = AffiliateGroup::GET_ORDERED_LIST($category);
			if(count($groups) < 1) {
				$skillResponse->addResponseComponent(new SimpleText(
					$requestBody['params']['affiliate_category'] . " 제휴업체를 찾을 수 없습니다."
				));

				return json_encode($skillResponse->render());
			}

			$carousel = new Carousel;
			foreach($groups as $group){
				$carousel->addCard($group->getBasicCard());
			}

			$skillResponse->addResponseComponent(new SimpleText(
				"🙋 맛집탐방 메뉴선정은 저에게 맡겨주세요!" . "\n" .
				"보기쉽게 정돈된 우리학교 맛집 알아보기 👇"
			));
			$skillResponse->addResponseComponent($carousel);

			return json_encode($skillResponse->render());
		}
		public function skillViewAffiliateList(){

		}
		public function skillViewAffiliateItemList(){

		}

		public function adminViewAffiliateGroupList(){
			$groups = AffiliateGroup::GET_LIST();

			return $this->adminView()->render('admin.affiliate.groups.html', [
				'sub_title' => "제휴업체 그룹 목록",
				'active_title' => "제휴업체 그룹 목록",
				'group_list' => $groups
			]);
		}
		public function adminViewAffiliateGroupAdd(){
			return $this->adminView()->render('admin.affiliate.groups.add.html', [
				'sub_title' => "제휴업체 그룹 추가",
				'active_title' => "제휴업체 그룹 추가"
			]);
		}
		public function adminViewAffiliateGroupInfo($group_id){
			$group = new AffiliateGroup($group_id);

			return $this->adminView()->render('admin.affiliate.groups.edit.html', [
				'sub_title' => "제휴업체 그룹 정보",
				'active_title' => "제휴업체 그룹 목록",
				'group' => $group,
				'thumbnail' => $group->getThumbnail()
			]);
		}
		public function adminViewAffiliateList(){
			$affiliates = Affiliate::GET_LIST();

			return $this->adminView()->render('admin.affiliate.html', [
				'sub_title' => "제휴업체 목록",
				'active_title' => "제휴업체 목록",
				'affiliate_list' => $affiliates
			]);
		}
		public function adminViewAffiliateAdd(){
			$groups = AffiliateGroup::GET_LIST();

			return $this->adminView()->render('admin.affiliate.add.html', [
				'sub_title' => "제휴업체 추가",
				'active_title' => "제휴업체 추가",
				'group_list' => $groups
			]);
		}
		public function adminViewAffiliateInfo($affiliate_id){
			$affiliate = new Affiliate($affiliate_id);
			$groups = AffiliateGroup::GET_LIST();
			$items = $affiliate->getAllItems();

			return $this->adminView()->render('admin.affiliate.edit.html', [
				'sub_title' => "제휴업체 정보",
				'active_title' => "제휴업체 목록",
				'affiliate' => $affiliate,
				'group_list' => $groups,
				'item_list' => $items,
				'belonging' => $affiliate->getBelongingGroups(),
				'thumbnail' => $affiliate->getThumbnail()
			]);
		}
		public function adminViewAddAffiliateItem($affiliate_id){
			$affiliate = new Delivery($affiliate_id);

			return $this->adminView()->render('admin.affiliate.item.add.html', [
				'sub_title' => "제휴업체 아이템 추가",
				'active_title' => "제휴업체 목록",
				'affiliate' => $affiliate
			]);
		}

		public function processAddAffiliateGroup(){
			B::PARAMETER_CHECK(['title', 'description', 'label', 'thumbnail', 'priority', 'category']);

			$group = new AffiliateGroup;
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];
			$group->priority = (int) $_REQUEST['priority'];
			$group->category = AffiliateGroup::CATEGORY_FOOD;

			if(intval($_REQUEST['thumbnail']) > 0)
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			if(in_array($_REQUEST['category'], [AffiliateGroup::CATEGORY_FOOD, AffiliateGroup::CATEGORY_PLAY]))
				$group->category = $_REQUEST['category'];

			/***
			 *	TODO - label 칼럼 중복 검사
			 */

			$group->save();

			header('Location: /admin/affiliate/groups');
		}
		public function processUpdateAffiliateGroup($group_id){
			B::PARAMETER_CHECK(['title', 'description', 'label', 'thumbnail', 'priority', 'category']);

			$group = new AffiliateGroup($group_id);
			$group->title = $_REQUEST['title'];
			$group->description = $_REQUEST['description'];
			$group->label = $_REQUEST['label'];
			$group->priority = (int) $_REQUEST['priority'];

			if(intval($_REQUEST['thumbnail']) > 0)
				$group->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$group->removeThumbnail();

			if(in_array($_REQUEST['category'], [AffiliateGroup::CATEGORY_FOOD, AffiliateGroup::CATEGORY_PLAY]))
				$group->category = $_REQUEST['category'];

			/***
			 *	TODO - label 칼럼 중복 검사
			 */

			$group->update();

			header('Location: /admin/affiliate/groups/' . $group->id);
		}
		public function processDeleteAffiliateGroup($group_id){
			$group = new AffiliateGroup($group_id);
			$group->delete();

			header('Location: /admin/affiliate/groups');
		}
		public function processAddAffiliate(){
			B::PARAMETER_CHECK(['title', 'description', 'thumbnail', 'location', 'map_x', 'map_x', 'contact', 'contract', 'promotion']);

			$affiliate = new Affiliate;
			$affiliate->title = $_REQUEST['title'];
			$affiliate->description = $_REQUEST['description'];
			$affiliate->location = $_REQUEST['location'];
			$affiliate->map_x = $_REQUEST['map_x'];
			$affiliate->map_y = $_REQUEST['map_y'];
			$affiliate->contact = $_REQUEST['contact'];
			$affiliate->contract = 0;
			$affiliate->promotion = 0;

			if(in_array($_REQUEST['contract'], [1, 2])) $affiliate->contract = $_REQUEST['contract'];
			if(in_array($_REQUEST['promotion'], [1, 2, 3])) $affiliate->promotion = $_REQUEST['promotion'];
			if(intval($_REQUEST['thumbnail']) > 0) $affiliate->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

			$affiliate->save();

			if(B::PARAMETER_CHECK(['groups'], true) && is_array($_REQUEST['groups'])){
				foreach($_REQUEST['groups'] as $group_id){
					$group = new AffiliateGroup(intval($group_id));
					$group->addAffiliate($affiliate);
				}
			}

			header('Location: /admin/affiliate');
		}
		public function processUpdateAffiliate($affiliate_id){
			B::PARAMETER_CHECK(['title', 'description', 'thumbnail', 'location', 'map_x', 'map_x', 'contact', 'contract', 'promotion']);

			$affiliate = new Affiliate($affiliate_id);
			$affiliate->title = $_REQUEST['title'];
			$affiliate->description = $_REQUEST['description'];
			$affiliate->location = $_REQUEST['location'];
			$affiliate->map_x = $_REQUEST['map_x'];
			$affiliate->map_y = $_REQUEST['map_y'];
			$affiliate->contact = $_REQUEST['contact'];

			if(in_array($_REQUEST['contract'], [1, 2])) $affiliate->contract = $_REQUEST['contract'];
			if(in_array($_REQUEST['promotion'], [1, 2, 3])) $affiliate->promotion = $_REQUEST['promotion'];
			if(intval($_REQUEST['thumbnail']) > 0)
				$affiliate->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$affiliate->removeThumbnail();

			$affiliate->update();

			if(B::PARAMETER_CHECK(['groups'], true) && is_array($_REQUEST['groups'])){
				$affiliate->releaseAllBelongingGroups();
				foreach($_REQUEST['groups'] as $group_id){
					$group = new AffiliateGroup(intval($group_id));
					$group->addAffiliate($affiliate);
				}
			}

			header('Location: /admin/affiliate/' . $affiliate->id);
		}
		public function processDeleteAffiliate($affiliate_id){
			$affiliate = new Affiliate($affiliate_id);
			$affiliate->delete();

			header('Location: /admin/affiliate');
		}
		public function processAddAffiliateItem($affiliate_id){
			B::PARAMETER_CHECK(['title', 'price', 'discount', 'thumbnail', 'is_visible']);
			$affiliate = new Affiliate($affiliate_id);

			$affiliateItem = new AffiliateItem;
			$affiliateItem->title = $_REQUEST['title'];
			$affiliateItem->price = $_REQUEST['price'];
			$affiliateItem->discount = $_REQUEST['discount'];

			$affiliateItem->is_visible = 'Y';
			if(in_array($_REQUEST['is_visible'], ['Y', 'N']))
				$affiliateItem->is_visible = $_REQUEST['is_visible'];

			$affiliateItem->setAffiliate($affiliate);
			if(intval($_REQUEST['thumbnail']) > 0)
				$affiliateItem->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));

			$affiliateItem->save();

			header('Location: /admin/affiliate/' . $affiliate->id);
		}
		public function processUpdateAffiliateItem($affiliate_id, $item_id){
			B::PARAMETER_CHECK(['title', 'price', 'discount', 'thumbnail', 'is_visible']);
			$affiliate = new Affiliate($affiliate_id);
			$affiliateItem = new AffiliateItem($item_id);

			if($affiliateItem->getDeliveryID() !== $affiliate->id)
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			$affiliateItem->title = $_REQUEST['title'];
			$affiliateItem->price = $_REQUEST['price'];
			$affiliateItem->discount = $_REQUEST['discount'];

			$affiliateItem->is_visible = 'Y';
			if(in_array($_REQUEST['is_visible'], ['Y', 'N']))
				$affiliateItem->is_visible = $_REQUEST['is_visible'];

			if(intval($_REQUEST['thumbnail']) > 0)
				$affiliateItem->setThumbnail(Attachment::CREATE_BY_MYSQLID($_REQUEST['thumbnail']));
			else
				$affiliateItem->removeThumbnail();

			$affiliateItem->update();

			header('Location: /admin/delivery/' . $affiliate->id . '/item/' . $affiliateItem->id);
		}
		public function processDeleteAffiliateItem($affiliate_id, $item_id){
			$affiliate = new Affiliate($affiliate_id);
			$affiliateItem = new AffiliateItem($item_id);

			if($affiliateItem->getDeliveryID() !== $affiliate->id)
				throw new \Phroute\Phroute\Exception\HttpRouteNotFoundException();

			$affiliateItem->delete();

			header('Location: /admin/delivery/' . $affiliate->id);
		}

		/**
		 * @return Twig_Environment
		 */
		protected function adminView(){
			$twig = B::VIEW();
			$twig->addGlobal('sub_section_title', '제휴업체 관리');
			$twig->addGlobal('sub_nav', [
				[
					"href" => "/admin/affiliate/add",
					"label" => "제휴업체 추가"
				], [
					"href" => "/admin/affiliate",
					"label" => "제휴업체 목록"
				], [
					"href" => "/admin/affiliate/groups/add",
					"label" => "제휴업체 그룹 추가"
				], [
					"href" => "/admin/affiliate/groups",
					"label" => "제휴업체 그룹 목록"
				]
			]);

			return $twig;
		}
	}