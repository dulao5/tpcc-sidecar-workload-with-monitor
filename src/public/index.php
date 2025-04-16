<?php

$default_host = "docker.for.mac.host.internal";

$dbHost = getenv("DB_HOST") ?: $default_host;
$dbPort = getenv("DB_PORT") ?: "4000";
$dbUser = getenv("DB_USER") ?: "root";
$dbPass = getenv("DB_PASS") ?: "";
$dbName = getenv("DB_NAME") ?: "test";
$dbType = getenv("DB_TYPE") ?: "tidb";
$dbSock = getenv("DB_SOCK") ?: "";

$beginTime = microtime(true);
$doneTasks = ["start_process ".date("Y-m-d H:i:s")];

$tidbServerID = 'null';

function traceTasks($taskname) {
	global $beginTime;
	global $doneTasks;
	$doneTasks[] = $taskname." (time: ".(microtime(true)-$beginTime)."s)";
}
function getDebugInfo($e = null) {
	global $doneTasks;
	$res = "req_id:".$_SERVER['X_REQUEST_ID']." doneTasks:\t". join("\t", $doneTasks);
	if (!empty($e)) {
		$res .= "\t error:\t".str_replace("\n", "\t", $e->getTraceAsString());
	}
	return $res;
}

function getRemoteTiDBServerID($db) {
	$stmt = $db->query('show status');
	if (empty($stmt)) {
		return 'get_empty';
	}
	$datas = $stmt->fetchAll();
	foreach ($datas as $row) {
		if ($row['Variable_name'] == 'server_id') {
			return $row['Value'];
		}
	}
	return 'notfound_serverid_key';
}
function getRemoteMySQLServerID($db) {
	$stmt = $db->query('show variables like "hostname"');
	if (empty($stmt)) {
		return 'get_empty';
	}
	$datas = $stmt->fetchAll();
	foreach ($datas as $row) {
		if ($row['Variable_name'] == 'hostname') {
			return $row['Value'];
		}
	}
	return 'notfound_serverid_key';
}
function getRemoteServerID($db) {
	global $dbType;
	if ($dbType == "tidb") {
		return getRemoteTiDBServerID($db);
	} else {
		return getRemoteMySQLServerID($db);
	}
}

try {

    // connect to mysql database (port 4000) using PDO
    $mysqlDSN = empty($dbSock) ? 
		"mysql:host=$dbHost;dbname=$dbName;port=$dbPort"
		:
		"mysql:unix_socket=$dbSock;dbname=$dbName"
		;
    $db = new PDO($mysqlDSN, $dbUser, $dbPass, array(
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ));
    traceTasks("do_connect");

    $serverID = getRemoteServerID($db);
    traceTasks("getRemoteServerID $serverID");

    $maxWarehouseId = 2;
    $maxDistrictId = 10;
    $maxCustomerId = 3000;
    $randomWarehouseId = rand(1, $maxWarehouseId);
    $randomDistrictId = rand(1, $maxDistrictId);
    $randomCustomerId = rand(1, $maxCustomerId);

    $stmt = $db->query("begin");
    traceTasks("do_begin");

    // sleep 500ms to simulate a slow connection
    usleep(60000); // for 2k RPS
    // usleep(600); // for 2k connection count
    traceTasks("do_sleep");

    // create a query to get all the data from the table
    #$stmt = $db->query("SELECT * FROM customer where c_w_id = $maxWarehouseId and  c_d_id = $randomDistrictId and c_id = $randomCustomerId");
    $stmt = $db->query("SELECT sleep(0.05) as req_". $_SERVER['X_REQUEST_ID']);
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
    $errorMsg = "access-tidb-success: -- debug info : ". (getDebugInfo(0));
    #error_log($errorMsg);
} catch (PDOException $e) {
    traceTasks("error");
    header("HTTP/1.1 501 Internal Server Error");
    $errorMsg = "access-tidb-error: ". $e->getMessage(). " -- debug info : ". (getDebugInfo($e));
    error_log($errorMsg);
    echo $errorMsg;
}
