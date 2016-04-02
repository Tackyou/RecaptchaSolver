<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');

@unlink('audio.mp3');
@unlink('output.flac');

if(!isset($_POST['audiocaptcha']) || empty($_POST['audiocaptcha'])){
	exit('Error#Audiocaptcha');
}
$mp3captcha = rawurldecode($_POST['audiocaptcha']);

file_put_contents('audio.mp3', file_get_contents($mp3captcha));

$handle = popen('ffmpeg.exe -y -i audio.mp3 -ar 16000 -ab 48k output.flac 2>&1', 'r');
if ($handle !== false)
{
	while (($char = fgetc($handle)) !== false)
	{
		// working
	}
	$returnVar = pclose($handle);
}
if ($returnVar === 0){
	// converted successfully
}else{
	exit('Error#Converting');
}

$getFlac = file_get_contents('output.flac');

//
$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/speech-api/v2/recognize?client=chromium&lang=en_US&key=AIzaSyDSeaYzUGERYLVC4MS5HQ9TUa2EI94sULg');
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
		if(count($result)>1){
			$confidence = explode('},{', $result[1]);
			$confidence = $confidence[0];
			$result = $result[0];
			$response = array('result' => $result, 'confidence' => $confidence);
			echo json_encode($response);
		}else{
			echo 'Error#Parsing: # 2 #'.$googleVoice;
		}
	}else{
		echo 'Error#Parsing: # 1 #'.$googleVoice;
	}
} catch (Exception $e) {
	echo 'Error#Resolving';
}
?>