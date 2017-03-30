<?php 

include_once "database.php";

class Passenger_for{

    private $passenger_id;
    private $Trajet;

    private function __construct($passenger_id, $Trajet){
        $this->passenger_id = $passenger_id;
        $this->Trajet = $Trajet;
    }

    /**
    * Add new Trajet on database, return Trajet created
    * @param $passenger_id int
    * @param $Trajet 
    */
    public static function addPassenger($passenger_id, $Trajet){
        // TODO : check for places in Trajet
        // {}

        // try to save it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "INSERT INTO Passenger_for (passenger_id, Trajet) 
            VALUES ( :passenger_id, :Trajet)";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':passenger_id'=>$passenger_id, ':Trajet'=>$Trajet));
        }
        catch(PDOException $e){
            print("failed to add new Passenger  : " . $e->getMessage());
            die();
        }
    }

    /**
    * Remove a passenger_for relation
    * @param $passenger_id int
    * @param $Trajet
    *
    */
    public static function removePassenger($passenger_id, $Trajet){
        // try to save it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "DELETE FROM Passenger_for 
            WHERE ( passenger_id = :passenger_id AND Trajet = :Trajet)";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':passenger_id'=>intval($passenger_id), ':Trajet'=>intval($Trajet)));
        }
        catch(PDOException $e){
            print("failed to remove passenger  : " . $e->getMessage());
            die();
        }
    }
}

?>