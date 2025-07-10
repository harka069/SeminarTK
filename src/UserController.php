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
                echo json_encode($this->gateway->getAllUsers());
                //TODO GET SINGLE USER
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
                $znamka     = $this->clean_string_input(filter_input(INPUT_GET, "znamka", FILTER_UNSAFE_RAW));
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

        // Validate or filter $data
        $allowed = ["Name", "Surname", "Mail", "Password"];
        $updateData = [];

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

        if (isset($updateData["Password"])) {
            $updateData["Password"] = password_hash($updateData["Password"], PASSWORD_DEFAULT);
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
