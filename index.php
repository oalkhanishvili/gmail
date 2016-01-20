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
    $getMessage = $gmail_service->users_messages->get('me',$message->getId(),['fields'=>'payload/body/data']);
    // var_dump($getMessage->getPayLoad()['body']['data']);
    $a = base64_decode($getMessage->getPayLoad()['body']['data']);
    // echo $a;
    $b  = strpos($a,'Message-ID:');
    $c = $b+11;
    // var_dump(strpos($a,'Message-ID:'));
    $d =  trim(substr($a,$c,( strpos($a, PHP_EOL, $c) -$c)));
    var_dump($d);
    continue;
    $getMessage2 = $gmail_service->users_messages->listUsersMessages('me',['q'=>'rfc822msgid:'.$d,'fields'=>'messages/id']);
    $e = ($getMessage2->getMessages()[0]['id']);
    $getMessage3 = $gmail_service->users_messages->get('me',$e,['fields'=>'payload/headers']);
    // var_dump($getMessage3['payload']['headers']);
    foreach ( $getMessage3['payload']['headers'] as $message ){
      if ($message['name'] == 'To'){
        var_dump($message['value']);
        break;
      }
    }
  }

  // echo json_encode($files_list);
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/gmail/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
