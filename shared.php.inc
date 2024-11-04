<?php
 function UUIDv7()
 {
		$time = floor(microtime(true) * 1000);
		$uuid['time_low'] = $time & 0xffff;
  $time = $time >> 16;
		$uuid['time_high'] = $time & 0xffffffff;
  return sprintf('%08x-%04x-%04x-%04x-%04x%04x%04x',
   $uuid['time_high'],
   $uuid['time_low'],
   random_int(0, 0x0fff) | 0x7000,
   random_int(0, 0x3fff) | 0x8000,
   random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
  );
 }

 function buildReq($url, $headers = array(), $method = 'GET', $data = null)
 {
  return array(
   'method' => $method,
   'url' => $url,
   'headers' => $headers,
   'data' => $data
  );
 }
?>