<?php
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");
// parameters
$hubVerifyToken = 'JobMagicToken';
$accessToken = "EAAE2ZBze3aCwBAN6EPuKhMWfqDC4uX8k2l7uLZBiZCRryRZC6ZBpk6xE3jMenwmYQvz0k3HUrcsSAdxIj14xj0bs4H6Qk2StZBWksDxNyOa0NN2r5RoJnrMiI1yKuT66J27ZAtQYbeIYnfDNNZAOcC9XEKZCHhyKWTbiiImQiKKgIGAZDZD";
// check token at setup
if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
  echo $_REQUEST['hub_challenge'];
  exit;
}

// handle bot's anwser
$input = json_decode(file_get_contents('php://input'), true);
$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
$messageText = $input['entry'][0]['messaging'][0]['message']['text'];
//$answer = "I don't understand. Ask me 'Indeed then search keyword eg: Java'.";
if($messageText == "hi") {
    $answer = "Hello, Welcome to JobMagic! Search through Indeed's job database by keyword. eg: Search Indeed Java";
    $response = [
    
        'recipient' => [ 'id' => $senderId ],
        'message' => [ 'text' => $answer ]
    ];
    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
elseif(strpos($messageText, "Indeed")==false){
    $answer = "I don't understand. Ask me 'Search Indeed keyword eg: Search Indeed Java'.";
    $response = [
    
        'recipient' => [ 'id' => $senderId ],
        'message' => [ 'text' => $answer ]
    ];
    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
elseif(strpos($messageText, "Indeed")>=0){
    $answer = "Got Indeed";
    $answer = "We are searching for job results, please be patient.";
    $response = [
    
        'recipient' => [ 'id' => $senderId ],
        'message' => [ 'text' => $answer ]
    ];
    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
    
    
    $ch = curl_init("http://api.indeed.com/ads/apisearch?publisher=5940159532938846&l=austin%2C+tx&sort=&radius=&st=&jt=&start=&limit=&fromage=&filter=&latlong=1&co=us&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2&q=" . $messageText);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $results = new SimpleXMLElement($output);
    $output = Array(
        Array(
            "job" => $results->results[0]->result[0]->jobtitle,
            "imageUrl" => "http://www.talenthq.com/wp-content/uploads/2015/05/Indeed-Logo.jpg",
            //"imageUrl" => "http://placehold.it/250/250",
            "url" => $results->results[0]->result[0]->url
        )
    );
    file_put_contents("output.txt", print_r($results, true));
    file_put_contents("output-2.txt", print_r($output, true));
    //Change the path of the parser.py
    // $command = 'python /home/ubuntu/workspace/parser.py "'.$messageText.'"';
    // $output = exec($command);
    // $output = json_decode($output, true);
    foreach ($output as $value) {
        $response = '{"recipient":{"id":"'.$senderId;
        $response = $response.'"},"message":{"attachment":{"type":"template","payload":{"template_type":"generic",';
        $response = $response.'"elements":[{"title":"'.str_replace('"','\"',$value['job']);
        $response = $response.'","image_url":"'.str_replace('"','\"',$value['imageUrl']);
        $response = $response.'","buttons":[{"type":"web_url","url":"'.str_replace('"','\"',$value['url']);
        $response = $response.'","title":"Open Web Url"}]}]}}}}';
        $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}
?>