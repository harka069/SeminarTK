<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);


$resource = $parts[3];
$order = $parts[4];

$user = new UserGateway($database);
$JwtCtrl = new Jwt($_ENV["SECRET_KEY"]);
$auth = new Auth($user, $JwtCtrl);
$IDuser = $auth->authenticateJWTToken();
if (!$IDuser) {exit;}

switch($resource){
    case "users":
        switch($order){
            case "getAllUsers":
                getAllUsers($database);
                break;
            case "updateUser":
                updateUser($database,$IDuser);    
                break;
            case "deleteUser":
                deleteUser($database,$IDuser);
                break;
            case "saveQuery":
                SaveQuerry($database,$IDuser);
                break;
            default:
                http_response_code(404);
                exit;
        }
        break;
    case "cars":
        switch($order){
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
        }
        break;
        

}

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
}