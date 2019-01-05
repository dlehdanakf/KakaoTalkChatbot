<?php
	use \Phroute\Phroute\RouteCollector;

	require '../vendor/autoload.php';

	date_default_timezone_set('Asia/Seoul');
	if(!isset($_SESSION)){ session_start(); }

	if(!B::LOAD_CONFIG()){
		header( $_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
		die("Service initialization failure");
	}

	B::OPEN_DB();
	B::LOAD_SETTING();

	$router = new RouteCollector();
	$router->filter('skillBefore', function(){
		header('Content-Type: application/json');
	});

	$router->any('events', function(){
		return B::VIEW()->render('event.detail.html');
	});

	$router->group(['prefix' => '/attachment'], function($router){
		$router->post('/upload', ["AttachmentController", "uploadAttachment"]);
		$router->get('/download', ["AttachmentController", "downloadAttachment"]);
	});

	$router->group(['prefix' => 'skill/v1/', 'before' => 'skillBefore'], function($router){
		$router->any('index', ["IndexController", "skillViewApplicationIndex"]);

		$router->any('contact', ["ContactsController", "skillViewList"]);
		$router->any('contact/detail', ["ContactsController", "skillViewDetail"]);

		$router->any('popkon/news', ["NewspaperController", "skillViewNews"]);
		$router->any('popkon/news/more', ["NewspaperController", "skillViewNewsMore"]);

		$router->any('toilet', ["ToiletController", "skillViewToilet"]);
		$router->any('toilet/more', ["ToiletController", "skillViewToiletMore"]);

		$router->any('calculator', ["CalculatorController", "skillViewDDay"]);

		$router->any('cafeteria', ["CafeteriaController", "skillViewCafeteriaList"]);
		$router->any('cafeteria/meal', ["CafeteriaController", "skillViewTodayMeal"]);

		/**
		 *	KakaoTalk 에서 BasicCard 형태에서
		 *	글자 수를 제한함에 따라 카카오 오픈빌더에서 기능 제공
		 */
		/** $router->any('schedule/list', ["ScheduleController", "skillViewList"]); */
	});

	$router->group(['prefix' => 'admin'], function($router){
		$router->get('/', ["AdminController", "viewIndexPage"]);
	});

	try {
		$dispatcher = new \Phroute\Phroute\Dispatcher($router->getData());
		$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], strtok($_SERVER["REQUEST_URI"], '?'));
		echo $response;
	} catch (\Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
		echo "404! HTTP Route not found";
	} catch (\Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
		echo "404! HTTP Method not found";
	} catch(Exception $e) {
		$skillResponse = new SkillResponse;
		$skillResponse->addResponseComponent(
			new SimpleText("[ERROR!] " . $e->getMessage())
		);

		echo json_encode($skillResponse->render());
	} finally {
		B::CLOSE_DB();
	}