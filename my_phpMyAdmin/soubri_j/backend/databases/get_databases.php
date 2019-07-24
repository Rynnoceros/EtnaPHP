<?php
/*
    ETNA PROJECT, 02/11/2018 by soubri_j
    my_phpMyAdmin : get_databases.php
    File description:
        REST API to get all databases.
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

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
$nb_rows = 0;
$result = "";
$response_code = 503;

if ($database) {
    $db = $database->database_connection();
    if ($db) {
        $manager = new ManageDatabase($db);
        
        //  Read all databases
        $result = $manager->get_all_databases();
        
        //  Count rows
        $nb_rows = count($result);
        
        if ($nb_rows > 0) {
            for ($i = 0; $i < $nb_rows; $i++) {
                array_push($database_arr['data'], $result[$i]);
            }
            $response_code = 200;
            $message = $database_arr;
        } else {
            $response_code = 204;
            $message = array("message" => 
                ErrorMessage::ERROR_NO_DATABASE_FOUND);
        }
    } else {
        $message = array("error" => ErrorMessage::ERROR_CANT_CONNECT_DATABASE);
    }
}
http_response_code($response_code);
echo json_encode($message)
?>