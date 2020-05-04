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
    $add = false;
    $delete = false;
    switch($_POST["functionName"]) {
        case "0" :
            $add=True;
            $result = buyProduct($mysqli);
            break;
        case "1" :
            $result = restock($mysqli);
            break;
        case "2" :
            $result = resupply($mysqli);
            break;
        case "3" :
            $result = revenue($mysqli);
            break;
        case "4":
            $result = employeeSpecial($mysqli);
            break;
        case "5":
            $result = memberStatus($mysqli);
            break;
        case "6":
            $result = lowStock($mysqli);
            break;
        case "7":
            $result = changeCustomerStatus($mysqli);
            break;
        case "8":
            $result = minSupplier($mysqli);
            break;
        case "9" :
            $add=True;
            $result = addCustomer($mysqli);
            break;
        case "10" :
            $add=True;
            $result = addEmployee($mysqli);
            break;
        case "11" :
            $delete = true;
            $result = removeEmployee($mysqli);
            break;
        case "12":
            $result = showProducts($mysqli);
            break;
        case "13":
            $result = showEmployees($mysqli);
            break;
        case "14" :
            $result = showCustomers($mysqli);
            break;
        case "15":
            $result = showSuppliers($mysqli);
            break;
        }

//    $query = "SELECT * FROM Product";
//    $result = $mysqli->query($query);
    
    /* Print Out the Result if there are any */
    if($add && !$result) {
        $html = "<p>Unable to add the tuple</p>";
    }
    else if($delete && !$result){
        $html = "<p>Unable to delete the tuple</p>";
    }
    else if ($result && $result->num_rows > 0) {
        $html = createTable($result);
    } else {
        $html = "<p>No results found.</p>";
    }
    
    if($result) {
        $result->close();
    }
    $mysqli->close();


    function buyProduct($mysqli) {
        $prod_ID = $_POST["prodID"];
        $cust_ID = $_POST["customerID"];
        // get the number of products on the shelf...
        $result = $mysqli -> query( "SELECT OnShelf FROM Product " . 
                                        "WHERE ID = $prod_ID");
        //decrement the number and update
        $row = $result->fetch_assoc(); //Do some error and return null if it has no rows
        if(count($row) == 0) {
            return NULL;
        }
        $newNomber = $row['OnShelf'] - 1;
        $mysqli -> query(   "UPDATE Product SET OnShelf=$newNomber " .  
                            "WHERE ID = $prod_ID");
        // add transaction to Purchase table...

        $sqlDate = date('Ymd'); 
        $query =    "INSERT INTO Purchase (CustomerID, ProdID, SellDate) VALUES ($cust_ID, $prod_ID, $sqlDate);";
        $mysqli->query($query);     
        $result = $mysqli -> query("SELECT * FROM Purchase WHERE CustomerID = $cust_ID");
        return $result;
    }

    function restock($mysqli) {
        $prod_ID = $_POST["prodID"];
        $crit_NO = $_POST["critNum"];

        $res = $mysqli -> query("SELECT OnShelf,InStorage FROM Product 
                                 WHERE ID = $prod_ID ");
        $row = $res -> fetch_assoc();
        if(count($row) == 0) {
            return NULL;// Product with specified ID doesn't exist
        }
        $numberOnShelf = $row['OnShelf'];
        $numberInStorage = $row['InStorage'];
        if ($crit_NO > $numberInStorage) { // not enough in the storage...
            echo "<p>Not Enough in Storage</p>";
            exit;
        }
        $numberInStorage -= $crit_NO;
        $numberOnShelf += $crit_NO;
        $mysqli -> query(   "UPDATE Product 
                            SET OnShelf = $numberOnShelf, InStorage = $numberInStorage 
                            WHERE ID = $prod_ID" );
        $result = $mysqli -> query("SELECT * FROM Product WHERE ID = $prod_ID");
        return $result;
    }

    function resupply($mysqli) {
        $quantity = $_POST["quantity"];
        $pname = $_POST["pname"];
        
        $res = $mysqli -> query("SELECT InStorage FROM Product WHERE Name = '$pname' ");
        if(!$res) {
            return NULL;
        }
        $row = $res -> fetch_assoc();
        if(count($row) == 0) {
            return NULL;
        }
        $prodInStore = $row['InStorage'];
        $prodInStore += $quantity;
        $mysqli -> query(" UPDATE Product SET InStorage = $prodInStore WHERE Name = '$pname' ");

        $result = $mysqli->query("SELECT * FROM Product WHERE Name = '$pname'");
        return $result;
    }

    function revenue($mysqli) {
        $phpDate = $_POST["saleDate"];
        $sqlDate = date('Ymd', strtotime($phpDate));
        $price = $_POST["priceThreshold"];
        $result = $mysqli->query("  SELECT ProdID, SUM(Price) AS Revenue
                                    FROM Purchase Pu, Product Pr 
                                    WHERE Pu.ProdID = Pr.ID AND Pr.Price < $price AND SellDate >= $sqlDate
                                    GROUP BY Pr.ID");
        
        return $result;
    }

    function employeeSpecial($mysqli){
        
        $result = $mysqli->query("SELECT Se.Name, COUNT(Sp.EmpID) as Employees
                                FROM Specialization Sp, Section Se 
                                WHERE Sp.SectionNo = Se.Number 
                                GROUP BY Se.Name");
        return $result;
    }

    function memberStatus($mysqli){
        $result = $mysqli->query(
            "SELECT *
            FROM
            (SELECT COUNT(ID) as Members
            FROM Customer
            WHERE MemberStatus = TRUE) as M,
            (SELECT COUNT(ID) as Nonmembers
            FROM Customer
            WHERE MemberStatus = FALSE) as N"
        );

        return $result;
    }

    function lowStock($mysqli){
        $limit = 3;
        $result = $mysqli->query(
            "SELECT Name, (OnShelf + InStorage) as Total
            FROM Product
            ORDER BY Total ASC LIMIT $limit"
        );
        return $result;
    }

    function changeCustomerStatus($mysqli){
        $statusChange = $_POST["statusChange"] == "makeMember" ? 1 : 0;
        $cust_ID = $_POST["customerID"];
        $result = $mysqli->query(
            "UPDATE Customer SET MemberStatus = $statusChange
            WHERE ID = $cust_ID"
        );//not sure if this works
        return $mysqli->query("SELECT * FROM Customer WHERE ID=$cust_ID");
    }

    function minSupplier($mysqli) {
        $prodID = $_POST["prodID"];

        $result = $mysqli->query("  SELECT SupplierName, ProdID, MIN(S.Price) as Price
                                    FROM SuppliedBy S
                                    WHERE ProdID=$prodID");
        return $result;
    }

    function addCustomer($mysqli){
        $DOB = $_POST['DOB'];
        $first = $_POST['firstName'];
        $middle = $_POST['middleName'];
        $last = $_POST['lastName'];
        $address = $_POST['address'];
        $memberStatus = $_POST['memberStatus'] == "true" ? 1 : 0;
        $sqlDate= date('Ymd', strtotime($DOB));

        $result = $mysqli->query(
            " INSERT INTO Customer(DOB,Address,FirstName,MiddleName,LastName,MemberStatus) VALUES
            ('$sqlDate','$address','$first','$middle','$last','$memberStatus')
        ");
        if(!$result){
            return null;
        }
        return $mysqli->query("SELECT * FROM Customer WHERE FirstName='$first' AND LastName='$last' AND Address='$address'");
    }
    
    function addEmployee($mysqli) {
        $jobTitle = $_POST['jobTitle'];
        $name = $_POST['empName'];
        $salary = $_POST['salary'];

        $result = $mysqli->query("INSERT INTO Employee(JobTitle, Name, Salary) VALUES ('$jobTitle', '$name', $salary);");
        if(!$result) {
            return null;   
        }
        return $mysqli->query("SELECT * FROM Employee WHERE Name='$name'");
        
    }
    
    function removeEmployee($mysqli) {
        $deleteID = $_POST['deleteID'];
        $res =  $mysqli -> query( "SELECT * FROM Employee WHERE ID = $deleteID ");
        if(count($res->fetch_assoc()) == 0) {
            return null;
        }
        $result = $mysqli -> query( "DELETE FROM Employee WHERE ID = $deleteID ");
        if(!$result) {
            return null;
        }
        $result =  $mysqli -> query( " DELETE FROM Specialization WHERE EmpID = $deleteID");
        $result = $mysqli -> query(" SELECT * FROM Employee ");
        return $result;

    }

    function showProducts($mysqli) {
        return $mysqli->query("SELECT * FROM Product");
    }

    function showEmployees($mysqli) {
        return $mysqli->query("SELECT * FROM Employee");
    }
    function showCustomers($mysqli) {
        return $mysqli->query("SELECT * FROM Customer");
    }

    function showSuppliers($mysqli) {
        return $mysqli->query("SELECT * FROM Supplier");
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
                if(strcmp($key, "Price") == 0 or strcmp($key, "Revenue") == 0) {
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
        <?php echo $html; ?>
    </body>
</html>