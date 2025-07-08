<?php
class StudentGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
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

}
