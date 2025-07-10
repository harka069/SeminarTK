<?php

class CarGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function CarByVIN(string $VIN)
    {
        $sql = "SELECT 
                a.VIN,
                a.prevozeni_kilometri,
                a.datum_prve_registracije,
                z.znamka_ime,
                m.model_ime,
                g.gorivo_tip

                FROM avtomobili a
                JOIN znamke z ON a.znamka_id = z.znamka_id
                JOIN modeli m ON a.modeli_id = m.model_id
                JOIN gorivo g ON a.gorivo_id = g.gorivo_id
                WHERE VIN = :id";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $VIN, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function CarByQuerry(string $startDate, string $endDate, string $brand, string $model, int $max_km,int $min_km,string $fuel): array|false
    {
            $sql = "SELECT 
                a.VIN,
                a.prevozeni_kilometri,
                a.datum_prve_registracije,
                z.znamka_ime AS brand,
                m.model_ime AS model,
                g.gorivo_tip AS gorivo
                
            FROM avtomobili a
            JOIN znamke z ON a.znamka_id = z.znamka_id
            JOIN modeli m ON a.modeli_id = m.model_id
            JOIN gorivo g ON a.gorivo_id = g.gorivo_id
            WHERE z.znamka_ime = :brand
              AND m.model_ime = :model
              AND a.datum_prve_registracije BETWEEN :start_date AND :end_date
              AND g.gorivo_tip = :fuel
              AND a.prevozeni_kilometri BETWEEN :min_km AND :max_km";
             

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":brand", $brand);
        $stmt->bindValue(":model", $model);
        $stmt->bindValue(":start_date", $startDate);
        $stmt->bindValue(":end_date", $endDate);
        $stmt->bindValue(":max_km", $max_km);
        $stmt->bindValue(":min_km", $min_km);
        $stmt->bindValue(":fuel", $fuel);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function MotStatByQuerry(string $startDate, string $endDate, string $brand, string $model, int $max_km,int $min_km,string $fuel): array|false
    {
           
        
        $sql = "SELECT
                COUNT(*) AS total_count,
                COUNT(CASE WHEN r.TEHNICNI_PREGLED_STATUS IN ('brezhiben', 'pogojno brezhiben') THEN 1 END) AS brezhiben_count,
                COUNT(CASE WHEN r.TEHNICNI_PREGLED_STATUS IN ('ni brezhiben', 'ni brezhiben - kriticna napaka') THEN 1 END) AS ne_brezhiben_count
            FROM avtomobili a
                JOIN znamke z ON a.znamka_id = z.znamka_id
                JOIN modeli m ON a.modeli_id = m.model_id
                JOIN gorivo g ON a.gorivo_id = g.gorivo_id
                JOIN rezultatiTP r ON a.VIN = r.VIN
            WHERE z.znamka_ime = :brand
                AND m.model_ime = :model
                AND a.datum_prve_registracije BETWEEN :start_date AND :end_date
                AND g.gorivo_tip = :fuel
                AND a.prevozeni_kilometri BETWEEN :min_km AND :max_km
                ";

                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":brand", $brand);
        $stmt->bindValue(":model", $model);
        $stmt->bindValue(":start_date", $startDate);
        $stmt->bindValue(":end_date", $endDate);
        $stmt->bindValue(":max_km", $max_km);
        $stmt->bindValue(":min_km", $min_km);
        $stmt->bindValue(":fuel", $fuel);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function ModelsByBrand(string $brand): array|false
    {
        /*$sql = "SELECT DISTINCT
            FROM modeli m   
                JOIN m ON m.modeli_id = znamka.znamka_id
            WHERE
            znamka.znamka_ID = :brand
        ";*/
        $sql = "SELECT DISTINCT m.model_ime
        FROM modeli m 
        JOIN znamke z ON m.znamka_id = z.znamka_id
        WHERE z.znamka_ime = :brand";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":brand", $brand);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN,0);
    }
}