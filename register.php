<?php

require __DIR__ . "/vendor/autoload.php";

switch ($_SERVER["REQUEST_METHOD"]) 
{
    case 'POST':
        new_user();
        break;
    case 'PUT':
        $user_id = $_GET['userID'] ?? null;
    if ($user_id && is_numeric($user_id)) {
        update_user($user_id);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid or missing user ID."]);
    }
        break;
}

function new_user(){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $database = new Database(
        $_ENV["DB_HOST"],
        $_ENV["DB_NAME"],
        $_ENV["DB_USER"],
        $_ENV["DB_PASS"]
    );
    $conn = $database->getConnection();

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $mail_exist  = new UserGateway($database);

    if(($mail_exist->getByMail($data['email']))!= false)
    {
        http_response_code(403);
        echo json_encode(["error" => "User already exists"]);
        exit;
    }
    
    $sql = "INSERT INTO users (Name, Surname, Password,Mail) VALUES (:name, :surname, :password_hash, :email)";
    $stmt = $conn->prepare($sql);
    $password_hash = password_hash($data["password"], PASSWORD_DEFAULT);
    $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindValue(":surname", $data["surname"], PDO::PARAM_STR);
    $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
    $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
    $stmt->execute();

    echo "Thank you for registering.";
    exit;
}

function update_user($user_id) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $database = new Database(
        $_ENV["DB_HOST"],
        $_ENV["DB_NAME"],
        $_ENV["DB_USER"],
        $_ENV["DB_PASS"]
    );
    $conn = $database->getConnection();

    // Read JSON body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Build dynamic SQL for only provided fields
    $fields = [];
    $params = [];

    if (!empty($data["name"])) {
        $fields[] = "Name = :name";
        $params[":name"] = $data["name"];
    }
    if (!empty($data["surname"])) {
        $fields[] = "Surname = :surname";
        $params[":surname"] = $data["surname"];
    }
    if (!empty($data["email"])) {
        $fields[] = "Mail = :email";
        $params[":email"] = $data["email"];
    }
    if (!empty($data["password"])) {
        $fields[] = "Password = :password_hash";
        $params[":password_hash"] = password_hash($data["password"], PASSWORD_DEFAULT);
    }
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update."]);
        exit;
    }

    // Build the SQL query
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE IDuser = :user_id";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(["success" => "User updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to update user."]);
    }
    exit;
}