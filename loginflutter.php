   
<?php
// Create connection to Oracle

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
//echo $_SERVER['CONTENT_TYPE'];

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (!str_contains($contentType, 'application/json'))
{
    echo json_encode([
        "status" => "FAILURE",
        "message" => "INVALID CONTENT TYPE"
    ]);
    exit;
}





$data=json_decode(file_get_contents("php://input"),true);
if (!is_array($data)) {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "INVALID JSON"
    ]);
    exit;
}



$Username=$data['name']??'';
$Password=$data['password']??'';
if ($Username === '' || $Password === '') {
    echo json_encode([
        "status" => "FAILURE",
        "message" => "NAME OR PASSWORD MISSING"
    ]);
    exit;
}



require_once "connectapp.php";
$query1="select PASSWORD from usersofchat where Username=:Us";
$stmt=oci_parse($conn, $query1);
oci_bind_by_name($stmt, ":Us", $Username);

if (!oci_execute($stmt)) {
    $e = oci_error($stmt);

    echo json_encode([
        "status" => "ERROR",
        "message" => $e['message']
    ]);
    exit;
}

if(($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS ))==TRUE)
{
    if(password_verify($Password,$row['PASSWORD']))
    {  
    //http_response_code(200);
    
    //$query3="select lastlogindate, lastloggedin from userlogin where username=:U and lastloggedout=null";
    $query3="update userlogin set lastloggedout=SYSTIMESTAMP where username=:U and lastloggedout is null and lastloggedin is not null";
    $stmt=oci_parse($conn, $query3);
    oci_bind_by_name($stmt, ":U", $Username);
    if (!oci_execute($stmt))
    {
    $e = oci_error($stmt);

    echo json_encode([
        "status" => "ERROR",
        "message" => $e['message']
    ]);
    exit;
    }
    
      oci_commit($conn); 

        

    $query2="insert into userlogin(lastlogindate,username,token) values(TO_DATE(:lld,'DD-MM-YYYY'),:n, :t)";
    $stmt=oci_parse($conn, $query2);
    date_default_timezone_set("Asia/Kolkata");
    $date=new DateTime();
    $formatted_date=$date->format('d-m-Y');
    $token=bin2hex(random_bytes(32));
    
    oci_bind_by_name($stmt, ":lld", $formatted_date);
    oci_bind_by_name($stmt, ":n", $Username);
    oci_bind_by_name($stmt, ":t", $token);
    //oci_bind_by_name($stmt, ":llit", $date->format('H:i:s'));
     if (!oci_execute($stmt))
    {
    $e = oci_error($stmt);

    echo json_encode([
        "status" => "ERROR",
        "message" => $e['message']
    ]);
    exit;
    }
    
    oci_commit($conn);   
     http_response_code(200);
    $response=[
            'message'=>'Login Successful',
            'token'=>$token];
    
    echo(json_encode($response));  
    //echo(json_encode($response));
    //}
    
   
    oci_free_statement($stmt);
    oci_close($conn);

    exit;
    }
    else
    {
    http_response_code(401);      
    $response=[
            'message'=>'Login UnSuccessful Wrong Password',
            ];
    echo(json_encode($response));
   oci_free_statement($stmt);
    oci_close($conn);
   
   exit;
       
    }
}
else
{
http_response_code(400);    
$response=[
            'message'=>'Login UnSuccessful Username Not Found',
            ];
echo(json_encode($response));
oci_free_statement($stmt);
oci_close($conn);
            exit;


}

?>
