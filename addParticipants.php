 
<?php
// Create connection to Oracle
header("Content-Type:application/json");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$tokenstring = $headers['authorization'] ?? '';
if($tokenstring=='')
  {
    echo json_encode([
    "status" => "AUTHENTICATION FAILURE",
    "message" =>"TOKEN NOT RECEIVED",
    "headers"=>getallheaders()
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

$response=array();





require_once "connectapp.php";


$query="select 1 from userlogin where token=:T and lastloggedin is not null and lastloggedout is null and trunc(lastlogindate)=trunc(SYSDATE)";
$stmt=oci_parse($conn,$query);
oci_bind_by_name($stmt,':T',$token);
//oci_bind_by_name($stmt,':U',$Sender);
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

$data = json_decode(file_get_contents("php://input"), true);

$Participant = $data['participant'] ?? null;
$Sender=$data['sender'] ?? null;

if ($Participant === null || $Participant === '') {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "PARTICIPANT REQUIRED"
    ]);
    exit;
}

if ($Sender === null || $Sender === '') {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "SENDER REQUIRED"
    ]);
    exit;
}

$Participant = trim($Participant);
$Sender = trim($Sender);
    $query = "select blocked from usersofchat where username=:S";
    $stmt=oci_parse($conn,$query);
    oci_bind_by_name($stmt, ':S', $Sender);
    
   if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    $response['sender']='ORACLE ERROR';
    $response['message']=$e['message'];
    echo json_encode($response);
    exit;
}
   //oci_commit($conn);

   $Group='';
   while(($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS))!=false)
    {
       // echo "Row is: ".$row;
        $Group=$row['BLOCKED'];
       /* foreach($row as $item)
            {
        print ($item);
            }*/
        //echo "here inside while loop";

    }
 if($Group==null || $Group=='')
  {

$Group=$Participant;

  }

 else
  {
 $ArrayOfUsernames=explode('#~#', $Group);
 //$Participant = strtoupper(trim($Participant));

//$ArrayOfUsernames = array_map('strtoupper', $ArrayOfUsernames);
 if(in_array($Participant, $ArrayOfUsernames))
  {
   http_response_code(400);
  echo json_encode( ["status" => "Failure",
        "message" => "Chat Participant Already Blocked"
    ]);
   exit;
  }
 $Group=$Group.'#~#'.$Participant;

  } 

  $query = "update usersofchat set blocked=:G where username=:S";
    $stmt=oci_parse($conn,$query);
    oci_bind_by_name($stmt, ':G', $Group);
    oci_bind_by_name($stmt, ':S', $Sender);
    
   if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    $response['status']='ORACLE ERROR';
    $response['message']=$e['message'];
    echo json_encode($response);
    exit;
}
  oci_commit($conn);
  http_response_code(200);
  echo json_encode( ["status" => "Success",
        "message" => "Participant With Username ".$Participant."   Blocked"
    ]);

       


oci_free_statement($stmt);
oci_close($conn);
?>

    
