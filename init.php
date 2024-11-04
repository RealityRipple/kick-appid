<?php
 require_once($GLOBALS['siteRoot'].'/rrs/git/kick-appid/shared.php.inc');
 $req = $GLOBALS['siteRoot'].'/rrs/git/kick-appid/req.json';
 $out = array();
 $kick = buildReq(
  'https://kick.com/realityripple/chatroom',
  array(
   'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
   'Sec-Fetch-Dest' => 'document',
   'Sec-Fetch-Mode' => 'navigate',
   'Sec-Fetch-Site' => 'none',
   'Sec-Fetch-User' => '?1'
  )
 );
 $id = UUIDv7();
 $out[$id] = $kick;
 file_put_contents($req, json_encode($out, JSON_PRETTY_PRINT));
?>