<?php 
    error_reporting(0);
    $localhost = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'fapi3';

    $conn = mysqli_connect($localhost, $user, $password, $dbname);
    if(!$conn) { die("Chyba databáze"); }

    $query = "SELECT * FROM produkty ORDER BY id ASC";
    $result = mysqli_query($conn, $query);

    $title = "FAPI | Praktický test";
    
    $count = 0;
    $hlaska ="";
    $zakaznik = "";
    $adresa = "";
    $dph = 21;

    
