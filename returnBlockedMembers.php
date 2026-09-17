 
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

   $Username = $data['username'] ?? null;

   $query="select blocked from usersofchat where username=:U";
   
   $stmt=oci_parse($conn,$query);
   oci_bind_by_name($stmt,':U',$Username);

   
   $result1=oci_execute($stmt);
   //oci_commit($conn);
   if (!$result1) {
    $e = oci_error($stmt);
    $response['username']='ORACLE ERROR';
   // $response['message']=$e['message'];
    echo json_encode($response);
    exit;
}
   


   while(($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS))==True)
    {
       
       $response[]=$row;   
       
       }
    

if($response!=null)
    {   
        
echo json_encode($response);

    }

    if($response==null)
        {
             //$response['name']='NULL1';
             //$response['message']='NULL1';
             $response=[];
             echo json_encode($response);

        }


oci_free_statement($stmt);
oci_close($conn);
?>

    
