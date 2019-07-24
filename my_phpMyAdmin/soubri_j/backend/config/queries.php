<?php
/*
    ETNA PROJECT, 30/10/2018 by soubri_j
    my_phpMyAdmin : queries.php
    File description:
        File containing all queries.
*/

class AllQueries 
{
    const SHOW_DATABASES = "SHOW DATABASES;";
    const CREATE_DATABASE = "CREATE DATABASE ";
    const DROP_DATABASE = "DROP DATABASE ";
    const SHOW_DATABASE_STATISTICS = "SELECT 
        COUNT(TABLE_NAME) AS
        nb_tables, MIN(CREATE_TIME) AS creation_date, 
        SUM(DATA_LENGTH+INDEX_LENGTH) AS memory_space 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = ? GROUP BY TABLE_SCHEMA";
    const SELECT_TABLES_FROM_DATABASE = "SELECT table_name,
        table_rows as nb_tables FROM 
        INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?;";
    const ALTER_TABLE = "ALTER TABLE ";
    const RENAME_TO = " RENAME TO ";
    const ADD_COLUMN = " ADD COLUMN ";
    const DROP_COLUMN = " DROP COLUMN ";
    const CHANGE_COLUMN = " CHANGE COLUMN ";
    const SHOW_TABLE_STATISTICS = "SELECT count(*) as TABLE_ROWS FROM "; 
    const SHOW_COLUMNS_FROM = "SHOW COLUMNS FROM ";
    const SELECT_ALL_ROWS = "SELECT * FROM ";
    const INSERT_INTO = "INSERT INTO ";
    const DELETE_FROM_TABLE = "DELETE FROM ";
    const UPDATE_TABLE = "UPDATE ";
    const VALUES = " VALUES (";
    const WHERE = " WHERE ";
}
?>