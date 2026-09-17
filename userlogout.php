   
<?php
// Create connection to Oracle
header("Content-Type:application/json");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

//$response=array();
//echo $_SERVER['CONTENT_TYPE'];


//echo 'Here in CONTENT_TYPE';
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



$data=json_decode(file_get_contents("php://input"),true);
$Sender=$data['sender']??'';
if ($Sender=='') {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "SENDER NOT PROVIDED"
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

                
  
   $query="update userlogin set lastloggedout=SYSTIMESTAMP where username=:u and lastloggedout is  NULL";
   $stmt=oci_parse($conn,$query);
   //oci_bind_by_name($stmt,':llout',$formatted_date_time);
   oci_bind_by_name($stmt,':u', $Sender);
   //oci_bind_by_name($stmt,':M',$Message);
   
   if (!oci_execute($stmt)) {
    oci_rollback($conn);

    $e = oci_error($stmt);

    echo json_encode([
        "status" => "ERROR",
        "message" => $e['message']
    ]);
    exit;
}

oci_commit($conn);


$noofrowsaffected=oci_num_rows($stmt);
echo(json_encode(["sender"=> $Sender,
"rows_updated"=>$noofrowsaffected
]));

//http_response_code(400);    
//$response=$data;


oci_close($conn);
?>
