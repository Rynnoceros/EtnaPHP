<?php
/*
    ETNA PROJECT, 03/11/2018 by soubri_j
    my_phpMyAdmin : change_column.php
    File description:
        REST API to change a column in a table.
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include_once("../config/Configuration.php");
include_once("../config/DatabaseConnection.php");
include_once("../config/LogMessage.php");
include_once("ManageTable.php");

$database = new DatabaseConnection(Configuration::USER, 
    Configuration::PASSWORD, Configuration::HOSTNAME, Configuration::PORT);
$db = null;
$manager = null;
$response_code = 503;
$message = "";
$data = "";
$result = "";

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->database_name && $data->table_name && $data->column_name && 
            $data->new_name && $data->column_type) {
            $manager = new ManageTable($db, $data->database_name);
            $result = $manager->change_column($data->table_name, 
                $data->column_name, $data->new_name, $data->column_type);
            if ($result) {
                $response_code = 200;
                $message = array("message" => LogMessage::get_success());
            } else {
                $response_code = 500;
                $message = array("error" => LogMessage::get_error());
            }
        } else {
            $response_code = 400;
            $message = array("error" => ErrorMessage::ERROR_WRONG_PARAMETERS);
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