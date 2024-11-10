<?php
 $HOME = posix_getpwuid(posix_getuid())['dir'];

 $req = $HOME.'/rrs/git/kick-appid/req.json';
 $store = $HOME.'/rrs/git/kick-appid/store/';
 if (count(array_keys($_FILES)) === 0)
 {
  header('Content-Type: application/json');
  if (!file_exists($req))
   echo '{}';
  else
   readfile($req);
  return;
 }
 $reqData = json_decode(file_get_contents($req), true);
 $retData = array();
 foreach ($reqData as $reqID => $reqInfo)
 {
  if (file_exists($store.$reqID.'.bin'))
  {
   $retData[$reqID] = array('success' => false, 'error' => 'FILE_EXISTS');
   continue;
  }
  if (!array_key_exists($reqID, $_FILES))
  {
   $retData[$reqID] = array('success' => false, 'error' => 'FILE_UPLOAD');
   continue;
  }
  $fileErr = UPLOAD_ERR_OK;
  if (array_key_exists('error', $_FILES[$reqID]))
   $fileErr = $_FILES[$reqID]['error'];
  if ($fileErr !== UPLOAD_ERR_OK) {
   switch ($fileErr) {
    case UPLOAD_ERR_INI_SIZE:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_INI_SIZE');
     break;
    case UPLOAD_ERR_FORM_SIZE:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_FORM_SIZE');
     break;
    case UPLOAD_ERR_PARTIAL:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_PARTIAL');
     break;
    case UPLOAD_ERR_NO_FILE:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_NO_FILE');
     break;
    case UPLOAD_ERR_NO_TMP_DIR:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_NO_TMP_DIR');
     break;
    case UPLOAD_ERR_CANT_WRITE:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_CANT_WRITE');
     break;
    case UPLOAD_ERR_EXTENSION:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_EXTENSION');
     break;
    default:
     $retData[$reqID] = array('success' => false, 'error' => 'UPLOAD_UNKNOWN: '.$fileErr);
     break;
   }
   continue;
  }
  if (!array_key_exists('tmp_name', $_FILES[$reqID]))
  {
   $retData[$reqID] = array('success' => false, 'error' => 'FILE_DATA');
   continue;
  }
  $tmpFile = $_FILES[$reqID]['tmp_name'];
  if (filesize($tmpFile) < 1)
  {
   $retData[$reqID] = array('success' => false, 'error' => 'FILE_SIZE');
   continue;
  }
  if (!move_uploaded_file($tmpFile, $store.$reqID.'.bin'))
  {
   $retData[$reqID] = array('success' => false, 'error' => 'FILE_ACCESS');
   continue;
  }
  $retData[$reqID] = array('success' => true);
 }
 header('Content-Type: application/json');
 echo json_encode($retData);
?>