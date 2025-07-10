<?php
class FavouriteController extends Controller
{
    public function __construct(private  FavouriteGateway $gateway)
    {
    }

    public function processRequest(string $method, ?int $IDuser): void
    {
       
       switch ($method) {
            case "GET":
               /*not needed?
               $IDuser = filter_input(INPUT_GET, "IDuser", FILTER_VALIDATE_INT);
               */
               if($IDuser){
                    $this->UserFavouriteQueries($IDuser);
                }else{
                    $this->NotEnoughParameters();
                    exit;
                }
                break;
            case "DELETE":
                $QueryID = filter_input(INPUT_GET, "QueryID", FILTER_VALIDATE_INT);
                 if (!$IDuser&&$QueryID) {
                    $this->NotEnoughParameters();
                    exit;
                    }
                $this->DeleteQuery((int)$IDuser,$QueryID);              
            break;
            default:
                $this->methodNotAllowed("GET,DELETE");
        }
    }
    private function UserFavouriteQueries(int $IDUser)
    {
       echo json_encode($this->gateway->UserFavouriteQueries($IDUser));
    }
    private function DeleteQuery(int $IDUser,int $QueryID)
    {
        echo json_encode($this->gateway->DeleteQuery($IDUser,$QueryID));
    }
}