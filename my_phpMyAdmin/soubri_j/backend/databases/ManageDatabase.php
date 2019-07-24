<?php    
/*  
    ETNA PROJECT, 30/10/2018 by soubri_j
    my_phpMyAdmin : ManageDatabase.php
    File description:
        File containing all methods to manage MySQL databases.
*/

include_once("../config/queries.php");
include_once("../config/MySqliConnector.php");
include_once("../tables/ManageTable.php");

class ManageDatabase
{
    private $connection;
    private $mysqli_connector;

    /*
        Constructor of the class
        Params : 
            connection : The database connection to use
    */
    public function __construct(mysqli $connection) 
    { 
        $this->connection = $connection;
        $this->mysqli_connector = new MySqliConnector($connection);
    }

    /*
        Function to get all database list
        Returns :
            Array of rows if ok, null otherwise
    */
    public function get_all_databases() : ?array
    {
        $return_array = null;
        $result = $this->mysqli_connector->query(AllQueries::SHOW_DATABASES,
            ErrorMessage::ERROR_CANT_READ_DATABASE_LIST, MYSQLI_NUM);
        
        if ($result) {
            $return_array = array();
            foreach($result as $row) {
                foreach($row as $column => $value) {
                    array_push($return_array, array("database_name" => $value));
                }
            }
        }
        return $return_array;
    }

    /*
        Function used to create a database
        Params :
            database_name : The name of the database to create
        Returns :
            true if database was created sucessfully, false otherwise
    */
    public function create_database(string $database_name) : bool
    {
        return ($this->mysqli_connector->generic_method(
            AllQueries::CREATE_DATABASE,
            $database_name, "Database ".$database_name.
            " succesfully created!"));
    }

    /*
        Function used to rename a database
        Params : 
            database_name : Name of the database to rename
            new_name : New database name
        Returns :
            true if ok, false otherwise.
    */
    public function rename_database(string $database_name, 
        string $new_name) : bool
    {
        $result = false;
        $tables = null;
        $manager = new ManageTable($this->connection, $database_name);

        if ($this->create_database($new_name)) {
            if ($this->mysqli_connector->select_database(
                $database_name)) {
                $tables = $manager->list_all_tables();
                $result = true;
                if ($tables) {
                    foreach ($tables as $row) {
                        foreach ($row as $column => $value) {
                            $result = $manager->rename_table($database_name.".".$value,
                                $new_name.".".$value);
                            if (!$result) {
                                break;
                            }
                        }
                    }
                }
                if ($result) {
                    $result = $this->drop_database($database_name);
                }
            }
        }

        return ($result);
    }

    /*
        Function used to drop a database
        Params : 
            database_name : The name of the database to drop
        Returns :
            true if database was dropped successfully, false otherwise
    */
    public function drop_database(string $database_name) : bool
    {
        return ($this->mysqli_connector->generic_method(
            AllQueries::DROP_DATABASE, $database_name,
            "Database ".$database_name." succesfully dropped!"));
    }

    /*
        Function used to get databases statictics. It returns database name, 
        number of tables, creation_date and memory space
        Returns :
            An array containing statistics if ok, null otherwise
    */
    public function show_statictics(string $database_name) : ?array
    {
        $arr_to_return = null;
        $result = $this->mysqli_connector->prepared_query(
            AllQueries::SHOW_DATABASE_STATISTICS,
            [$database_name => "s"]);

        if ($result) {
            $arr_to_return = array();
            foreach($result[0] as $key => $value) {
                $arr_to_return += array($key => $value);
            }
        }
        return $arr_to_return;
    }
}
?>