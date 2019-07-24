<?php
/*
    ETNA PROJECT, 02/11/2018 by soubri_j
    my_phpMyAdmin : create_database.php
    File description:
        REST API used to create a database.
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include_once("../config/DatabaseConnection.php");
include_once("../config/LogMessage.php");
include_once("../config/Configuration.php");
include_once("ManageDatabase.php");

//  Connecting to database
$database = new DatabaseConnection(Configuration::USER, Configuration::PASSWORD, 
    Configuration::HOSTNAME, Configuration::PORT);
$response_code = 503;
$message = array();
$manager = null;
$db = null;
$result = false;
$data = ""; 

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $manager = new ManageDatabase($db); 
        
        //  Getting posted datas
        $data = json_decode(file_get_contents("php://input"));

        if ($data->database_name) {
            $result = $manager->create_database($data->database_name);
            if ($result) {
                $response_code = 201;
                $message = array("message" => LogMessage::get_success());
            } else {
                $response_code = 503;
                $message = array("error" => LogMessage::get_error());
            }
        } else {
            $response_code = 400;
            $message = array("error" => 
                ErrorMessage::ERROR_DATABASE_NAME_EMPTY);
        }
    }
    else {
        $message = array("error" => ErrorMessage::ERROR_CANT_CONNECT_DATABASE);
    }
} else {
    $message = array("error" => ErrorMessage::ERROR_CANT_CREATE_CONNECTION);
}
http_response_code($response_code);
echo json_encode($message);
?>

