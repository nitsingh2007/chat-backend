 
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

$response=[];





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


   $Sender=$_GET['sender']??'&#NULL';
   $Receiver=$_GET['receiver']??'';
   $LastMessageTime=$_GET['lastmessagetime']??'';

   
   if($Sender=='&#NULL')
    {
     $query="select  sender,count(message) as numofmessages from messages where receiver=:r group by sender" ;
     $stmt=oci_parse($conn,$query);
     oci_bind_by_name($stmt,':r',$Receiver);
    }
    else
        {

   if ($LastMessageTime == '') {

    $query = "select sender, receiver,message,message_id,message_type, 
   trunc(timeofmessage) as message_date, to_char(timeofmessage, 'DD-MM-YYYY HH24:MI:SS.FF6') as message_datetime, file_path,deletedbysender, deletedbyreceiver 
              from messages
              where ((sender=:s and receiver=:r)
                  or (sender=:r and receiver=:s))
              order by timeofmessage";

} else {

    $query = "select sender, receiver,message,message_id,message_type, 
   trunc(timeofmessage) as message_date, to_char(timeofmessage, 'DD-MM-YYYY HH24:MI:SS.FF6') as message_datetime, file_path,deletedbysender, deletedbyreceiver 
              from messages
              where ((sender=:s and receiver=:r)
                  or (sender=:r and receiver=:s))
                and timeofmessage >
                    TO_TIMESTAMP(:t,
                                 'DD-MM-YYYY HH24:MI:SS.FF6')
              order by timeofmessage";
} 
  /* $query="select  sender, receiver,message,message_id,message_type, 
   trunc(timeofmessage) as message_date, to_char(timeofmessage, 'DD-MM-YYYY HH24:MI:SS.FF6') as message_time, file_path,deletedbysender, deletedbyreceiver from messages 
   where ((sender=:s and receiver=:r) or (sender=:r and receiver=:s)) and timeofmessage > TO_TIMESTAMP(:t, 'DD-MM-YYYY HH24:MI:SS.FF6') order by timeofmessage"; */
   
   $stmt=oci_parse($conn,$query);
   oci_bind_by_name($stmt,':s',$Sender);
   oci_bind_by_name($stmt,':r',$Receiver);
   if ($LastMessageTime != '') {
    oci_bind_by_name($stmt, ':t', $LastMessageTime);
}
 
           }
   
   if (!oci_execute($stmt))
    {
    $e = oci_error($stmt);
    $response['sender']='ORACLE ERROR';
    $response['message']=$e['message'];
    echo json_encode($response);
    exit;
}
   
   


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

    
