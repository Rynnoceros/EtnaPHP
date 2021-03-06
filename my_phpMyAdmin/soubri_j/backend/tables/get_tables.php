<?php
/*
    ETNA PROJECT, 02/11/2018 by soubri_j
    my_phpMyAdmin : get_tables.php
    File description:
        REST API to get all tables of a database.
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

include_once("../config/Configuration.php");
include_once("../config/DatabaseConnection.php");
include_once("../config/LogMessage.php");
include_once("ManageTable.php");

$database = new DatabaseConnection(Configuration::USER, 
    Configuration::PASSWORD, Configuration::HOSTNAME, Configuration::PORT);
$db=null;
$manager=null;
$response_code = 503;
$message= "";
$database_name= "";
$data_arr = array();

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $database_name = $_GET["database_name"];
        if ($database_name) {
            $manager = new ManageTable($db, $database_name);

            $result = $manager->list_all_tables();
            if ($result) {
                $data_arr['data'] = $result;
                $response_code = 200;
                $message = $data_arr;
            } else if ($result === null) {
                $response_code = 200;
                $message = array("message" => 
                    ErrorMessage::ERROR_NO_TABLES_FOUND);
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
} else {
    $message = array("error" => ErrorMessage::ERROR_CANT_CREATE_CONNECTION);
}
http_response_code($response_code);
echo json_encode($message);
?>

