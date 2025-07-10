<?php
class CarController extends Controller
{
    public function __construct(private CarGateway $gateway)
    {
    }

    public function processRequest(string $method): void
    {
       switch ($method) {
            case "GET":
                $znamka     = $this->clean_string_input(filter_input(INPUT_GET, "znamka", FILTER_UNSAFE_RAW));
                $model      = $this->clean_string_input(filter_input(INPUT_GET, "model", FILTER_UNSAFE_RAW));
                $start_date = $this->clean_string_input(filter_input(INPUT_GET, "start_date", FILTER_UNSAFE_RAW));
                $end_date   = $this->clean_string_input(filter_input(INPUT_GET, "end_date", FILTER_UNSAFE_RAW));
                $fuel       = $this->clean_string_input(filter_input(INPUT_GET, "fuel", FILTER_UNSAFE_RAW));
                $VIN        = $this->clean_string_input(filter_input(INPUT_GET, "VIN", FILTER_UNSAFE_RAW));

                $max_km     = filter_input(INPUT_GET, "max_km", FILTER_VALIDATE_INT);
                $min_km     = filter_input(INPUT_GET, "min_km", FILTER_VALIDATE_INT);

                if($znamka && $model && $start_date && $end_date && $max_km && $fuel) {
                    $this->CarByQuerry($start_date, $end_date,$znamka, $model,$max_km, $min_km,$fuel);
                }elseif($VIN){
                    $this->CarByVIN($VIN);
                }elseif($znamka){
                    $this->ModelsByBrand($znamka);
                }
                else{
                    $this->NotEnoughParameters();
                }
                break;
            case "PUT":
              
            break;
            case "DELETE":
            
            break;
            default:
                $this->methodNotAllowed("GET, PUT");
        }
    }

    private function CarByVIN(string $VIN)
    {
        if (preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $VIN) !== 1) {
            http_response_code(400);
            echo json_encode(["error"=> 'VIN is not in correct format']);
            exit;
        }
        echo json_encode($this->gateway->CarByVIN($VIN));
    }
    private function CarByQuerry(string $start_date, string $end_date, string $brand, string $model, int $max_km,int $min_km,string $fuel)
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
        echo json_encode($this->gateway->MotStatByQuerry($start_date, $end_date, $brand, $model, $max_km, $min_km, $fuel));
    }

    private function ModelsByBrand(string $brand)
    {
       echo json_encode($this->gateway->ModelsByBrand($brand));
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