<?php

if (!empty($_SERVER['PHP_AUTH_USER'])) {

  //Instantiate the GET variables
  if (isset($_GET['remote']))
    $remote = filter_var(urldecode($_GET['remote']), FILTER_SANITIZE_STRING);
  if (isset($_GET['project']))
    $project = filter_var(urldecode($_GET['project']), FILTER_SANITIZE_STRING);
  if (isset($_GET['action']))
    $action = filter_var(urldecode($_GET['action']), FILTER_SANITIZE_STRING);
  if (isset($_GET['storage_pool']))
    $storage_pool = filter_var(urldecode($_GET['storage_pool']), FILTER_SANITIZE_STRING);
  if (isset($_GET['name']))
    $name = filter_var(urldecode($_GET['name']), FILTER_SANITIZE_STRING);
  if (isset($_GET['content_type']))
    $content_type = filter_var(urldecode($_GET['content_type']), FILTER_SANITIZE_STRING);
  if (isset($_GET['size']))
    $size = filter_var(urldecode($_GET['size']), FILTER_SANITIZE_STRING);
  
    //Instantiate the POST variable
  if (isset($_POST['json']))  
    $json = $_POST['json'];

  //Set the curl variables
  $cert = "/var/lxdware/data/lxd/client.crt";
  $key = "/var/lxdware/data/lxd/client.key";

  //Query DB to find remote
  $db = new SQLite3('/var/lxdware/data/sqlite/lxdware.sqlite');
  $db_results = $db->query("SELECT * FROM lxd_hosts WHERE id = $remote LIMIT 1");

  while($res = $db_results->fetchArray()){
    $url = "https://" . $res['host'] . ":" . $res['port'];

    //Run the matching action
    switch ($action) {
      case "createStorageVolumeForm":
        $url = $url . "/1.0/storage-pools/" . $storage_pool . "/volumes?project=" . $project;
        $data = escapeshellarg('{"config": {"size": "'.$size.'GB"}, "name": "'.$name.'", "type": "custom", "content_type": "'.$content_type.'"}');
        $results = shell_exec("sudo curl -k -L --connect-timeout 3 --cert $cert --key $key -X POST -d $data '$url'");
        break;
      case "createStorageVolumeJson":
        $url = $url . "/1.0/storage-pools/" . $storage_pool . "/volumes?project=" . $project;
        $data = escapeshellarg($json);
        $results = shell_exec("sudo curl -k -L --connect-timeout 3 --cert $cert --key $key -X POST -d $data '$url'");
        break;
      case "deleteStorageVolume":
        $url = $url . "/1.0/storage-pools/" . $storage_pool . "/volumes/" . $name . "?project=" . $project;
        $data = escapeshellarg('{}');
        $results = shell_exec("sudo curl -k -L --connect-timeout 3 --cert $cert --key $key -X DELETE -d $data '$url'");
      break;
      case "updateStorageVolume":
        $url = $url . "/1.0/storage-pools/" . $storage_pool . "/volumes/" . $name . "?project=" . $project;
        $data = escapeshellarg($json);
        $results = shell_exec("sudo curl -k -L --connect-timeout 3 --cert $cert --key $key -X PUT -d $data '$url'");
      break;
      case "loadStorageVolume":
        $url = $url . "/1.0/storage-pools/" . $storage_pool . "/volumes/" . $name . "?project=" . $project;
        $results = shell_exec("sudo curl -k -L --connect-timeout 3 --cert $cert --key $key -X GET '$url'");
      break;
    }
  }

  echo $results;

}
else {
  echo '{"error": "not authenticated", "error_code": "401", "metadata": {"err": "not authenticated", "status_code": "401"}}';
}
  
?>