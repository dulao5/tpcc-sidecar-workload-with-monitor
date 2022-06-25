<?php

$default_host = "docker.for.mac.host.internal";

$dbHost = getenv("DB_HOST") ?: $default_host;
$dbPort = getenv("DB_PORT") ?: "4000";
$dbUser = getenv("DB_USER") ?: "root";
$dbPass = getenv("DB_PASS") ?: "";
$dbName = getenv("DB_NAME") ?: "test";

try {

    // connect to mysql database (port 4000) using PDO
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;port=$dbPort", $dbUser, $dbPass);

    // set up error handling
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // get max id from the table warehouse
    $stmt = $db->prepare("SELECT MAX(w_id) FROM warehouse");
    $stmt->execute();
    $maxWarehouseId = $stmt->fetchColumn();
    $randomWarehouseId = rand(1, $maxWarehouseId);

    // get max id from the table district
    $stmt = $db->prepare("SELECT MAX(d_id) FROM district");
    $stmt->execute();
    $maxDistrictId = $stmt->fetchColumn();
    $randomDistrictId = rand(1, $maxDistrictId);

    // get max id from the table customer
    $stmt = $db->prepare("SELECT MAX(c_id) FROM customer");
    $stmt->execute();
    $maxCustomerId = $stmt->fetchColumn();
    $randomCustomerId = rand(1, $maxCustomerId);

    // sleep 500ms to simulate a slow connection
    usleep(500000);

    // create a query to get all the data from the table
    $stmt = $db->query("SELECT * FROM customer where c_w_id = $maxWarehouseId and  c_d_id = $randomDistrictId and c_id = $randomCustomerId");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r(array(
        "randomIds" => array(
            "warehouse" => $randomWarehouseId,
            "district" => $randomDistrictId,
            "customer" => $randomCustomerId
        ),
        "data" => $datas
    ));

    // close the connection
    $db = null;
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: " . $e->getMessage();
}