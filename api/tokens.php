<?php

$payload = [
    "sub" => $user["IDuser"],
    "name" => $user["Name"],
    "exp" => time() + 2000
];

$JwtController = new Jwt($_ENV["SECRET_KEY"]);

$access_token = $JwtController->encode($payload);

$refresh_token_expiry = time() + 432000;

$refresh_token = $JwtController->encode([
    "sub" => $user["IDuser"],
    "exp" => $refresh_token_expiry
]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token
]);