 
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



   date_default_timezone_set("Asia/Kolkata");             
   $today_date=new DateTime();
   $formatted_date=$today_date->format('d-m-Y');
   
   $query="select distinct a.username, b.profilepic from userlogin a  
join usersofchat b
on a.username=b.username
 order by LOWER(a.username) ASC";
   $stmt=oci_parse($conn,$query);
   //oci_bind_by_name($stmt,':lld',$formatted_date);

   
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
       // echo "Row is: ".$row;
        
       $response[]=['username'=>$row['USERNAME'],
              'profilepic'=>$row['PROFILEPIC']] ;    
       //$response[]=$row;
       /*for each username please check for profile pic in the profilepic folder and if it exists then append the path of the profile pic after three special
       character which are %^& so as to enable separation of username from path of prifile pic on the client side which is flutter app */
       /*$user_name=$row['USERNAME'];
      $checkDir="profilepic/".$user_name;
if(!is_dir($checkDir))
  {
$response[]=['username'=>$row['USERNAME'],
              'profilepic'=>'' ] ;   
continue;

  }
  $files=array_diff(scandir($checkDir), array('.', '..'));
  $latestFile='';
  $latestModifiedFile=0;
  foreach($files as $file)
    {
       $fullPath = $checkDir . "/" . $file;
      if(is_file($fullPath))
        {
          $modifiedTime=filemtime($fullPath);
          if($modifiedTime>$latestModifiedFile)
            {
            $latestModifiedFile=$modifiedTime;
            $latestFile=$fullPath;
            }


        }

    }
    

    if($latestFile!='')
        {
        $profilePicPath='http://172.16.105.83:8080/practice/'.$latestFile;
        $response[]=['username'=>$row['USERNAME'],
              'profilepic'=>$profilePicPath ] ;   


        }
        else
            {
                 $response[]=['username'=>$row['USERNAME'],
              'profilepic'=>'' ] ;   




            }   
        

    }*/
    }
    
//http_response_code(400); 
//print_r($response);
if($response!=null)
    {   
        //echo sizeof($response);

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

    
