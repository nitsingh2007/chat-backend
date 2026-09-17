<?php

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$tokenstring = $headers['authorization'] ??'';
if($tokenstring=='')
  {
    echo json_encode([
    "status" => "AUTHENTICATION FAILURE",
    "message" =>"TOKEN NOT RECEIVED"
  ]);
  exit;

  }
  $token='';
  if (preg_match('/Bearer\s+(.*)$/i', $tokenstring, $matches)) {
    $token = $matches[1];
}
if($token==null || $token=='')
  {
     echo json_encode([
    "status" => "AUTHENTICATION FAILURE",
    "message" =>"TOKEN NOT RECEIVED"
  ]);
  exit;

  }



$data = json_decode(file_get_contents("php://input"), true);

$Sender = $data['sender'] ?? null;
$Message = $data['message'] ?? null;
$Receiver = $data['receiver'] ?? '&#ALL';
$Message_Type = $data['message_type'] ?? 'TEXT';
$File_Path = $data['file_path'] ?? null;

if ($Message_Type === 'FILE' && ($Message === null || $Message === '')) {
  $Message = 'File sent';
}
if ($Sender === null || $Sender === '') {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "SENDER REQUIRED"
    ]);
    exit;
}



require_once "connectapp.php";


$query="select 1 from userlogin where token=:T and username=:U and lastloggedin is not null and lastloggedout is null and trunc(lastlogindate)=trunc(SYSDATE)";
$stmt=oci_parse($conn,$query);
oci_bind_by_name($stmt,':T',$token);
oci_bind_by_name($stmt,':U',$Sender);
if(!oci_execute($stmt))
  {
    echo json_encode([
    "status" => "ERROR",
    "message" =>"DATABASE ERROR"
  ]);
  exit;

  }
  if(!oci_fetch($stmt))
    {

     echo json_encode([
    "status" => "AUTHENTICATION FAILURE",
    "message" =>"INCORRECT TOKEN"
  ]);
  exit;

    }


  

$query = 'insert into messages(sender, message, receiver, message_type, file_path)
          values(:S,:M,:R,:MT,:FP)';

$stmt = oci_parse($conn, $query);

oci_bind_by_name($stmt, ':S', $Sender);
oci_bind_by_name($stmt, ':M', $Message);
oci_bind_by_name($stmt, ':R', $Receiver);
oci_bind_by_name($stmt, ':MT', $Message_Type);
oci_bind_by_name($stmt, ':FP', $File_Path);

if (!oci_execute($stmt)) {
  $e = oci_error($stmt);

  echo json_encode([
    "status" => "Failure",
    "message" => $e['message']
  ]);
  exit;
}

oci_commit($conn);

echo json_encode([
  "status" => "Success",
  "SENDER" => $Sender,
  "MESSAGE" => $Message,
  "RECEIVER" => $Receiver,
  "MESSAGE_TYPE" => $Message_Type,
  "FILE_PATH" => $File_Path
]);

oci_close($conn);
?>