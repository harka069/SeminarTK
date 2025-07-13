<?php

class UserController extends Controller
{
    public function __construct(private UserGateway $gateway)
    {
    }

    public function processRequest(string $method,?int $id = null): void
    {
       switch ($method) {
            case "GET":
                $IDuser     = filter_input(INPUT_GET, "IDuser", FILTER_VALIDATE_INT);
                if (!$IDuser) {
                    echo json_encode($this->gateway->getAllUsers());
                }
                echo json_encode($this->gateway->getByID($IDuser));
                break;

            case "PUT":
                //$id     = $this->clean_string_input(filter_input(INPUT_GET, "znamka", FILTER_UNSAFE_RAW));
                if (!$id) {
                    http_response_code(400);
                    $this->NotEnoughParameters();
                    return;
                }
                $this->update_user((int)$id);
                break;
            case "POST":
               // $id     = $this->clean_string_input(filter_input(INPUT_GET, "znamka", FILTER_UNSAFE_RAW));
               //TODO - into other endpoint, her is more like register user place
                if (!$id) {
                    http_response_code(400);
                    $this->NotEnoughParameters();
                    return;
                }
                $this->InsertQuery((int)$id);
                break;
            case "DELETE":
                //$znamka     = $this->clean_string_input(filter_input(INPUT_GET, "znamka", FILTER_UNSAFE_RAW));
                 if (!$id) {
                    $this->NotEnoughParameters();
                    return;
                } 
                $this->delete_user((int)$id);  
                break;
            default:
                $this->methodNotAllowed("GET, PUT,DELETE");
        }
    }
/*
    public function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }*/
    private function update_user(int $id): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $userOldData = $this->gateway->getByID($id);
        // Validate or filter $data
        $allowed = ["name", "surname", "mail", "password"];
        $updateData = [];
        if (!password_verify($data['oldPassword'], $userOldData[ 'Password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Old password is incorrect']);
            exit;
}
        foreach ($allowed as $field) {
            if (!empty($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            http_response_code(400);
            echo json_encode(["error" => "No valid fields to update"]);
            return;
        }

        if (isset($updateData["password"])) {
            $updateData["password"] = password_hash($updateData["password"], PASSWORD_DEFAULT);
        }

        if ($this->gateway->updateUser($id, $updateData)) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Update failed"]);
        }
    }
    private function delete_user(int $id)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $userData = $this->gateway->getByID($id);
        if (!password_verify($data['Password'], $userData[ 'Password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Password is incorrect']);
            exit;
        }
        if ($this->gateway->deleteUser($id)) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Delete failed"]);
        } 
    }

    private function InsertQuery(int $id){
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $fields = ["znamka", "model", "start_date", "end_date", "fuel", "max_km", "min_km"];

        foreach ($fields as $field) {
            if(!isset($data[$field])) {
                $this->NotEnoughParameters();
                exit;
            }
        }
        if ($this->gateway->InsertQuery($id,$data)) {
            echo json_encode(["message" => "Querry inserted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Query insert failed"]);
        }
    }
    /*
    *NOT needed since we have controller class
    private function NotEnoughParameters(): void
    {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Not enough parameters"]);
    }
    private function clean_string_input(string|null $input): string|null 
    {
    return $input !== null ? strip_tags($input) : null;
    }
    public function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }*/

}
