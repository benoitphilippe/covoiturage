<?php 

include_once "database.php"; // get database object

class TableRows extends RecursiveIteratorIterator {
    function __construct($it) {
        parent::__construct($it, self::LEAVES_ONLY);
    }

    function current() {
        return "<td style='width:150px;border:1px solid black;'>" . parent::current(). "</td>";
    }

    function beginChildren() {
        echo "<tr>";
    }

    function endChildren() {
        echo "</tr>" . "\n";
    }
} 

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
    private $password;
    private $is_admin;

    //constructor
    private function __construct($id, $pseudo, $password, $is_admin = False, $email=null, $first_name=null, $last_name=null){
        $this->$id = $id;
        $this->$email = $email;
        $this->password= $password;
        $this->is_admin= $is_admin;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    /**
    * sign up method 
    * add new user to database
    * @param all nedded to __constructor except idate
    * @return User object or null if fail
    */ 
    public static function sign_up( $pseudo, $password, $is_admin = 0, $email='null', $first_name='null', $last_name='null'){
        // hash password 
        $hashed_password = hash("sha256", $password);
        // try to save it on database
        try {
            // connect to database
            $database = new Database();
            $sql = "INSERT INTO User (pseudo, password, is_admin, email, first_name, last_name) VALUES ('$pseudo', '$hashed_password', $is_admin, $email, $first_name, $last_name)";
            $database->getLink()->exec($sql);
        }
        catch(PDOException $e){
            print("failed to sign up  : " . $e->getMessage());
            return null;
        }
        //return User::sign_in($pseudo, $password);
        return 'hey';
    }

    /**
    * return the first user idetified by his pseudo and his password
    * @param pseudo string
    * @param password string not hashed
    * @return User | null
    */
    static public function sign_in($pseudo, $password){
        // first, we hash the password
        $hashed_password = hash("sha256", $password);
        // look for the pseudo in database
        try {
            // connect to database
            $database = new Database();
            $sql = "SELECT * FROM User WHERE ('pseudo' = '$pseudo')";
            print($sql);
            $stmt = $database->getLink()->prepare($sql);
            $stmt->execute();
            $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
            foreach($stmt->fetchAll() as $k=>$v) {
                print ("$k->$v");
            }
        }
        catch(PDOException $e){
            print("failed to sign in  : " . $e->getMessage());
            return null;
        }
    }
}

User::sign_in('bphilippe', 'password');
?>