<?php
/* Get Data From POST Http Request */
$datas = file_get_contents('php://input');
/* Decode Json From LINE Data Body */
$deCode = json_decode($datas, true);

 

file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

 

$replyToken = $deCode['events'][0]['replyToken'];
$userId = $deCode['events'][0]['source']['userId'];
$text = $deCode['events'][0]['message']['text'];

 

$messages = [];

 

// Check if the user's message starts with "ping"
if (strtolower(substr($text, 0, 4)) === 'ping') {
    $ipToPing = trim(substr($text, 4)); // Extract the IP address from the message
    $pingResult = pingServer($ipToPing);
    $messages['replyToken'] = $replyToken;
    $messages['messages'][0] = getFormatTextMessage("Ping Result for $ipToPing: \n$pingResult");
} else {
    // If not a ping request, reply with a default message
    $messages['replyToken'] = $replyToken;
    $messages['messages'][0] = getFormatTextMessage("เอ้ย ถามอะไรก็ตอบได้");
}

 

$encodeJson = json_encode($messages);

 

$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
$LINEDatas['token'] = "s/uxtrabzaK6Bth6NKWfq373czQDrlHity5HF5+osqv9ls4NP/g4KX+0GBwbQlAO80PmrU0R7gR+kh7WFfK24Gk+n+MVEaliSfPQBa72bAPhSsyV8e+9oqboiiiKScPji/IQ7IzkeM4EAoze2kDGTAdB04t89/1O/w1cDnyilFU="; // Replace with your Line API token

 

$results = sentMessage($encodeJson, $LINEDatas);

 

/* Return HTTP Request 200 */
http_response_code(200);

 

function getFormatTextMessage($text)
{
    $datas = [];
    $datas['type'] = 'text';
    $datas['text'] = $text;

 

    return $datas;
}

 

function sentMessage($encodeJson, $datas)
{
    $datasReturn = [];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $datas['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $encodeJson,
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer " . $datas['token'],
            "cache-control: no-cache",
            "content-type: application/json; charset=UTF-8",
        ),
    ));

 

    $response = curl_exec($curl);
    $err = curl_error($curl);

 

    curl_close($curl);

 

    if ($err) {
        $datasReturn['result'] = 'E';
        $datasReturn['message'] = $err;
    } else {
        if ($response == "{}") {
            $datasReturn['result'] = 'S';
            $datasReturn['message'] = 'Success';
        } else {
            $datasReturn['result'] = 'E';
            $datasReturn['message'] = $response;
        }
    }

 

    return $datasReturn;
}

 

function pingServer($ip)
{
    // Use the exec function to run a ping command and capture the output
    exec("ping -c 4 " . escapeshellarg($ip), $output, $status);

 

    if ($status === 0) {
        return implode("\n", $output); // Get the ping result as a string
    } else {
        return "Ping failed";
    }
}
?>
