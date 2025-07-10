<?php
class FavouriteGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {  
        $this->conn = $database->getConnection();
    }
    public function UserFavouriteQueries(int $IDUser)
    {
        $sql = "SELECT *
                FROM user_queries
                WHERE
                user_id = :IDUser
        ";  
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":IDUser", $IDUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function DeleteQuery(int $IDUser,int $QueryID)
    {
        $sql = "DELETE FROM user_queries
                WHERE user_id = :IDUser
                AND   query_id = :QueryID
        ";  
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":IDUser", $IDUser, PDO::PARAM_INT);
        $stmt->bindValue(":QueryID", $QueryID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}