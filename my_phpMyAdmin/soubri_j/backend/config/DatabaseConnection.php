<?php
/*
    ETNA PROJECT, 30/10/2018 by soubri_j
    my_phpMyAdmin : DatabaseConnection.php
    File description:
        File containing methods to manage MySQL connections.
*/

include_once("LogMessage.php");

// Class used to store connections informations
class DatabaseConnection
{
    private $username;
    private $password;
    private $host;
    private $port;
    public $connection;

    public function __construct(string $user, string $pass, 
                                string $host, int $port)
    {
        $this->username = $user;
        $this->password = $pass;
        $this->host = $host;
        $this->port = $port;
    }

    public function __destruct()
    {
        unset($this->username);
        unset($this->password);
        unset($this->host);
        unset($this->port);
    }

    public function username() : string
    {
        return ($this->username);
    }

    public function password() : string
    {
        return ($this->password);
    }

    public function host() : string
    {
        return ($this->host);
    }

    public function port() : int
    {
        return ($this->port);
    }

    /*  
        Method used to connect a user to a MySQL database.
        Returns :
            mysqli connection in case of success, null otherwise
    */
    public function database_connection() : ?mysqli
    {
        $result = null;
        $result = new mysqli($this->host, $this->username, 
                            $this->password, "", $this->port);
        
        if ($result->connect_errno) {
            LogMessage::display_message(
                            MessageType::ERROR, "Error connecting to ".
                            $this->host." with username ".
                            $this->username." : ".
                            $this->connection->getMessage());
            $result = null;
        } else {
            LogMessage::display_message(
                            MessageType::SUCCESS, 
                            SuccessMessage::SUCCESS_CONNECTION);
        }
        return ($result);
    }

    /*  
        Method used to close a MySQL connection
        Returns :
            true if connection has been closed successfully, false otherwise
    */
    public function database_disconnection() : bool
    {
        $result = false;
        if ($this->connection) {
            $result = $this->connection->close();
            if ($result) {
                LogMessage::display_message(
                                MessageType::SUCCESS, 
                                SuccessMessage::SUCCESS_DISCONNECTION);
            } else {
                LogMessage::display_message(
                                MessageType::ERROR, 
                                ErrorMessage::ERROR_CONNECTION_FAILED.
                                $this->connection->error);
            }
        }
        return ($result);
    }
}
?>