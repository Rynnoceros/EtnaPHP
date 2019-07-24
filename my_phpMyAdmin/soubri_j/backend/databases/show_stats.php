<?php
/*
    ETNA PROJECT, 02/11/2018 by soubri_j
    my_phpMyAdmin : show stats.php
    File description:
        REST API to get statistics on a database.
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include_once("ManageDatabase.php");
include_once("../config/DatabaseConnection.php");
include_once("../config/LogMessage.php");
include_once("../config/Configuration.php");

//  Instantiation of db connection
$database = new DatabaseConnection(Configuration::USER, Configuration::PASSWORD, 
    Configuration::HOSTNAME, Configuration::PORT);
$message = array();
$db = null;
$manager = null;
$database_arr = array();
$database_arr['data'] = array();
$data = "";
$result = "";
$response_code = 503;

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $manager = new ManageDatabase($db);
        
        // Read parameters
        $data = json_decode(file_get_contents("php://input"));
        if ($data->database_name) {
            //  Read all databases
            $result = $manager->show_statictics($data->database_name);
            
            if ($result) {
                $database_arr['data']= array_merge($database_arr['data'], 
                                                    $result);
                $response_code = 200;
                $message = $database_arr;
            } else {
                $message = array("error" => LogMessage::get_error());
            }
        } else {
            $response_code = 400;
            $message = array("error" => 
                ErrorMessage::ERROR_DATABASE_NAME_EMPTY);
        }
    } else {
        $message = array("error" => ErrorMessage::ERROR_CANT_CONNECT_DATABASE);
    }
}
http_response_code($response_code);
echo json_encode($message)
?>