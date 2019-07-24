 <?php
 /*
    ETNA PROJECT, 31/10/2018 by soubri_j
    my_phpMyAdmin : MySqliConnector.php
    File description:
        Connector used to simplifu mysqli use.
*/

include_once("DatabaseConnection.php");
include_once("LogMessage.php");

class MySqliConnector
{
    private $connector = null;
    private $database_name = "";
    private $error = "";
    private $message = "";

    /*  
        Constructor of the class
        Params :
            connector : The database connection to use
            database_name = The name of the database to select
    */
    public function __construct(mysqli $connector, string $database_name = "") 
    {
        $this->connector = $connector;
        $this->select_database($database_name);
    }

    /*  
        Function to get the connector.
        Returns :
            mysqli connection of ok, false otherwise
    */
    public function get_connector() : mysqli
    {
        return ($this->connector);
    }

    /*
        Function to get selected database
        Returns :
            database name
    */
    public function get_database_name() : string{
        return ($this->database_name);
    }

    /*
        Function to close connection and unset the connector
        Returns :
            true if ok, false otherwise
    */
    public function unset_connector() : bool
    {
        $result = true;

        if ($this->connector) {
            $result = database_disconnection(self::$connector);
        }
        return ($result);
    }

    /*  
        Function to select a database to parse
        Params :
            database_name : Name of the database to select
        Returns :
            true if ok, false otherwise
    */
    public function select_database(string $database_name) : bool
    {
        $result = false;

        if($this->connector) {
            $result = $this->connector->select_db($database_name);
            if ($result) {
                $this->database_name = $database_name;
            }
        }
        return ($result);
    }

    /*
        Function used to display an error indicating to user to connect to
        database before querying.
    */
    private function error_no_connection()
    {
        LogMessage::display_message(MessageType::ERROR, 
                                    ErrorMessage::ERROR_NO_CONNECTION);
    }

    /*
        Function used to check if ther was an error during request execution
    */
    private function check_sql_errors()
    {
        if ($this->connector && $this->connector->errno) {
            LogMessage::display_message(MessageType::ERROR, 
                            ErrorMessage::ERROR_EXECUTING_REQUEST.
                            $this->connector->error);
        }
    }

    /*
        Function to check if someone tries to do sql injections using
        parameters of functions.
        Params :
            param : The param to check
    */
    public function check_param_sanity(string &$param)
    {
        $param = $this->connector->real_escape_string($param);
    }

    /*
        Function to check if someone tries to do sql injections using
        parameters of functions.
        Params :
            params : Parameters to check
    */
    public function check_params_sanity(array &$params)
    {
        foreach ($params as $param) {
            while (current($params)) {
                $this->check_param_sanity(current($params));
                next($params);
            }
        }
    }

    /*
        Function used to call generic database call
        Params :
            query : The query to execute
            param : The param for the query
            msg : message to display in case of success
        Returns :
            true if query was executed successfully, false otherwise
    */
    public function generic_method(string $query, string $param, 
                                            string $msg) : bool
    {
        $result = false;

        if ($this->check_connector()) {
            $this->check_param_sanity($param);
            $result = $this->connector->query($query.$param);
            if ($result) {
                LogMessage::display_message(MessageType::SUCCESS, $msg);
            }
        }
        $this->check_sql_errors();
        return ($result);
    }

    /*  
        Function used to call generic queries returning array of rows
        Params :
            query : The query to execute
            error_msg : Error message to display in error case
        Returns :
            An array containing results if ok, null otherwise
    */
    public function query(string $query, string $error_msg, 
        int $result_type = MYSQLI_ASSOC) : ?array
    {
        $result = null;

        if ($this->check_connector()) {
            $result = $this->connector->query($query);
            if ($result && gettype($result) == "object") {
                return $result->fetch_all($result_type);
            } else if ($result) {
                $result = null;
                LogMessage::display_message(MessageType::SUCCESS, 
                    SuccessMessage::SUCCESS_QUERY);
            }
            else {
                $result = null;
                LogMessage::display_message(MessageType::ERROR, 
                                $error_msg.
                                $this->connector->error);
            }
        }
        $this->check_sql_errors();
        return ($result);
    }

    /*  
        Function used to call queries with one param
        Params :
            query : The query to execute
            param : The param to pass to the query
            error_msg : Error message to display in error case
        Returns :
            An array containing results if ok, null otherwise
    */
    public function query_with_param(string $query, string $param,
        string $error_msg) : ?array
    {
        $result = null;

        if ($this->check_connector()) {
            $this->check_param_sanity($param);
            $result = $this->connector->query($query.$param);
            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            else {
                $result = null;
                LogMessage::display_message(MessageType::ERROR, 
                                $error_msg.
                                $this->connector->error);
            }
        }
        $this->check_sql_errors();
        return ($result);
    }

    /*
        Function used to execute generic prepared statement that returns
        array of rows.
        Params :
            query : The prepared statement to execute
            params : All parameters of the prepared statement
        Returns :
            An array containing results if ok, null otherwise
    */
    public function prepared_query(string $query, array $params)
        : ?array
    {
        $param_array = array();
        $param_array_temp = array();
        $types = "";
        $param_number = 0;
        $return_array = null;
        $result = true;

        if ($this->check_connector()) {
            $state = $this->connector->prepare($query);
            if ($state) {
                foreach($params as $param => $type) {
                    $types .= $type;
                    $param_array_temp[] = $param;
                }
                $param_array[] = & $types;
                $param_number = count($params);
                for ($i = 0; $i < $param_number; $i++) {
                    $param_array[] = & $param_array_temp[$i];
                }
                call_user_func_array(array($state, 'bind_param'),$param_array);
                $result = $state->execute();
                if ($result) {
                    $result = $state->get_result();
                    if ($result) {
                        $return_array = $result->fetch_all(MYSQLI_ASSOC);
                    }
                }
            }
        }
        $this->check_sql_errors();
        return ($return_array);
    }

    /*  
        Function used to check if a user is connected or not
        Returns :
            true if the user is connected, false otherwise
    */
    public function check_connector() : bool
    {
        $result = false;

        if ($this->connector) {
            $result = true;
        } else {
            $this->error_no_connection();
        }
        return ($result);
    }

    /*
        Function used to check if user has selected a database
        Returns :
            true if a user has selected a database, false otherwise
    */
    public function check_database_name() : bool
    {
        $result = false;

        if ($this->database_name) {
            $result = true;
        } else {
            LogMessage::display_message(MessageType::ERROR, 
                            ErrorMessage::ERROR_MUST_SELECT_DATABASE);
        }
        return ($result);
    }
}
?>