<?php 


/*
    Exemple d'utilisation des fonctions de connection
    Création d'un nouveau compte utilisateur :
    $user = User::sign_up(pseudo, password[, is_admin = 0, email=null, first_name=null, last_name=null, age=null ]);

    Récupération d'un compte existant:
    $user = User::sign_in(pseudo, password);

    on peut égalment gérer les exceptions levées en cas de pseudo existants ou d'erreur de mot de passe.

    un utilisateur peut poster un trajet par :
    $user->postTrajet($price, $departure_date, $nb_places, $departure_city, $arrival_city);

*/

include_once "database.php"; // get database object
include_once "trajet.php"; // post trajet

// Exceptions to handle
class PseudoNotFoundException extends Exception {}
class PasswordNotMatchException extends Exception {}
class PseudoAlreadyExistsException extends Exception {}

/**
* Interface for table user for database
*/
class User {
    
    /**
    * datas for one user
    */
    private $id;
    private $email;
    private $age;
    private $first_name;
    private $last_name;
    private $pseudo;
    // private $password; not for server
    private $is_admin;

    //constructor
    private function __construct($id, $pseudo, /*$password,*/ $is_admin = False, $email=null, $first_name=null, $last_name=null, $age=null){
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->email = $email;
        // $this->password= $password; only present in database, server doesn't not have to know about it
        $this->is_admin= $is_admin;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->age = $age;
    }

    /**
    * return an object User by giving an Id
    * @param $id : int id of user
    * @return User | null
    */
    public static function getUserById($id){
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM User WHERE (id = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                return new User($row["id"], $row["pseudo"], /*$row["password"],*/ $row["is_admin"], $row["email"], $row["first_name"], $row["last_name"], $row["age"]);
            }
            // no user was found 
            return null;
        }
        catch(PDOException $e){
            print("failed to sign in  : " . $e->getMessage());
            die();
        }
    }

    /**
    * sign up method 
    * add new user to database
    * @param all nedded to __constructor excepted id
    * @return User 
    * @throws PseudoAlreadyExistsException
    */ 
    public static function sign_up( $pseudo, $password, $is_admin = 0, $email=null, $first_name=null, $last_name=null, $age=null){
        // hash password 
        $hashed_password = hash("sha256", $password);

        // check for pseudo in database
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM User WHERE (pseudo = '$pseudo')";
            if($database->getLink()->query($sql)->rowCount() > 0){
                // pseudo already exists, throw exception
                throw new PseudoAlreadyExistsException("$pseudo already exists, try another pseudo");
            }
        }
        catch(PDOException $e){
            print("failed to check pseudo  : " . $e->getMessage());
            die();
        }

        // try to save it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "INSERT INTO User (pseudo, password, is_admin, email, first_name, last_name, age) 
            VALUES ( :pseudo, :hashed_password, :is_admin, :email, :first_name, :last_name, :age)";
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(
                ':pseudo' => $pseudo,
                ':hashed_password' => $hashed_password,
                ':is_admin' => $is_admin,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':age' => $age,
                ':email' => $email
            ));

        }
        catch(PDOException $e){
            print("failed to sign up  : " . $e->getMessage());
            die();
        }
        return User::sign_in($pseudo, $password);
    }

    /**
    * return the first user idetified by his pseudo and his password
    * @param pseudo string
    * @param password string not hashed
    * @return User
    * @throws PasswordNotMatchException
    * @throws PseudoNotFoundException
    */
    static public function sign_in($pseudo, $password){
        // first, we hash the password
        $hashed_password = hash("sha256", $password);
        // look for the pseudo in database
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM User WHERE (pseudo = :pseudo)";
            //$sql = "SELECT * FROM User WHERE (pseudo = '$pseudo')";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':pseudo'=>$pseudo));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                // check for password 
                if(strcmp($hashed_password, $row["password"]) == 0){
                    // password is the same
                    // create new user object and return it
                    return new User($row["id"], $row["pseudo"], /*$row["password"],*/ $row["is_admin"], $row["email"], $row["first_name"], $row["last_name"], $row["age"]);
                }
                else {
                    // password is not the same, raise an Exception for this
                    throw new PasswordNotMatchException("Password not match for $pseudo account.");
                }
            }
            // cannot find Pseudo
            throw new PseudoNotFoundException("$pseudo is not valid account's pseudo");
        }
        catch(PDOException $e){
            print("failed to sign in  : " . $e->getMessage());
            die();
        }
    }

    /**
    * User post a trajet
    * @return posted trajet
    */
    public function postTrajet($price, $departure_date, $nb_places, $departure_city, $arrival_city){
        return Trajet::addTrajet($price, $departure_date, $nb_places, $this->id, $departure_city, $arrival_city);
    }

    /**
    * Get all trajet posted as driver
    * @return int[] : list of trajet id as driver
    */
    public function listTrajetAsDriver(){
        $list = array();
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT idTrajet FROM `Trajet` WHERE (id_driver = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$this->id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $list[] = intval($row["idTrajet"]);
            }
            return $list;
        }
        catch(PDOException $e){
            print("failed to get Trajet as driver : " . $e->getMessage());
            die();
        }
    }

    /**
    * Get all trajet booked as passenger
    * @return int[] : list of trajet id as passenger
    */
    public function listTrajetAsPassenger(){
        $list = array();
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT Trajet FROM `Passenger_for` WHERE (passenger_id = :id)";
            // we should have only one row for this request
            $statement = $database->getLink()->prepare($sql);
            $statement->execute(array(':id'=>$this->id));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $list[] = intval($row["Trajet"]);
            }
            return $list;
        }
        catch(PDOException $e){
            print("failed to get Trajet as Passenger  : " . $e->getMessage());
            die();
        }
    }


    /**
    * User set hismself as a passenger
    * @param $idTrajet int id of Trajet
    */
    public function setAsPassengerForTrajet($idTrajet){
        Passenger_for::addPassenger($this->id, $idTrajet);
    }

    /**
    * Cancel a trip as passenger 
    * @param $idTrajet int id of Trajet
    */
    public function CancelTripAsPassenger($idTrajet){
        Passenger_for::removePassenger($this->id, $idTrajet);
    }

    // getters
    public function get_id(){
        return $this->id;
    }
     public function get_pseudo(){
        return $this->pseudo;
    }
     public function get_is_admin(){
        return $this->is_admin;
    }
     public function get_age(){
        return $this->age;
    }
     public function get_first_name(){
        return $this->first_name;
    }
     public function get_last_name(){
        return $this->last_name;
    }
    public function get_email(){
        return $this->email;
    }

    // setters
     public function set_is_admin($is_admin){
        $this->is_admin = $is_admin;
    }
     public function set_age($age){
        $this->age = $age;
    }
     public function set_first_name($name){
        $this->first_name = $name;
    }
     public function set_last_name($name){
        $this->last_name = $name;
    }
    public function set_email($email){
        $this->email = $email;
    }
}
?>