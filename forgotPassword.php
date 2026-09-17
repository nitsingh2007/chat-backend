   
<?php
// Create connection to Oracle
ini_set('display_errors', 0);
error_reporting(E_ALL);
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



$response=array();
 $otpmatch=false;
//echo $_SERVER['CONTENT_TYPE'];
if(str_contains($_SERVER['CONTENT_TYPE'],'application/json')==true)
{
//echo 'Here in CONTENT_TYPE';
$data=json_decode(file_get_contents("php://input"),true);
//$Username=$data['name'];
$Password=$data['password'];
$Email=$data['email'];
$Otp=$data['otp'];
}

require_once "connectapp.php";

if($Password==null || $Email==null || $Otp==null)
{
http_response_code(400);    
$response=[
            'status'=>'Failure',
            'message'=>'Any of the  Username, Password or Email has not been provided',
            ];
    
  echo(json_encode($response));  
   exit;
}



$query1="select otp from tempotptable where email=:E and username='$#dummy'
 and timeofotp>current_timestamp- interval '5' minute order by timeofotp desc";
 
$stmt=oci_parse($conn, $query1);
oci_bind_by_name($stmt, ":E", $Email);
//oci_bind_by_name($stmt, ":U", $Username);

$result=oci_execute($stmt);
if(!$result)
    {
     http_response_code(400);
      $error=oci_error($stmt);
      $response=[
            'status'=>'Failure',
            'message'=>$error['message']
            ];
    
  echo(json_encode($response));  
   exit;
    }   
    $rows=[];
    oci_fetch_all($stmt,$rows);
    
    if(empty($rows['OTP']))
        {
        http_response_code(400);
        //$error=oci_error($stmt);
        $response=[
            'status'=>'Failure',
            'message'=>'OTP Expired. OTP has to be used within 5 minutes'
            ];
        echo(json_encode($response));  
    
        exit;


        } 
    
    if (!empty($rows['OTP']) && $Otp == $rows['OTP'][0]) {
    $otpmatch = true;
}
    
 
if($otpmatch==false)
    {

    http_response_code(400);
      //$error=oci_error($stmt);
      $response=[
            'status'=>'Failure',
            'message'=>'OTP Mismatch.Please enter correct OTP.'
            ];
        echo(json_encode($response));  
    
        exit;
    }

try{
$query1="update usersofchat
set password= :P where email=:E";
$stmt=oci_parse($conn, $query1);
oci_bind_by_name($stmt, ":E", $Email);

$hashedPassword = password_hash($Password, PASSWORD_BCRYPT);
oci_bind_by_name($stmt, ":P", $hashedPassword);
//oci_bind_by_name($stmt, ":P", $Password);
$result=oci_execute($stmt);
if(!$result)
    {
     $error=oci_error($stmt);
     
        http_response_code(400);
        $response=[
            'status'=>'Failure',
            'message'=>$error['message']
            ];
    
  echo(json_encode($response));  
    

   exit;
        }
    

}

    catch(Exception $e)
    {
   
    echo"Inside Exception catch";
    http_response_code(400);
        $response=[
            'status'=>'Failure',
            'message'=>$e->getMessage()
            ];
    
  echo(json_encode($response));  
    exit;

    }

    
    
        
            /*if(!oci_commit($conn))
                {
            $error=oci_error($conn);    
            http_response_code(400);
        $response=[
            'status'=>'Failure',
            'message'=>$error['message'],
            ];
    
  echo(json_encode($response));  
    exit;

                } */

            http_response_code(200);
        $response=[
            'status'=>'Success',
            'message'=>'The Password has been succesfully changed.',
            ];
            $deleteQuery = "delete from tempotptable where email=:E and username='$#dummy'";

            $deleteStmt = oci_parse($conn, $deleteQuery);

            //oci_bind_by_name($deleteStmt, ":U", $Username);
            oci_bind_by_name($deleteStmt, ":E", $Email);

            oci_execute($deleteStmt);
            oci_commit($conn);
            echo(json_encode($response));  

        
    
oci_free_statement($stmt);
oci_free_statement($deleteStmt);
oci_close($conn);
?>
