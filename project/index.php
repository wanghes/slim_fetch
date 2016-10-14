<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "yinrenlei00";
$config['db']['dbname'] = "demo";

$app = new \Slim\App(['settings'=>$config]);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    //header('Content-type: application/json');
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}
/*$app->map('/:x+', function($x) {
    http_response_code(200);
})->via('OPTIONS');*/

$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET CHARACTER SET utf8");
    return $pdo;
};

$app->get('/hello/{name}', function (Request $request, Response $response,$args) {
    $name =$args['name'];
    $name = $request->getAttribute('name');

    //$response->getBody()->write("Hello, $name");
    $data = array('name' => $name, 'age' => 40);
    $newResponse = $response->withJson($data);
    $this->logger->addInfo("Something interesting happened");
    $db = $this->db;
    return $response;
});

$app->get('/',function(Request $request, Response $response,$args){
    $parsedBody = $request->getUri();
    print_r($parsedBody->getBaseUrl());
    echo '<br>';

    $headers = $response->getHeaders();
    foreach ($headers as $name => $values) {
        echo $name . ": " . implode(", ", $values);
    }
});

$app->post('/user/login',function(Request $request, Response $response){
    //$isXHR = $request->isXhr();

    $parsedBody = $request->getParsedBody();
    $response->withJson($parsedBody);
});

$app->post('/user',function(Request $request, Response $response){
    //$isXHR = $request->isXhr();

    $parsedBody = $request->getParsedBody();
    $response->withJson($parsedBody);
});

$app->get('/foods',function(Request $request, Response $response){
   $sql = "SELECT * FROM food";
    try {
        $db = $this->db;
        $stmt = $db->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        $response->withJson($wines);
    } catch(PDOException $e) {
        $response->withJson(array('status'=>false,'msg'=>$e->getMessage()));
    }
});

$app->get('/phones',function(Request $request, Response $response){
   $sql = "SELECT * FROM phones";
    try {
        $db = $this->db;
        $stmt = $db->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        $response->withJson($wines);
    } catch(PDOException $e) {
        $response->withJson(array('status'=>false,'msg'=>$e->getMessage()));
    }
});

$app->run();
?>