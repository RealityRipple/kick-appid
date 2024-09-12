<?php
 $url = 'https://kick.com';
 $git = '/usr/bin/git -C '.$GLOBALS['siteRoot'].'/rrs/git/kick-appid/';
 $dest = $GLOBALS['siteRoot'].'/rrs/git/kick-appid/app.json';
 $jscr = $GLOBALS['siteRoot'].'/rrs/git/kick-appid/script.json';

 function getScriptURLs($url)
 {
  /*
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
  curl_setopt($ch, CURLOPT_TIMEOUT, 45);
  $buffer = curl_exec($ch);
  if ($buffer === false)
   return false;
  curl_close($ch);
  */
  $buffer = file_get_contents($GLOBALS['siteRoot'].'/rrs/git/kick-appid/kick.html');
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

 function getAppID($url)
 {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
  curl_setopt($ch, CURLOPT_TIMEOUT, 45);
  $buffer = curl_exec($ch);
  if ($buffer === false)
   return false;
  curl_close($ch);
  $ids = array();
  $ct = preg_match_all('/VITE_PUSHER_APP_KEY:\s*"([0-9a-f]+)"/i', $buffer, $ids, PREG_SET_ORDER);
  if ($ct < 1)
   return false;
  if (count($ids[0]) < 2)
   return false;
  return $ids[0][1];
 }

 function updateScript($url, $destScript)
 {
  $scriptURLs = getScriptURLs($url);
  if ($scriptURLs === false)
   return array(false, false);
  $jScript = json_decode(file_get_contents($destScript), true);
  if ($jScript === null)
   $jScript = array('url' => false);
  $foundURL = false;
  $appID = false;
  for ($i = 0; $i < count($scriptURLs); $i++)
  {
   $tmpID = getAppID($scriptURLs[$i]);
   if ($tmpID !== false)
   {
    $appID = $tmpID;
    $foundURL = $scriptURLs[$i];
    break;
   }
  }
  if ($foundURL === false)
   $foundURL = $jScript['url'];
  else if ($jScript['url'] !== $foundURL)
  {
   $jScript['url'] = $foundURL;
   file_put_contents($destScript, json_encode($jScript, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
  }
  return array($appID, $foundURL);
 }

 $appID = false;
 $foundURL = false;
 list($appID, $foundURL) = updateScript($url, $jscr);
 if ($foundURL === false)
  return;
 if ($appID === false)
  $appID = getAppID($foundURL);
 if ($appID === false)
  return;
 $jApp = json_decode(file_get_contents($dest), true);
 if ($jApp === null)
  $jApp = array('PUSHER_APP_ID' => false);
 if ($jApp['PUSHER_APP_ID'] === $appID)
  return;
 $jApp['PUSHER_APP_ID'] = $appID;
 file_put_contents($dest, json_encode($jApp, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
 exec($git.' add '.$dest);
 exec($git.' commit -m "AppID Update on '.date('Y-m-d').'"');
 exec($git.' tag "v'.date('Y.m.d').'"');
 exec($git.' push');
 exec($git.' push --tags');
?>