<?php

    /* Connect to the MySQL database */
    $dbhost = "localhost";
    $dbuser = "user";
    $dbpass = "pass";
    $dbname = "FinalProject";

    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    /* Using a POST request, determine which 
       function needs to be ran and printed out */
    $result = null;
    switch($_POST["functionName"]) {
        case "0" :
            //$result = buyProduct();
            break;
        case "1" :
            //$result = restock();
            break;
        case "2" :
            //$result = resupply();
            break;
        case "3" :
            //$result = revenue();
            break;
    }

//    $query = "SELECT * FROM Product";
//    $result = $mysqli->query($query);
    
    /* Print Out the Result if there are any */
    if ($result && $result->num_rows > 0) {
        $html = createTable($result);
    } else {
        $html = "<p>No results found.</p>";
    }

    if($result) {
        $result->close();
    }
    $mysqli->close();


    function buyProduct() {
        $query = "";
        $result = $mysqli->query($query);
        
        return $result;
    }

    function restock() {
        $query = "";
        $result = $mysqli->query($query);
        
        return $result;
    }

    function resupply() {
        $query = "";
        $result = $mysqli->query($query);
        
        return $result;
    }

    function revenue() {
        $query = "";
        $result = $mysqli->query($query);
        
        return $result;
    }

    /* Creates a neat table from the result */
    function createTable($result) {
        $table = "<table class='table table-bordered'>";
        $row = $result->fetch_assoc();
        $table .= "<tr>";
        foreach(array_keys($row) as $field) {
            $table .= "<th class='text-center'>$field</th>";
        }
        $table .= "</tr>";
        while($row) {
            $table .= "<tr>";
            foreach($row as $key => $value) {
                $table .= "<td class='text-center'>";
                if(strcmp($key, "Price") == 0) {
                    $table.="\$" . number_format($value, 2, ".", "");
                }
                else {
                    $table .= $value;
                }
                $table.="</td>";
            }
            $table .= "</tr>";
            $row = $result->fetch_assoc();
        }
        $table .= "</table>";
        return $table;
    }
?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Store Database</title>
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="index.css">
        
    </head>
    <body>
        <?php echo $html ?>
    </body>
</html>