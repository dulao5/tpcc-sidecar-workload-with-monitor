<?php

$default_host = "docker.for.mac.host.internal";

$dbHost = getenv("DB_HOST") ?: $default_host;
$dbPort = getenv("DB_PORT") ?: "4000";
$dbUser = getenv("DB_USER") ?: "root";
$dbPass = getenv("DB_PASS") ?: "";
$dbName = getenv("DB_NAME") ?: "test";

$beginTime = microtime(true);
$doneTasks = ["start_process ".date("Y-m-d H:i:s")];

function traceTasks($taskname) {
	global $beginTime;
	global $doneTasks;
	$doneTasks[] = $taskname." (time: ".(microtime(true)-$beginTime)."s)";
}
function getDebugInfo($e = null) {
	global $doneTasks;
	$res = "doneTasks:\t". join("\t", $doneTasks);
	if (!is_null($e)) {
		$res .= "\t error:\t".str_replace("\n", "\t", $e->getTraceAsString());
	}
	return $res;
}

try {

    // connect to mysql database (port 4000) using PDO
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;port=$dbPort", $dbUser, $dbPass, array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
    traceTasks("do_connect");

    $maxWarehouseId = 2;
    $maxDistrictId = 10;
    $maxCustomerId = 3000;
    $randomWarehouseId = rand(1, $maxWarehouseId);
    $randomDistrictId = rand(1, $maxDistrictId);
    $randomCustomerId = rand(1, $maxCustomerId);

    $stmt = $db->query("begin");
    traceTasks("do_begin");

    // sleep 500ms to simulate a slow connection
    usleep(60000);
    traceTasks("do_sleep");

    // create a query to get all the data from the table
    $stmt = $db->query("SELECT * FROM customer where c_w_id = $maxWarehouseId and  c_d_id = $randomDistrictId and c_id = $randomCustomerId");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    traceTasks("do_query");

    print_r(array(
        "randomIds" => array(
            "warehouse" => $randomWarehouseId,
            "district" => $randomDistrictId,
            "customer" => $randomCustomerId
        ),
	"data" => $datas,
	"debug" => getDebugInfo(),
	"time" => microtime(1) - $beginTime
    ));

    $stmt = $db->query("commit");
    traceTasks("do_commit");

    // close the connection
    $db = null;
} catch (PDOException $e) {
    traceTasks("error");
    header("HTTP/1.1 501 Internal Server Error");
    $errorMsg = "phpapp error: ". $e->getMessage(). " -- debug info : ". (getDebugInfo($e));
    error_log($errorMsg);
    echo $errorMsg;
}
