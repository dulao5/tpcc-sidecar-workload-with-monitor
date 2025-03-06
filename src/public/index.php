<?php

$default_host = "docker.for.mac.host.internal";

$dbHost = getenv("DB_HOST") ?: $default_host;
$dbPort = getenv("DB_PORT") ?: "4000";
$dbUser = getenv("DB_USER") ?: "root";
$dbPass = getenv("DB_PASS") ?: "";
$dbName = getenv("DB_NAME") ?: "test";

try {

    // connect to mysql database (port 4000) using PDO
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;port=$dbPort", $dbUser, $dbPass, array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));

    $maxWarehouseId = 2;
    $maxDistrictId = 10;
    $maxCustomerId = 3000;
    $randomWarehouseId = rand(1, $maxWarehouseId);
    $randomDistrictId = rand(1, $maxDistrictId);
    $randomCustomerId = rand(1, $maxCustomerId);

    $stmt = $db->query("begin");

    // sleep 500ms to simulate a slow connection
    usleep(60000);

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

    $stmt = $db->query("commit");

    // close the connection
    $db = null;
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("error: ". $e->getMessage());
    echo "Error: " . $e->getMessage();
}
