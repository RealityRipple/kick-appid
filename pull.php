<?php
 $HOME = posix_getpwuid(posix_getuid())['dir'];

 function getAppID()
 {
  $url = 'https://web.kick.com/api/v1/realtime/connection';
  $hHdrs = array();
  $hHdrs[] = 'Accept: application/json';
  $hHdrs[] = 'Content-Type: application/json';
  $hHdrs[] = 'Origin: https://kick.com';
  $hHdrs[] = 'Referer: https://kick.com/';
  $hHdrs[] = 'X-App-Platform: web';
  $uuid = UUIDv4();
  $body = array(
   'client' => array(
    'id' => $uuid,
    'type' => 'web'
   ),
   'capabilities' => array(
    'accepted_providers' => array(
     array('provider' => 'pusher')
    )
   )
  );
  $vars = json_encode($body);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
  curl_setopt($ch, CURLOPT_TIMEOUT, 45);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $hHdrs);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
  $result = curl_exec($ch);
  if ($result === false)
   return false;
  $j = json_decode($result, true);
  if ($j === null)
   return false;
  if (!array_key_exists('message', $j) || $j['message'] !== 'success')
   return false;
  if (!array_key_exists('data', $j))
   return false;
  if (!array_key_exists('mode', $j['data']) || $j['data']['mode'] !== 'websocket')
   return false;
  if (!array_key_exists('connections', $j['data']) || !is_array($j['data']['connections']))
   return false;
  foreach ($j['data']['connections'] as $conn)
  {
   if (!array_key_exists('credentials', $conn))
    continue;
   if (!array_key_exists('app_key', $conn['credentials']))
    continue;
   if (!array_key_exists('cluster', $conn['credentials']))
    continue;
   return $conn['credentials'];
  }
  return false;
 }

 function UUIDv4()
 {
  return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
   random_int(0, 0xffff), random_int(0, 0xffff),
   random_int(0, 0xffff),
   random_int(0, 0x0fff) | 0x4000,
   random_int(0, 0x3fff) | 0x8000,
   random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
  );
 }

 function saveAppID($appID, $cluster)
 {
  $git = '/usr/bin/git -C '.$GLOBALS['HOME'].'/rrs/git/kick-appid/';
  $app = $GLOBALS['HOME'].'/rrs/git/kick-appid/app.json';
  $jApp = json_decode(file_get_contents($app), true);
  if ($jApp === null)
   $jApp = array('PUSHER_APP_ID' => false, 'PUSHER_CLUSTER' => false);
  if (array_key_exists('PUSHER_APP_ID', $jApp) && array_key_exists('PUSHER_CLUSTER', $jApp))
  {
   if ($jApp['PUSHER_APP_ID'] === $appID && $jApp['PUSHER_CLUSTER'] === $cluster)
    return;
  }
  $jApp['PUSHER_APP_ID'] = $appID;
  $jApp['PUSHER_CLUSTER'] = $cluster;
  file_put_contents($app, json_encode($jApp, JSON_PRETTY_PRINT));
  exec($git.' add '.$app);
  exec($git.' commit -m "AppID Update on '.date('Y-m-d').'"');
  exec($git.' tag "v'.date('Y.m.d').'"');
  exec($git.' push');
  exec($git.' push --tags');
 }

 $appData = getAppID();
 if ($appData === false)
  return;
 saveAppID($appData['app_key'], $appData['cluster']);
?>