<?php
$socket = stream_socket_server("tcp://0.0.0.0:6722", $errno, $errstr);
if (!$socket) {
  echo "$errstr ($errno)<br />\n";
  } else {

while (true) {
 while ($conn = @stream_socket_accept($socket,$nbSecondsIdle))
     {
      $message= fread($conn, 1024);
       echo 'I have received that : '.$message."\n";
        fputs ($conn, "01000001");
    //     fclose ($conn);
     }

}
fclose($socket);
}
                ?>