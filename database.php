<?php

    $dbhost = "localhost";
    $dbuser = "user";
    $dbpass = "pass";
    $dbname = "FinalProject";

    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    
    print "Connected! Status:" . $mysqli->host_info . "\n";
    exit;
    $query = " Select * from foodStock where cost > 10";
    $result = $mysqli->query($query);
    print_r($result);
    


     $row = $result->fetch_assoc();
     print_r($row);
 
     while($row = $result->fetch_assoc()) {
         print 'I have ' . $row['quantity'] . ' ' . $row['name'] . ' for $' . $row['cost'];
         print "<br>\n";
     }
    

    $result->close();
    $mysqli->close();
?>