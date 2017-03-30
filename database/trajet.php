<?php 

include_once "database.php";
include_once "passenger_for.php";

class Trajet {
    private $id;
    private $price; // int
    private $departure_date; // date format YYYY-MM-DD HH:MM:SS
    private $nb_places;
    private $id_driver; // to User::id
    private $departure_city; // to City::id 
    private $arrival_city; // to City::id
    private $canceled; // boolean, False by default

    private function __construct($id, $price, $departure_date, $nb_places, $id_driver, $departure_city, $arrival_city, $canceled){
        $this->id = $id;
        $this->price = $price;
        $this->departure_date = $departure_date;
        $this->nb_places = $nb_places;
        $this->id_driver = $id_driver;
        $this->departure_city = $departure_city;
        $this->arrival_city = $arrival_city;
        $this->canceled = $canceled;
    }
    /**
    * return number places left for this Trajet
    * @return int
    */
    public function countNbPlacesLeft(){
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT COUNT(`Trajet`) FROM `Passenger_for` WHERE (Trajet = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$this->id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                return $this->nb_places - $row["COUNT(`Trajet`)"];
            }
            return null;
        }
        catch(PDOException $e){
            print("failed to get number places left  : " . $e->getMessage());
            die();
        }
    }
    /**
    * Search Trajets by giving departure date, deaprture city, and arrival city
    * Ex : Trajet::search(date("Y-m-d H:i:s" , mktime(0,0,0,5,10,1995)["1995-05-10 00:00:00"]), 10 [Akividu], 12[]);
    * @param $departure_date : string format datetime sql like "y-m-d H:i:s". Everything that is after this date will be catch. 
    * @param $departure_city : int id of city
    * @param $arrival_city : int id of city
    * @return Trajet[] : list of Trajet matching this search
    */
    public static function search($departure_date, $departure_city, $arrival_city){
        $list = array();
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM `Trajet` WHERE (departure_date > :date AND departure_city = :departure_city AND arrival_city = :arrival_city)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(
                ':date'=>$departure_date,
                ':departure_city'=>$departure_city,
                ':arrival_city'=>$arrival_city
                ));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $list[] = new Trajet($row["idTrajet"], $row["price"], $row["departure_date"], $row["nb_places"], $row["id_driver"], $row["departure_city"], $row["arrival_city"], $row["canceled"]);
            }
            return $list;
        }
        catch(PDOException $e){
            print("failed to search trajet : " . $e->getMessage());
            die();
        }
    }

    /**
    * Get Trajet object by Id 
    * @param $id : int Trajet id 
    * @return Trajet | null
    */
    public static function getTrajetById($id){
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM Trajet WHERE (idTrajet = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                return new Trajet($row["idTrajet"], $row["price"], $row["departure_date"], $row["nb_places"], $row["id_driver"], $row["departure_city"], $row["arrival_city"], $row["canceled"]);
            }
            // no trajet was found 
            return null;
        }
        catch(PDOException $e){
            print("failed to get Trajet by id  : " . $e->getMessage());
            die();
        }
    }

    /**
    * Cancel a Trajet by putting "canceled" attribut to True
    */
    public function cancel(){
        // try to change canceled attribut it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "UPDATE `Trajet` SET `canceled` = '1' WHERE `Trajet`.`idTrajet` = :id";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(":id" => $this->id));
        }
        catch(PDOException $e){
            print("failed to cancel a Trajet  : " . $e->getMessage());
            die();
        }
        // success, chande it on object 
        $this->canceled = True;
    }

    /**
    * Uncancel a Trajet by putting "canceled" attribut to False
    */
    public function uncancel(){
        // try to change canceled attribut it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "UPDATE `Trajet` SET `canceled` = '0' WHERE `Trajet`.`idTrajet` = :id";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(":id" => $this->id));
        }
        catch(PDOException $e){
            print("failed to uncancel a trajet: " . $e->getMessage());
            die();
        }
        // success, chande it on object 
        $this->canceled = False;
    }

    /**
    * Add new Trajet on database, return Trajet created
    * @param $price int 
    * @param $depature_date Date
    * @param $nb_places int 
    * @param $id_driver int
    * @param $depature_city City::id 
    * @param $arrival_city City::id
    * @return Trajet
    */
    public static function addTrajet($price, $departure_date, $nb_places, $id_driver, $departure_city, $arrival_city){
        // try to save it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "INSERT INTO Trajet (price, departure_date, nb_places, id_driver, departure_city, arrival_city) 
            VALUES ( :price, :departure_date, :nb_places, :id_driver, :departure_city, :arrival_city)";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(
                ':price' => $price,
                ':departure_date' => $departure_date,
                ':nb_places' => $nb_places,
                ':id_driver' => $id_driver,
                ':departure_city' => $departure_city,
                ':arrival_city' => $arrival_city
            ));
        }
        catch(PDOException $e){
            print("failed to add new trajet  : " . $e->getMessage());
            die();
        }

        // row inserted, now get id 
        try {
            // connect to database
            $database = new Database();
            $sql  = 'SELECT idTrajet 
            FROM Trajet
            WHERE (price = :price AND departure_date = :departure_date AND nb_places = :nb_places
            AND id_driver = :id_driver AND departure_city = :departure_city AND arrival_city = :arrival_city 
            AND canceled = 0)';
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(
                ':price' => $price,
                ':departure_date' => $departure_date,
                ':nb_places' => $nb_places,
                ':id_driver' => $id_driver,
                ':departure_city' => $departure_city,
                ':arrival_city' => $arrival_city
            ));
            $result = $statement->fetchAll();
            // take first result for id
            $id = intval($result[0][0]);
            return new Trajet($id, $price, $departure_date, $nb_places, $id_driver, $departure_city, $arrival_city, 0);
        }
        catch(PDOException $e){
            print("failed to get Trajet id : " . $e->getMessage());
            die();
        }
        return null; // problem 
    }
    /**
    * @return int[] : list of the passengers' ids
    */
    public function listPassengers(){
        $list = array();
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT passenger_id FROM `Passenger_for` WHERE (Trajet = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$this->id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $list[] = intval($row["passenger_id"]);
            }
            return $list;
        }
        catch(PDOException $e){
            print("failed to list passenger  : " . $e->getMessage());
            die();
        }
    }

    /**
    * @return the User id of the driver
    */
    public function getDriver(){
        return $this->id_driver;
    }
}

/*
*   $trajet = Trajet::addTrajet(20, date("Y-m-d H:i:s"), 4, 2, 10, 12);
*   $trajet->cancel();
*   var_dump($trajet);
*
*/
?>