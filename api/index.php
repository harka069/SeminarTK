<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$subresource = "nisubresors";
$resource = $parts[3];
if (isset($parts[4]))
{
    $subresource = $parts[4];
}


$user = new UserGateway($database);
$JwtCtrl = new Jwt($_ENV["SECRET_KEY"]);
$auth = new Auth($user, $JwtCtrl);
$IDuser = $auth->authenticateJWTToken();
if (!$IDuser) {
    header("Location: ../frontend/html/meni.html"); 
    exit;}

switch($resource){
    case "users":
        switch($subresource){
            case "favourite":
                $gateway = new FavouriteGateway($database);
                $controller = new FavouriteController($gateway);
                $controller->processRequest($_SERVER['REQUEST_METHOD'],$IDuser);
            break;
            default:
                $gateway = new UserGateway($database);
                $controller = new UserController($gateway);
                $controller->processRequest($_SERVER['REQUEST_METHOD'],$IDuser); 
            break;
        }
        exit;
        
    case "cars":
        $gateway = new CarGateway($database);
        $controller = new CarController($gateway);
        $controller->processRequest($_SERVER['REQUEST_METHOD']); 
        break;
        

}
