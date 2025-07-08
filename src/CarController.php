<?php
class CarController
{
    public function __construct(private CarGateway $gateway)
    {
    }

    public function processRequest(string $method): void
    {
       switch ($method) {
            case "GET":
                if (!empty($_GET["znamka"])&&
                !empty($_GET["model"])&&
                !empty($_GET["start_date"])&&
                !empty($_GET["end_date"])
                )
                $start_date = $_GET["start_date"];
                $end_date   = $_GET["end_date"];
                $znamka     = $_GET["znamka"];
                $model      = $_GET["model"];
                $max_km      = $_GET["max_km"];
                $min_km     = $_GET["min_km"];
            $fuel           = $_GET["fuel"];
                $this->CarByQuerry($start_date, $end_date,$znamka, $model,$max_km, $min_km,$fuel);
            break;
            case "PUT":
              
            break;
            case "DELETE":
            
            break;
            default:
                $this->methodNotAllowed("GET, PUT");
        }
    }

    private function methodNotAllowed(string $allowed_method): void
    {
        http_response_code(405);
        header("Allow: $allowed_method");
    }
    public function CarByVIN(string $VIN)
    {
        if (preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $VIN) !== 1) {
            exit;
        }
        echo json_encode($this->gateway->CarByVIN($VIN));
    }
    public function CarByQuerry(string $start_date, string $end_date, string $brand, string $model, int $max_km,int $min_km,string $fuel)
    {
        $from = DateTime::createFromFormat('Y-m-d', $start_date);
        $to = DateTime::createFromFormat('Y-m-d', $end_date);

        // Validate dates
        if (!$from || !$to) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid date format. Use YYYY-MM-DD."]);
        exit;
        }
         // Validate kilometers
        if ($max_km < $min_km) {
            http_response_code(400);
            echo json_encode(["error" => "max_km can't be more as min_km"]);
        exit;
        }

    // Check order
        if ($from > $to) {
            http_response_code(400);
            echo json_encode(["error" => "`start$start_date` must be earlier than `end_date$end_date`."]);
        exit;
        }
        echo json_encode($this->gateway->CarByQuerry($start_date, $end_date, $brand, $model, $max_km, $min_km, $fuel));
    }
    

}