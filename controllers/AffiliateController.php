<?php
	class AffiliateController {
		public function skillViewFoodGroup(){

		}
		public function skillViewPlayGroup(){

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

		/**
		 * @return Twig_Environment
		 */
		protected function adminView(){
			$twig = B::VIEW();
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