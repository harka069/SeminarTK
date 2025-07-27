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
    header("Location: ../frontend/html/meni.html"); //TODO better..maybe in routing
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

        /*switch($subresource){
            case "MotStatByQuerry":
                MotStatByQuerry($database);
                break;
            case "CarByVin":
                CarByVIN($database);
                break;
            case "ModelsByBrand":   
                ModelsByBrand($database);
                break; 
            default:
                http_response_code(404);
                exit;
        }*/
        break;
        

}
/*
*NOT needed since we do everything in Controller functions (Car,User,Favourite)
function getAllUsers($database)
{
    $gateway = new UserGateway($database);
    $controller = new UserController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD']); 
}

function updateUser($database,$IDuser)
{
    $gateway = new UserGateway($database);
    $controller = new UserController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD'],$IDuser);
}

function deleteUser($database,$IDuser)
{

    $gateway = new UserGateway($database);
    $controller = new UserController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD'],$IDuser);
}

function CarByVIN($database)
{
    $gateway = new CarGateway($database);
    $controller = new CarController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD']);
}

function MotStatByQuerry($database)
{
    $gateway = new CarGateway($database);
    $controller = new CarController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD']);
}

function ModelsByBrand($database)
{
    $gateway = new CarGateway($database);
    $controller = new CarController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD']);
}

function SaveQuerry($database,$IDuser)
{
    $gateway = new UserGateway($database);
    $controller = new UserController($gateway);
    $controller->processRequest($_SERVER['REQUEST_METHOD'],$IDuser);
}*/
