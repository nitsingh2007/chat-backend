   
<?php
// Create connection to Oracle
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use PHPMailer\PHPMailer\SMTP;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";
require_once __DIR__.'/config.php';
//require 'vendor/autoload.php';

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
//$data=json_decode(file_get_contents("php://input"),true);
//$Email=$data['email']??'nit_singh2007@yahoo.co.in';
//echo $_SERVER['CONTENT_TYPE'];
if(isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
{
//echo 'Here in CONTENT_TYPE';
$data=json_decode(file_get_contents("php://input"),true);
$Username=$data['name']??'$#dummy';
//$Password=$data['password'];
$Email=$data['email'];
}



require_once "connectapp.php";

if($Email==null)
{
    $response=[
            'status'=>'Failure',
            'message'=>'The Email Address for sending OTP has not been provided',
            ];
    
  echo(json_encode($response));  
   exit;
}



$otp=random_int(1111,9999);
$mail = new PHPMailer(true);



$query1="insert into tempotptable(email, username,otp) values(:E,:U,:O)";
$stmt=oci_parse($conn, $query1);
oci_bind_by_name($stmt, ":E", $Email);
oci_bind_by_name($stmt, ":U", $Username);
oci_bind_by_name($stmt, ":O", $otp);
//oci_bind_by_name($stmt, ":P", $Password);
$result=oci_execute($stmt);
if(!$result)
    {
     $error=oci_error($stmt);
     if($error['code']==1)   

        http_response_code(400);
        $response=[
            'status'=>'Failure',
            'message'=>'There was a server Error',
            ];
    
  echo(json_encode($response));  
   exit;

    }
    else
        {
            oci_commit(($conn));
            try {
    // Configuration SMTP
    $mail->SMTPDebug = 0;                         // Show output (Disable in production)
    $mail->isSMTP();                                               // Activate SMTP sending
    $mail->Host  = 'smtp.gmail.com';                     // SMTP Server
    $mail->SMTPAuth  = true;                                       // SMTP Identification
    $mail->Username  = 'fdkolchat@gmail.com';                  // SMTP User
   
   //$mail->Password=getenv("MAIL_PASSWORD");
    $mail->Password=EMAIL_KEY; 
   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port  = 587;
   // $From=$_REQUEST['Sender'];
    $Recipient=$Email;
    $Subject='OTP VERIFICATION FROM FDKOLCHAT';
    $Content='Dear user,<br>
    <p>Your OTP for change of password is '.(string)$otp.'<br>
    
    With Regards<br>
    Team FDKOLCHAT'
    ;
    $mail->setFrom($mail->Username,'FDKOLCHAT');                // Mail sender

    // Recipients
    $mail->addAddress($Email, ' ');  // Email and recipient's name

    // Mail content
    $mail->isHTML(true);
    $mail->Subject = $Subject;
    $mail->Body  = $Content;
    $mail->AltBody = 'Dear user,
    Your OTP for change of password on FDKOLCHAT is '.(string)$otp.'
    
    With Regards
    Team FDKOLCHAT';
    $mail->send();
    //echo 'The message has been sent';
    //header("Location:Welcome.php? Message=SMTP Settings Saved for Username=".$Username."&Username=".$Username);
    //header("Location:Welcome.php? Message=Your Mail has been Sent"."&Username=".$Username);
    
} catch (Exception $e) {
    //echo "Message has not been sent. Mailer Error: {$mail->ErrorInfo}";
    http_response_code(500);
        $response=[
            //'http_response_code'=>http_response_code(),
            'status'=>'Failure',
            'message'=>$mail->ErrorInfo
            ];

         echo(json_encode($response));  
         exit;

}

            http_response_code(200);
        $response=[
            'status'=>'Success',
            'message'=>'OTP sent succesfully to the user\'s  mail address',
            ];

         echo(json_encode($response));  

        }






oci_close($conn);
?>
