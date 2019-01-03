<?php
	class AdminController {
		public function viewIndexPage(){
			return B::VIEW()->render('admin.index.html');
		}
	}