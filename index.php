<?php
	use \Phroute\Phroute\RouteCollector;

	require './vendor/autoload.php';

	date_default_timezone_set('Asia/Seoul');
	if(!isset($_SESSION)){ session_start(); }

	if(!B::LOAD_CONFIG()){
		header( $_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
		die("Service initialization failure");
	}

	B::OPEN_DB();

	$router = new RouteCollector();
	$router->filter('skillBefore', function(){
		header('Content-Type: application/json');
	});

	$router->group(['prefix' => 'skill/v1/', 'before' => 'skillBefore'], function($router){
		$router->any('contact', ["ContactsController", "skillViewList"]);
		$router->any('contact/detail', ["ContactsController", "skillViewDetail"]);

		$router->any('popkon/news', ["NewspaperController", "skillViewNews"]);
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
			new SimpleText($e->getMessage())
		);

		echo json_encode($skillResponse->render());
	} finally {
		B::CLOSE_DB();
	}