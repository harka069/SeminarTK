<?php
class Auth
{

    private array $payload =[];

    public function __construct(private UserGateway $user_gateway, private Jwt $JwtCtrl)
    {
    }



    public function authenticateJWTToken(): int|false
    {

        if (!preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        try {
            //$data = $this->JwtCtrl->decode($matches[1]);
            $this->payload = $this->JwtCtrl->decode($matches[1]);
        } catch (InvalidSignatureException $e) {

            http_response_code(401);
            echo json_encode(["message" => "invalid signature"]);
            return false;
        }catch (TokenExpiredException $e) {

            http_response_code(401);
            echo json_encode(["message" =>  "Token expired"]);

            return false;
        }catch (Exception $e) {

            http_response_code(400);
            echo json_encode(["message" => $e->getMessage()]);
            return false;
        }

        return $this->payload["sub"];

        #return true;

    }
    public function getUserIdFromToken(): int
    {
        if (!isset($this->payload["sub"])) {
            echo json_encode(["message" => "User ID (sub) not found in token"]);
            exit;
        }

        return $this->payload["sub"];
    }
}
