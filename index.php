<?php
require __DIR__ . '/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->addScope(array(
  'https://mail.google.com/',
  'https://www.googleapis.com/auth/gmail.modify',
  'https://www.googleapis.com/auth/gmail.readonly'
));

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive_service = new Google_Service_Gmail($client);
  $files_list = $drive_service->users_messages->listUsersMessages('me');
  $messages = array();
  if ($files_list->getMessages()) {
        $messages = array_merge($messages, $files_list->getMessages());
        $pageToken = $files_list->getNextPageToken();
      }
  foreach($messages as $message){
    // var_dump($message->getId());
  }
  $getMessage = $drive_service->users_messages->get('me','14f8ec4149d06e07',['fields'=>'payload/body/data']);
  // var_dump($getMessage->getPayLoad()['body']['data']);
  $a = $getMessage->getPayLoad()['body']['data'];
  echo base64_decode($a);
  var_dump(strpos($a,'Message-ID'));
  // echo json_encode($files_list);
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/gmail/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
