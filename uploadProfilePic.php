   
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
$token='';
if(isset($_POST['token']))
  {
    $token=$_POST['token'];
  }
else
  {
echo json_encode([
    "status" => "AUTHENTICATION FAILURE",
    "message" =>"TOKEN NOT RECEIVED"
  ]);
  exit;



  }

  

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



if($_SERVER['REQUEST_METHOD']=='POST')
{


if(isset($_POST['sender']))
  {
    $sender=$_POST['sender'];
if (isset($_FILES['file']))
{
$uploadedFile=$_FILES['file'];
$file_name = time() . "_" . basename($uploadedFile['name']);
//$file_name=$file['name'];
$file_size=$uploadedFile['size'];
$tmp_file_name=$uploadedFile['tmp_name'];
$file_error=$uploadedFile['error'];
$uploadDir=__DIR__."/profilepic/".$sender."/";
error_log("SENDER = [" . $sender . "]");
error_log("__DIR__ = [" . __DIR__ . "]");
error_log("UPLOAD DIR = [" . $uploadDir . "]");
error_log("PARENT = [" . dirname($uploadDir) . "]");
error_log("PARENT EXISTS = " . (is_dir(dirname($uploadDir)) ? "YES" : "NO"));
error_log("PARENT WRITABLE = " . (is_writable(dirname($uploadDir)) ? "YES" : "NO"));
error_log("UPLOAD DIR EXISTS = " . (is_dir($uploadDir) ? "YES" : "NO"));

if(!is_dir($uploadDir))
  {
   //mkdir($uploadDir, 0755, true);
    if (!mkdir($uploadDir, 0755, true))
    {
        echo json_encode([
            "status" => "Failure",
            "message" => "Could not create upload directory",
            "directory" => $uploadDir,
            "error" => error_get_last()
        ]);
        exit;
    }

  }
$targetFilePath=$uploadDir.$file_name;
if ($file_error==0)
  {
  if(move_uploaded_file($tmp_file_name, $targetFilePath))
    {
        
      // if file moved succesfully to server then the file path has to be stored in the user registration table on the server
      
$urlofprofilepic = "https://fdkolchat.duckdns.org/app/profilepic/"
    . $sender . "/"
    . $file_name;
$query="update usersofchat set profilepic=:P where username=:U";
$stmt=oci_parse($conn,$query);
oci_bind_by_name($stmt, ':P', $urlofprofilepic);
oci_bind_by_name($stmt, ':U', $sender);
   


   try{
   $result1=oci_execute($stmt);
   //oci_commit($conn);
   if (!$result1) {
    $e = oci_error($stmt);
    $response['status']='Failure';
    $response['message']=$e['message'];
     unlink($targetFilePath);
    echo json_encode($response);
    exit;
}
   }
   catch(Exception $e)
   {
   // echo" Error Occurred While Retrieving Message:". $e->getMessage();
    //oci_rollback($conn);
    $response['status']='Failure';
    $response['message']=$e->getMessage();
    //$response['message']=$e->getMessage();
    unlink($targetFilePath);
    echo json_encode($response);
    
    exit;
   }   
oci_commit($conn);
 $files=array_diff(scandir($uploadDir), array('.', '..'));
foreach($files as $file)
    {
       $fullPath = $uploadDir . $file;
      if(is_file($fullPath))
        {
          if($targetFilePath!=$fullPath)
            {
              unlink($fullPath);

            }
        }

    }

echo(json_encode(["status"=>"Success",
      "message"=>"File Uploaded Succesfully",
      "path"=>"profilepic/".$sender."/".$file_name
      ]));
// give logic for deleting older profile pics
 
  //$latestFile='';
  //$latestModifiedFile=0;
  
    }

    else
      {
        echo(json_encode(["status"=>"Failure",
        "message"=>"File Didn't Upload"
        ]));


      }


  }

  else
    {

     echo(json_encode(["status"=>"Failure",
     "message"=>"Upload Error Code--".$file_error
     ]));
    }


}
else
  {
  
echo(json_encode(["status"=>"Failure",
     "message"=>"File Field Not Set in MultiPart Request in Dart"
     ]));
  }

  }

  else
    {

    echo(json_encode(["status"=>"Failure",
     "message"=>"Sender Name Field  not sent alongwith the uploaded File"
     ]));

    }

}




?>
