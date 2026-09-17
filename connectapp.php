 <?php
 require_once __DIR__.'/config.php';
 $conn = oci_connect(DB_USER, DB_PASS, DB_HOST);
if (!$conn) {
   $m = oci_error();
   //echo $m['message'], "\nLogin unsuccesful in Database","\n";
   http_response_code(503);
   $response=[
            'status'=>"FAILURE",
            'message'=>'Database Connection Erro:'.$m['message'],
            ];
    echo(json_encode($response));
   exit;
}
else {
  // print "Login Succesful in Database!";
}
?>