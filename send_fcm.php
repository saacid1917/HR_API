<?php
include("dbconnection.php");

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

ini_set('display_errors', 0);
error_reporting(E_ALL);

$con = dbconnection();
mysqli_set_charset($con, 'utf8mb4');

// ---- CONFIG ----
// Put your FCM server key in an ENV var or here:
$fcmKey = getenv('FCM_SERVER_KEY'); // recommended
// $fcmKey = 'AAAA...your_server_key_here...'; // quick test only

if (!$fcmKey) { echo json_encode(['success'=>false,'error'=>'no-fcm-key']); exit; }

// Accept JSON or Form
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);

$mode  = isset($in['mode'])  ? strtolower(trim($in['mode']))  : (isset($_POST['mode']) ? strtolower(trim($_POST['mode'])) : 'user'); // user|dept|all
$title = isset($in['title']) ? trim($in['title']) : (isset($_POST['title']) ? trim($_POST['title']) : 'Message');
$body  = isset($in['body'])  ? trim($in['body'])  : (isset($_POST['body'])  ? trim($_POST['body'])  : '');

$userID = isset($in['userID']) ? trim($in['userID']) : (isset($_POST['userID']) ? trim($_POST['userID']) : '');
$dept   = isset($in['department']) ? trim($in['department']) : (isset($_POST['department']) ? trim($_POST['department']) : '');

if ($body === '') { echo json_encode(['success'=>false,'error'=>'missing-body']); exit; }

$tokens = [];

// --- target selection ---
if ($mode === 'user') {
    if ($userID === '') { echo json_encode(['success'=>false,'error'=>'missing-userID']); exit; }
    $stmt = $con->prepare("SELECT token FROM tbl_fcm_tokens WHERE userID=?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $tokens[] = $row['token']; }
} elseif ($mode === 'dept') {
    if ($dept === '') { echo json_encode(['success'=>false,'error'=>'missing-dept']); exit; }
    // 1) get userIDs in the department (adjust table/column names if needed)
    //    Your app shows department as deptName inside the employee table.
    $u = $con->prepare("SELECT userID FROM employee WHERE deptName=?");
    $u->bind_param("s", $dept);
    $u->execute();
    $users = $u->get_result();
    $ids = [];
    while ($r = $users->fetch_assoc()) { $ids[] = $r['userID']; }
    if ($ids) {
        // 2) fetch tokens for those users
        // build placeholders (?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('s', count($ids));
        $sql = "SELECT token FROM tbl_fcm_tokens WHERE userID IN ($placeholders)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) { $tokens[] = $row['token']; }
    }
} else { // all
    $res = $con->query("SELECT token FROM tbl_fcm_tokens");
    while ($row = $res->fetch_assoc()) { $tokens[] = $row['token']; }
}

if (!$tokens) { echo json_encode(['success'=>false,'error'=>'no-tokens']); exit; }

// --- FCM send (legacy HTTP for simplicity) ---
$payload = [
  "registration_ids" => array_values($tokens),
  "notification" => [
    "title" => $title,
    "body"  => $body
  ],
  "data" => [
    "title" => $title,
    "body"  => $body,
    "kind"  => "admin_notice"
  ],
  "priority" => "high"
];

$ch = curl_init("https://fcm.googleapis.com/fcm/send");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: key=$fcmKey",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$out  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
  'success' => ($code === 200),
  'http'    => $code,
  'fcm'     => json_decode($out, true)
]);
