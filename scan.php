<?php
 $HOME = posix_getpwuid(posix_getuid())['dir'];

 require_once($HOME.'/rrs/git/kick-appid/shared.php.inc');
 $req = $HOME.'/rrs/git/kick-appid/req.json';
 $store = $HOME.'/rrs/git/kick-appid/store/';

 function getScriptURLs($buffer)
 {
  $url = 'https://kick.com';
  $scripts = array();
  $ct = preg_match_all('/<script\s+[^>]*src="([^"]+)"[^>]*>/i', $buffer, $scripts, PREG_SET_ORDER);
  if ($ct < 1)
   return false;
  $urls = array();
  for ($i = 0; $i < $ct; $i++)
  {
   if (count($scripts[$i]) < 2)
    continue;
   $scURL = $scripts[$i][1];
   if (strpos($scURL, '://') === false)
   {
    if (substr($scURL, 0, 1) === '/')
     $scURL = $url.$scURL;
    else
     $scURL = $url.'/'.$scURL;
   }
   $urls[] = $scURL;
  }
  if (count($urls) < 1)
   return false;
  return $urls;
 }

 function getAppID($buffer)
 {
  $ids = array();
  $ct = preg_match_all('/VITE_PUSHER_APP_KEY:\s*"([0-9a-f]+)"/i', $buffer, $ids, PREG_SET_ORDER);
  if ($ct < 1)
   return false;
  if (count($ids[0]) < 2)
   return false;
  return $ids[0][1];
 }

 function saveAppID($appID)
 {
  $git = '/usr/bin/git -C '.$GLOBALS['HOME'].'/rrs/git/kick-appid/';
  $app = $GLOBALS['HOME'].'/rrs/git/kick-appid/app.json';
  $jApp = json_decode(file_get_contents($app), true);
  if ($jApp === null)
   $jApp = array('PUSHER_APP_ID' => false);
  if ($jApp['PUSHER_APP_ID'] === $appID)
   return;
  $jApp['PUSHER_APP_ID'] = $appID;
  file_put_contents($app, json_encode($jApp, JSON_PRETTY_PRINT));
  exec($git.' add '.$app);
  exec($git.' commit -m "AppID Update on '.date('Y-m-d').'"');
  exec($git.' tag "v'.date('Y.m.d').'"');
  exec($git.' push');
  exec($git.' push --tags');
 }

 if (!file_exists($req))
  return;
 $reqText = file_get_contents($req);
 $retData = json_decode($reqText, true);
 $reqData = json_decode($reqText, true);
 if ($reqData === false)
  return;
 foreach ($reqData as $reqID => $reqInfo)
 {
  if (!file_exists($store.$reqID.'.bin'))
   continue;
  unset($retData[$reqID]);
  $reqBin = file_get_contents($store.$reqID.'.bin');
  unlink($store.$reqID.'.bin');
  $appID = getAppID($reqBin);
  if ($appID !== false)
  {
   saveAppID($appID);
   continue;
  }
  $reqScripts = getScriptURLs($reqBin);
  if ($reqScripts === false)
   continue;
  foreach ($reqScripts as $rScr)
  {
   $id = UUIDv7();
   $retData[$id] = buildReq($rScr, array('Accept' => '*/*', 'Sec-Fetch-Dest' => 'script', 'Sec-Fetch-Mode' => 'no-cors', 'Sec-Fetch-Site' => 'same-origin'));
  }
 }
 file_put_contents($req, json_encode($retData, JSON_PRETTY_PRINT));
?>