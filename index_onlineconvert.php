<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');

$mp3captcha = rawurldecode($_POST['audiocaptcha']);

$post = '------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="UPLOAD_PROGRESS"

fa1f87153df39bdafdf637295a000c7b
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="file"; filename=""
Content-Type: application/octet-stream


------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="external_url"

'.$mp3captcha.'
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="bit_depth"

0
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="frequency"

16000
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="channel"

0
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="audio_start"


------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="audio_end"


------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="string_method"

convert-to-flac
------WebKitFormBoundaryhnA39CIALDIS0Bdc
Content-Disposition: form-data; name="upload_token"


------WebKitFormBoundaryhnA39CIALDIS0Bdc--';

//////////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'session.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'session.txt');
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36');
curl_setopt($ch, CURLOPT_URL, 'http://audio.online-convert.com/convert-to-flac');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$output = curl_exec($ch);
curl_close ($ch);

$postlink = explode('class="forms" action="', $output);
$postlink = explode('" name="forms"', $postlink[1]);
$postlink = $postlink[0];

if(empty($postlink)){
	exit('Error#Postlink');
}

//////////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'session.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'session.txt');
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36');
curl_setopt($ch, CURLOPT_URL, $postlink);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryhnA39CIALDIS0Bdc'));

$output = curl_exec($ch);

curl_close ($ch);

if(!empty($output)){
	$getDL = explode('Click here to <a href="', $output);
	$getDL = explode('">download the', $getDL[1]);
	$getDL = $getDL[0];
	if(!empty($getDL)){
		sleep(10);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'session.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'session.txt');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36');
		curl_setopt($ch, CURLOPT_URL, $getDL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$getFlac = curl_exec($ch);
		curl_close($ch);
	}
}

if(empty($getFlac)){
	exit('Error#Converting: '.$postlink.' ###### '.$output);
}

//
$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/speech-api/v2/recognize?client=chromium&lang=en_US&key=AIzaSyAcalCzUvPmmJ7CZBFOEWx2Z1ZSn4Vs1gg');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, '@'.$getFlac);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: audio/x-flac; rate=16000'));

$googleVoice = curl_exec($ch);

curl_close ($ch);

try {
	$result = explode('{"alternative":[{"transcript":"', $googleVoice);
	if(count($result)>1){
		$result = explode('","confidence":', $result[1]);
		$confidence = explode('},{', $result[1]);
		$confidence = $confidence[0];
		$result = $result[0];
		$response = array('result' => $result, 'confidence' => $confidence);
		echo json_encode($response);
	}else{
		echo 'Error#Parsing: '.$postlink.' ## flac ## '.$getFlac.' ## dl ## '.$getDL.' ## output ## '.$output.' ## voice ## '.$googleVoice;
	}
} catch (Exception $e) {
	echo 'Error#Resolving';
}
?>