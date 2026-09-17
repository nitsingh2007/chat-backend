   
<?php
error_reporting(0);
ini_set('display_errors', 0);
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




 
//echo $_SERVER['CONTENT_TYPE'];
if(str_contains($_SERVER['CONTENT_TYPE'],'application/json')==true)
{
//echo 'Here in CONTENT_TYPE';
$data=json_decode(file_get_contents("php://input"),true);
$sender=$data['sender'];
if($sender==null)
  {
http_response_code(400);
   echo(json_encode(["status"=>"Failure",
        "message"=>"Profile Pic Doesn't Exist"
        ]));
        exit;

  }
$checkDir=__DIR__."/profilepic/".$sender;
if(!is_dir($checkDir))
  {
   //mkdir($uploadDir, 0777, true);
   http_response_code(400);
   echo(json_encode(["status"=>"Failure",
        "message"=>"Profile Pic Doesn't Exist"
        ]));
exit;
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
      $lastIndexOf=strrpos($latestFile, '/');
      $latestFileName=substr($latestFile, $lastIndexOf+1);  
      http_response_code(200);
      echo(json_encode(["status"=>"Success",
      "message"=>"Profile Pic Found",
      "path"=>'https://fdkolchat.duckdns.org/app/profilepic/'.$sender.'/'.$latestFileName
      
      ]));



      }




    else
      {
        http_response_code(400);
        echo(json_encode(["status"=>"Failure",
        "message"=>"Profile Pic Doesn't Exist"
        ]));


      }


 
    }






?>
