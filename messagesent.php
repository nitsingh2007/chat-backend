 
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

$LastMessageTime=$_GET['lastmessagetime']??'';

if ($LastMessageTime == '') {

    $query = "select sender, message, trunc(timeofmessage) as message_date, to_char(timeofmessage, 'DD-MM-YYYY HH24:MI:SS.FF6') as message_datetime from messages 
   where receiver='&#ALL' and timeofmessage >= (SYSTIMESTAMP - INTERVAL '1' DAY) order by timeofmessage";

} else {
                
   $query="select sender, message, trunc(timeofmessage) as message_date, to_char(timeofmessage, 'DD-MM-YYYY HH24:MI:SS.FF6') as message_datetime from messages 
   where receiver='&#ALL' and timeofmessage >TO_TIMESTAMP(:t,'DD-MM-YYYY HH24:MI:SS.FF6') and timeofmessage >= (SYSTIMESTAMP - INTERVAL '1' DAY) order by timeofmessage";

}


   $stmt=oci_parse($conn,$query);

    if ($LastMessageTime != '') {
    oci_bind_by_name($stmt, ':t', $LastMessageTime);
}
   //oci_execute($stmt);
   //oci_commit($conn);
   if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    $response['sender']='ORACLE ERROR';
    $response['message']=$e['message'];
    echo json_encode($response);
    exit;
}
   //oci_commit($conn);


   while(($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS))==True)
    {
       // echo "Row is: ".$row;
        $response[]=$row;
       /* foreach($row as $item)
            {
        print ($item);
            }*/
        //echo "here inside while loop";

    }
//http_response_code(400); 
//print_r($response);

             echo json_encode($response);

       


oci_free_statement($stmt);
oci_close($conn);
?>

    
