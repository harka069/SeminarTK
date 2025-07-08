<?php

class UserController
{
    public function __construct(private UserGateway $gateway)
    {
    }

    public function processRequest(string $method,?int $id = null): void
    {
       switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAllUsers());
                break;

            case "PUT":
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing user ID"]);
                    return;
                }
                $this->update_user((int)$id);
                break;
            case "DELETE":
                 if (!$id) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing user ID"]);
                    return;
                } 
                $this->delete_user((int)$id);  
            default:
                $this->methodNotAllowed("GET, PUT");
        }
    }

    private function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }
    public function update_user(int $id): void
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
    public function delete_user(int $id)
    {
       if ($this->gateway->deleteUser($id)) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Delete failed"]);
        } 
    }

}
