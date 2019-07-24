<?php
/*
    ETNA PROJECT, 30/10/2018 by soubri_j
    my_phpMyAdmin : LogMessage.php
    File description:
        Method used to display message to the user.
*/

class MessageType 
{
    const ERROR = 0;
    const SUCCESS = 1;
}

class ErrorMessage
{
    const ERROR_MUST_SELECT_DATABASE = "You must select a database to display".
        " its tables!!!";
    const ERROR_NO_SQL_INJECTION = "No sql injection!!!";
    const ERROR_CONNECTION_FAILED = "Failed to close connection: ";
    const ERROR_EXECUTING_REQUEST = "Error executing request : ";
    const ERROR_NO_CONNECTION = "Can't request database. ".
        "No connection!!! Please call method instantiate_connector before".
        " using any method.";
    const ERROR_CANT_READ_DATABASE_LIST = "Can't read databases list : ";
    const ERROR_CANT_CONNECT_DATABASE = "Can\'t connect to database";
    const ERROR_CANT_CREATE_CONNECTION = "Can\'t create DatabaseConnection";
    const ERROR_DATABASE_NAME_EMPTY = "Database name empty!";
    const ERROR_NO_DATABASE_FOUND = "No database found!";
    const ERROR_NO_TABLES_FOUND = "No tables found!";
    const ERROR_WRONG_PARAMETERS = "Parameters are incorrect!";
}

class SuccessMessage
{
    const SUCCESS_DISCONNECTION = "Connection successfully closed!";
    const SUCCESS_CONNECTION = "Successfull connection!";
    const SUCCESS_TABLE_RENAMED = "Table successfully renamed!";
    const SUCCESS_COLUMN_ADDED = "Column successfully added!";
    const SUCCESS_COLUMN_DROPPED = "Column successfully dropped!";
    const SUCCESS_COLUMN_CHANGED = "Column successfully updated!";
    const SUCCESS_INSERT = "Row inserted successfully!";
    const SUCCESS_DELETE = "Row deleted successfully!";
    const SUCCESS_UPDATE = "Row updated successfully!";
    const SUCCESS_QUERY = "Query executed successfully!";
}

class LogMessage
{
    static $error = "";
    static $success = "";

    private function __construct() {}

    /*  
        Method used to display different type of messages
        Params :
            message_type : The type of message
            message : The message to display
    */
    public static function display_message(int $message_type, string $message)
    {
        self::$error= "";
        self::$success = "";

        switch ($message_type) {
            case MessageType::ERROR : 
                self::$error = "Error : ".$message; break;
            case MessageType::SUCCESS : 
                self::$success = "Success : ".$message; break;
            default :
                self::$error = "Unknown message type!!!";
        }
    }

    /*
        Method to get the last generated error
        Returns : 
            the last generated error
    */
    public static function get_error() : string
    {
        return self::$error;
    }

    /*
        Method to get the last success message
    */
    public static function get_success() : string
    {
        return self::$success;
    }
}
?>
