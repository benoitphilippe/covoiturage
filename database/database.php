<?php 

include_once "const.php";

class Database {
    private $link; // link to mysql database

    /**
    * @return link object to database
    */
    public function getLink(){
        return $this->link;
    }
    /**
    * constructor database
    */
    function __construct(){
        try {
            $this->link = new PDO("mysql:host=". HOST .";dbname=". DATABASE, USER, PASSWORD);
            // set the PDO error mode to exception
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
?>