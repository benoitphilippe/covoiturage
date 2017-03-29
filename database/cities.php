<?php 

/*
    Exemple pour afficher les villes correspondantes à une recherche pour 'Queb'
    $cities = City::searchByName('Queb');
    foreach($cities as $row){
        print($row->toString() . "\n");
    }

    Resultat :
    Quebrangulo, Alagoas, Brazil
    Quebec, Quebec, Canada
    Quebradanegra, Cundinamarca, Colombia
    Quebo, Quinara, Guinea-Bissau

    pour la création d'un nouveau trajet, on prend l'id de la ville selectionnée :
    $city->get_id();
*/

include_once "database.php"; // get database object

/**
* Interfaces for cities in database
*/
class City {
    private $id;
    private $city_name;
    private $state_name;
    private $country_name;

    // contructor
    private function __construct($id, $city_name, $state_name, $country_name){
        $this->id = $id;
        $this->city_name = $city_name;
        $this->state_name = $state_name;
        $this->country_name = $country_name;
    }

    /**
    * Get all the cities matching with given name
    * @param name string, part of the name of city
    * @return City[]
    */
    public static function searchByName($name){
        // create an empty array
        $cities = array();
        try {
            // connect to database
            $database = new Database();
            $sql  = 'SELECT cities.id, cities.name, states.name, countries.name 
            FROM cities JOIN states JOIN countries 
            WHERE cities.name LIKE :citi_name_like 
            AND (cities.state_id = states.id AND states.country_id = countries.id)';
            $statement = $database->getLink()->prepare($sql);
            $name_like = $name . '%';
            $statement->execute(array(':citi_name_like'=> $name_like));
            $result = $statement->fetchAll();
            foreach($result as $row) {
                // $row[0] = cities.id
                // $row[1] = cities.name
                // $row[2] = states.name 
                // $row[3] = counties.name
                $cities[] = new City(intval($row[0]), $row[1], $row[2], $row[3]);
            }
            return $cities;
        }
        catch(PDOException $e){
            print("failed to search city  : " . $e->getMessage());
            die();
        }
    }

    public function toString(){
        return "$this->city_name, $this->state_name, $this->country_name";
    }

    public function get_id(){
        return $this->id;
    }

}
?>