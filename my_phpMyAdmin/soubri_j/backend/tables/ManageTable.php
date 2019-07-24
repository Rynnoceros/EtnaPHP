<?php
/* 
    ETNA PROJECT, 01/11/2018 by soubri_j
    my_phpMyAdmin : ManageTable.php
    File description:
        File containing all methods to manage MySQL tables.
*/

include_once("../config/queries.php");
include_once("../config/MySqliConnector.php");

class ManageTable
{
    private $connection;
    private $mysqli_connector;
    
    /*
        Constructor of the class
        Params :
            connection : The database connection to use
    */
    public function __construct(mysqli $connection, string $database_name) 
    { 
        $this->connection = $connection;
        $this->mysqli_connector = new MySqliConnector($connection, $database_name);
    }

    /*
        Function to list all tables of a database
        Returns :
            An array containing all tables if ok, null otherwise
    */
    public function list_all_tables() : ?array
    {
        $return_array = null;
        $result = null;
        $array = null;

        if ($this->mysqli_connector->check_connector() && 
            $this->mysqli_connector->check_database_name()) {
            $result = $this->mysqli_connector->prepared_query(
                AllQueries::SELECT_TABLES_FROM_DATABASE,
                [$this->mysqli_connector->get_database_name() => "s"]);
            if ($result) {
                $return_array = array();
                foreach($result as $row) {
                    $array = array();
                    foreach($row as $column => $value) {
                        $array += array($column => $value);
                    }
                    array_push($return_array, $array);
                }
            }
        }
        return ($return_array);
    }

    /*
        Function to rename a table.
        Params :
            table_to_rename : The table to rename
            new_name : The new name of the table
        Returns :
            true if table was successfully renamed, false otherwise
    */
    public function rename_table(string $table_to_rename,
                                string $new_name) : bool
    {
        $result = false;

        if ($this->mysqli_connector->check_connector()) {
            $this->mysqli_connector->check_param_sanity($table_to_rename);
            $result = $this->mysqli_connector->generic_method(
                AllQueries::ALTER_TABLE.$table_to_rename.
                AllQueries::RENAME_TO, $new_name,
                SuccessMessage::SUCCESS_TABLE_RENAMED);
        }
        return ($result);
    }

    /*
        Function used to add a column to table
        Params : 
            table_to_alter : Table to alter
            column_to_add : Name of the column to add
            column_type : Type of the column
    */
    public function add_column_to_table(string $table_to_alter,
                                                string $column_to_add,
                                                string $column_type) : bool
    {
        $result = false;
        $array_parameters = array($table_to_alter,$column_to_add);

        if ($this->mysqli_connector->check_connector()) {
            $this->mysqli_connector->check_params_sanity($array_parameters);
                $result = $this->mysqli_connector->generic_method(
                    AllQueries::ALTER_TABLE.$table_to_alter.
                    AllQueries::ADD_COLUMN.$column_to_add." ",
                    $column_type, SuccessMessage::SUCCESS_COLUMN_ADDED);
        }
        return ($result);
    }

    /*
        Function used to remove a column from a table
        Params :
            table_to_alter : The table to alter
            column_to_remove : The column to remove
        Returns :
            true if the column was removed, false otherwise
    */
    public function drop_column_from_table(string $table_to_alter,
                                            string $column_to_remove) : bool
    {
        $result = false;

        if ($this->mysqli_connector->check_connector()) {
            $this->mysqli_connector->check_param_sanity($table_to_alter);
            $result = $this->mysqli_connector->generic_method(
                AllQueries::ALTER_TABLE.$table_to_alter.
                AllQueries::DROP_COLUMN, $column_to_remove,
                SuccessMessage::SUCCESS_COLUMN_DROPPED);
        }
        return ($result);
    }

    /*
        Function used to change a column definition
        Params :
            table_to_alter : The table to alter
            column_to_modify : The column to alter
            new_column_name : The new column name
            new_column_definition : The new column definition
        Returns : 
            true if column succesfully modified, false otherwise
    */
    public function change_column(string $table_to_alter,
        string $column_to_modify, string $new_column_name, 
        string $new_column_definition) : bool
    {
        $result = false;
        $array_check = array($table_to_alter, $column_to_modify, $new_column_name);

        if ($this->mysqli_connector->check_connector()) {
            $this->mysqli_connector->check_params_sanity($array_check);
            $result = $this->mysqli_connector->generic_method(
                AllQueries::ALTER_TABLE.$table_to_alter.
                AllQueries::CHANGE_COLUMN.$column_to_modify." ".
                $new_column_name." ", $new_column_definition." ", 
                SuccessMessage::SUCCESS_COLUMN_CHANGED);
        }
        return ($result);
    }

    /*
        Function used to show statistics on a table.
        Params : 
            table_name : Table name to show statistics
        Returns :
            an array containing statistics of the table if ok, null otherwise
    */
    public function show_statistics(string $table_name) : ?array
    {
        return ($this->mysqli_connector->query(
            AllQueries::SHOW_TABLE_STATISTICS.$table_name,
            ErrorMessage::ERROR_EXECUTING_REQUEST));
    }

    /*
        Function used to show columns of a table
        Params :
            tbale_name : The table to look at
        Returns :
            an array containing table columns if ok, null otherwise
    */
    public function show_columns(string $table_name) : ?array
    {
        return ($this->mysqli_connector->query(
            AllQueries::SHOW_COLUMNS_FROM.$table_name,
            ErrorMessage::ERROR_EXECUTING_REQUEST));
    }

    /*
        Function used to display all rows of a table
        Params :
            table_name : The table name to list
        Returns :
            an array containing all rows of the table of ok, null otherwise
    */
    public function select_all_rows(string $table_name) : ?array
    {
        return ($this->mysqli_connector->query_with_param(
            AllQueries::SELECT_ALL_ROWS, $table_name, 
            ErrorMessage::ERROR_EXECUTING_REQUEST));
    }

    /*
        Function used to insert a row in a table
        Params :
            table_name : Table name where to insert the row
        Returns :
            true if row successfully inserted, false otherwise
    */
    public function insert_into_table(string $table_name, array $values)
        : bool
    {
        $result = false;
        $param_number = 0;
        $query = AllQueries::INSERT_INTO;

        $this->mysqli_connector->check_param_sanity($table_name);
        $this->mysqli_connector->check_params_sanity($values);
        $query .= " ".$table_name.AllQueries::VALUES;
        $param_number = count($values);

        foreach ($values as $name => $value) {
            $query .= "'".$value."',";
        }
        $query = substr($query, 0, strlen($query) - 1).");";
        //foreach ($values[$param_number - 1] as $name => $value) 
        //    $query .= "'".$value."');";
        $result = $this->mysqli_connector->generic_method($query, "",
            SuccessMessage::SUCCESS_INSERT);
        return ($result);
    }

    /*
        Function to remove rows from a table
        Params :
            table_name : Table name where to delete
            criterias : Array of criterias;
        Returns :
            true if ok, false otherwise
    */
    public function delete_from_table(string $table_name, 
        array $criterias) : bool
    {
        $result = false;
        
        $query = AllQueries::DELETE_FROM_TABLE.$table_name." ";
        $query = $this->fill_query_criterias($query, $criterias);
        $result = $this->mysqli_connector->generic_method($query, "",
            SuccessMessage::SUCCESS_DELETE);
        
        return ($result);
    }

    /*
        Function used to update rows of a table
        Params :
            table_name : Table to update
            new_values : Modified values
            criterias : Criterias to select rows to modify
        Returns :
            true if update success, false otherwise
    */
    public function update_table(string $table_name,
        array $new_values, array $criterias) : bool
    {
        $result = false;
        $values_number = count($new_values);
        $query = AllQueries::UPDATE_TABLE.$table_name." SET ";
        if ($values_number > 0) {
            foreach ($new_values as $name => $value) {
                $query .= $name." = '".$value."',";
            }
            $query = substr($query, 0, strlen($query) - 1);
            $query = $this->fill_query_criterias($query, $criterias);
            $result = $this->mysqli_connector->generic_method($query, "",
                SuccessMessage::SUCCESS_UPDATE);
        }

        return ($result);
    }

    /*
        Function to execute a query in a database
        Params : 
            query : The query to execute
        Returns :
            an array containing query results
    */
    public function execute_query(string $query) : ?array
    {
        $result_arr = array();
        $result = $this->mysqli_connector->query($query, 
            ErrorMessage::ERROR_EXECUTING_REQUEST);

        if (!$result)
        {
            if (LogMessage::get_success()) {
                $result_arr = array("message" => LogMessage::get_success());
            } else {
                $result_arr = $result;
            }
        } else {
            $result_arr['data'] = $result;
        }

        return ($result_arr);
    }

    /*
        Function to complete query with where condition
        Params :
            query : The query to complete
            criterias : The criterias to parse
        Returns :
            The completed query
    */
    private function fill_query_criterias(string $query, 
        array $criterias) : string 
    {
        $param_number = count($criterias);
        $i = 0;
        $comparison = "";

        if ($param_number > 0) {
            foreach ($criterias as $name => $value) {
                if ($value == "") {
                    $comparison = "(".$name." is NULL OR ".$name." = '') ";
                } else {
                    $comparison = $name." = '".$value."' ";
                }
                if ($i == 0) {
                    $query .= AllQueries::WHERE." ".$comparison;
                } else {
                    $query .= " AND ".$comparison;
                }
                $i++;
                //next($criterias);
            }
        }
        return $query;
    }
}
?>