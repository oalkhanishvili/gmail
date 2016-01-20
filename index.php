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
  $gmail_service = new Google_Service_Gmail($client);
  $files_list = $gmail_service->users_messages->listUsersMessages('me',['q'=>'from:mailer-daemon@googlemail.com']);
  $messages = array();
  if ($files_list->getMessages()) {
        $messages = array_merge($messages, $files_list->getMessages());
        $pageToken = $files_list->getNextPageToken();
      }
  foreach($messages as $message){
    var_dump($message->getId());
    $getMessage = $gmail_service->users_messages->get('me',$message->getId(),['fields'=>'payload/headers']);
    foreach ( $getMessage['payload']['headers'] as $k => $val ){
      if ( $val['name'] == 'X-Failed-Recipients' ){
        var_dump($val['value']);
      }
    }
  }

  // echo json_encode($files_list);
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/gmail/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
