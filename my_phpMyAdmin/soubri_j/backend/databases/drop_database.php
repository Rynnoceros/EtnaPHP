<?php
/*
    ETNA PROJECT, 02/11/2018 by soubri_j
    my_phpMyAdmin : drop_database.php
    File description:
        REST API used to drop a database.
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
$db = null;
$manager = null;
$data = "";
$result = "";

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $manager = new ManageDatabase($db);
        $data = json_decode(file_get_contents("php://input"));

        if ($data->database_name) {
            $result = $manager->drop_database($data->database_name);
            if ($result) {
                $response_code = 200;
                $message = array("message" => LogMessage::get_success());
            } else {
                $message = array("error" => LogMessage::get_error());
            }
        } else {
            $response_code = 400;
            $message = array("error" => ErrorMessage::ERROR_DATABASE_NAME_EMPTY);
        }
    } else {
        $message = array("error" => ErrorMessage::ERROR_CANT_CONNECT_DATABASE);
    }
} else {
    $message = array("error" => ErrorMessage::ERROR_CANT_CREATE_CONNECTION);
}
http_response_code($response_code);
echo json_encode($message);
?>