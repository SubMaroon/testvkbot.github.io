<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Our web handlers

$app->get('/', function() use($app) {
	return "Hello World!";
});
$app->post('/bot', function() use($app) {
	$data = json_decode(file_get_contents('php://input'));
	
	if(!$data)
		return 'nioh';
	
	if($data->secret !== getenv('VK_SECRET_TOKEN') && $data->type !== 'confirmation')
		return 'nioh ';
	
	switch($data->type)
		{
			case 'confirmation':
			return getenv('VK_CONFIRMATION_CODE');
			break;
			
	case 'message_new':
			
			$user_id = $data->object->user_id; 
			$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0")); 
			$user_name = $user_info->response[0]->first_name; 
			
		$request_params = array(
			'user_id'=>$data->object->user_id,
			'message'=> "Привет, {$user_name}. Выбери позицию:
			1-Оборудование,
			2-Аренда",
			'access_token'=> getenv('VK_TOKEN'),
				'v'=>'5.69'
			);
			
			if ( $data->object->body == 1 )
				$request_params = array(
				'user_id'=>$data->object->user_id,
				'message'=> 'Какое оборудование тебя интересует?',
				'access_token'=> getenv('VK_TOKEN'),
				'v'=>'5.69');
				
				if ( $data->object->body == 2 )
				$request_params = array(
				'user_id'=>$data->object->user_id,
				'message'=> 'Наш прайслист:
				Цены всякие там...',
				'access_token'=> getenv('VK_TOKEN'),
				'v'=>'5.69');
			file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
			return 'ok';
			break;
		}
	return "nioh";
});
$app->run();
?>