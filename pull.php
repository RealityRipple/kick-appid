<?php
 $url = 'https://kick.com';
 $git = '/usr/bin/git';
 $dest = $GLOBALS['siteRoot'].'/rrs/git/kick-appid/app.json';

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
  $ct = preg_match_all('/<script\s+.*src="([^"]+)".*>/i', $buffer, $scripts, PREG_SET_ORDER);
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

 $newScript = false;
 $newApp = false;
 $jRet = json_decode(file_get_contents($dest), true);
 $scriptURLs = getScriptURLs($url);
 $foundURL = false;
 $appID = false;
 if ($scriptURLs !== false)
 {
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
  if ($foundURL !== false)
  {
   if ($jRet['_script_url'] !== $foundURL)
   {
    $newScript = true;
    $jRet['_script_url'] = $foundURL;
   }
  }
 }
 if ($foundURL === false)
  $foundURL = $jRet['_script_url'];

 if ($appID === false)
  $appID = getAppID($foundURL);
 if ($appID !== false)
 {
  if ($jRet['PUSHER_APP_ID'] !== $appID)
  {
   $newApp = true;
   $jRet['PUSHER_APP_ID'] = $appID;
  }
 }
 if ($newScript || $newApp)
 {
  file_put_contents($dest, json_encode($jRet, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
  if ($newApp)
  {
   exec($git.' add '.$dest);
   exec($git.' commit -m "AppID Update on '.date('Y-m-d').'"');
   exec($git.' tag "v'.date('Y.m.d').'"');
   exec($git.' push');
   exec($git.' push --tags');
  }
  else
  {
   exec($git.' add '.$dest);
   exec($git.' commit -m "Script URL Update on '.date('Y-m-d').'"');
   exec($git.' push');
  }
 }
?>