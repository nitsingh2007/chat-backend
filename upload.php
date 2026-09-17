   
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*$finfo = finfo_open(FILEINFO_MIME_TYPE);

$mime = finfo_file($finfo, 'Flora Case Brief.docx');

echo $mime;
print(var_dump(get_loaded_extensions()));*/

header('Content-Type: application/json');

if(!isset($_POST['token']))
  {
    echo json_encode([
        "status" => "AUTHENTICATION FAILURE",
        "message" =>"TOKEN NOT RECEIVED"
    ]);
    exit;
  }

$token=$_POST['token'];



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
if (isset($_FILES['file']))
{
$file=$_FILES['file']??'Flora Case Brief.docx';
$file_name=$file['name'];
$file_size=$file['size'];
$tmp_file_name=$file['tmp_name'];
$file_error=$file['error'];
$allowed = ['jpg','jpeg','png','pdf','mp4','txt', 'doc','xlsx', 'pptx', 'docx', 'xls'];

$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if(!in_array($ext,$allowed))
{
    echo json_encode([
        "status"=>"Failure",
        "message"=>"Invalid file type"
    ]);
    exit;
}
if($file_size>500*1024)
  {
     echo json_encode([
        "status"=>"Failure",
        "message"=>"File More than 500KB not allowed"
    ]);
    exit;


  }

if($file_error != UPLOAD_ERR_OK)
{
    echo(json_encode(["status"=>"Failure",
        "message"=>"File Didn't Upload"
        ]));
        exit;
// return error
}

  $allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',      // docx
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',            // xlsx
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',    // pptx
    'application/msword',           // doc
    'application/vnd.ms-excel',     // xls
    'application/octet-stream',
    'video/mp4',
    'text/plain'
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $tmp_file_name);
if(!in_array($mime, $allowedMimeTypes))
{
    echo json_encode([
        "status"=>"Failure",
        "message"=>"Invalid MIME type"
    ]);
    exit;
}


$uploadDir=__DIR__."/uploads/";
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
  //find out the size of the uploads dir. if more than 250 MB then delete oldest to newest so that it falls below 50 MB
  $files=scandir($uploadDir);
  $DirectorySize=0;
  $FileListLastModified=[];
  //$lastmodified=0;

  foreach($files as $file)
    {
   $pathofile=$uploadDir.$file;
     
  if ($file==='.' || $file==='..' || (!is_file($pathofile)))
        {
          continue;
        }
        $sizeoffile=filesize($pathofile); 
       
        $FileListLastModified[]=["path" => $pathofile, "filemodifiedtime"=>filemtime($pathofile), "sizeoffile"=> $sizeoffile];
        
        $DirectorySize=$DirectorySize+$sizeoffile;
    }
if(($DirectorySize+$file_size)>250*1024*1024)
  {
  usort($FileListLastModified, function ($a, $b) {return ($a['filemodifiedtime'])<=>($b['filemodifiedtime']); });
  foreach($FileListLastModified as $item)
    {
      if(($DirectorySize-$item['sizeoffile'])>50*1024*1024)
        {
       
      if( unlink($item['path']))
        {
      $DirectorySize=$DirectorySize-$item['sizeoffile'];    
        }
         

        }
        else
          {

            break;
          }


    }

  }


$targetFilePath=$uploadDir.time()."_".basename($file_name);

  if(move_uploaded_file($tmp_file_name, $targetFilePath))
    {
      echo(json_encode(["status"=>"Success",
      "message"=>"File Uploaded Succesfully",
      "path"=>"https://fdkolchat.duckdns.org/app/uploads/".basename($targetFilePath)
      ]));

 

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

oci_free_statement($stmt);
oci_close($conn);


?>
