<?php

class UserGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    public function getByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM users
                WHERE IDuser = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUsername(string $username): array | false
    {
        $sql = 'SELECT * FROM users WHERE name = :username';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getByMail(string $mail): array | false
    {
        $sql = 'SELECT * FROM users WHERE mail = :mail';
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
     public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users";

        $stmt = $this->conn->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
    
    public function updateUser(int $id, array $data): bool
    {
        $fields = [];
        $params = [":id" => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE IDuser = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    public function deleteUser(int $id): bool
    {
        $sql = "DELETE FROM users WHERE IDuser = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
        }
    public function InsertQuery(int $id, array $data): bool
    {
        $columns = ['user_id']; // assume 'user_id' is the column for $id
        $placeholders = [':id'];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $columns[] = $key;
            $placeholders[] = ":$key";
            $params[":$key"] = $value;
        }

        $columnsStr = implode(', ', $columns);
        $placeholdersStr = implode(', ', $placeholders);

        $sql = "INSERT INTO user_queries ($columnsStr) VALUES ($placeholdersStr)";
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return $stmt->execute();
    }


}
