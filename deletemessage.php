<?php
error_reporting(0);
ini_set('display_errors', 0);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit(0);
}
header("Content-Type:application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$tokenstring = $headers['authorization'] ?? '';
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

if (!$data) {

    http_response_code(400);

    echo json_encode([
        "status" => "Failure",
        "message" => "Invalid JSON"
    ]);

    exit;
}

if (!isset($data['message_id']) || !isset($data['delete_initiated_by'])) {
    http_response_code(400);

    echo json_encode([
        "status" => "Failure",
        "message" => "Missing fields"
    ]);

    exit;
}
$Message_Id=$data['message_id'];
$Delete_Initiated_By=$data['delete_initiated_by'];
//$Message_Type = $data['message_type'];
//$File_Path = $data['file_path'] ?? null;





require_once "connectapp.php";


$query="select 1 from userlogin where token=:T and username=:U and lastloggedin is not null and lastloggedout is null and trunc(lastlogindate)=trunc(SYSDATE)";
$stmt=oci_parse($conn,$query);
oci_bind_by_name($stmt,':T',$token);
oci_bind_by_name($stmt,':U',$Delete_Initiated_By);
if(!oci_execute($stmt))
  {
    $e=oci_error($stmt);
    echo json_encode([
    "status" => "ERROR",
    "message" =>$e['message']
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



//if($Message_Type==='FILE')
  //{
$query="select sender, receiver from messages where message_id=:M";
$stmt = oci_parse($conn, $query);

oci_bind_by_name($stmt, ':M', $Message_Id);

if (!oci_execute($stmt)) {
  $e = oci_error($stmt);
  http_response_code(400);
  echo json_encode([
    "status" => "Failure",
    "message" => $e['message']
  ]);
  exit;
}

//oci_commit($conn);



$Sender='';
$Receiver='';


//there is only going to be one row as message_id is uuid of guid type and unique
while(($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS))==True)
    {
       $Sender=$row['SENDER'];
       $Receiver=$row['RECEIVER'];
       

       }

       if($Delete_Initiated_By != $Sender && $Delete_Initiated_By != $Receiver)
{
    http_response_code(403);

    echo json_encode([
        "status" => "Failure",
        "message" => "Unauthorized"
    ]);

    exit;
}


    if($Delete_Initiated_By==$Sender)
      {

       $query = "update messages set deletedbysender='yes' where message_id=:M";
      }

      else
        {
        $query="update messages set deletedbyreceiver='yes' where message_id=:M";

        }

$stmt = oci_parse($conn, $query);

oci_bind_by_name($stmt, ':M', $Message_Id);

if (!oci_execute($stmt)) {
  $e = oci_error($stmt);
  http_response_code(400);
  echo json_encode([
    "status" => "Failure",
    "message" => $e['message']
  ]);
  exit;
}

oci_commit($conn);
/*$var=false;
if($Message_Type==='FILE')
  {
  if(is_file($File_Path))
        {
          
            $var= unlink($File_Path);

            
        }



  }*/
http_response_code(200);
echo json_encode([
  "status" => "Success",
  "message" =>"deleted",
   //"file"=>$var
  
]);
oci_free_statement($stmt);
oci_close($conn);
?>