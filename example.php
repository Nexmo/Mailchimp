<?php
error_log('getting config');
require_once __DIR__ . '/config.php';

/**
 * Read MailChimp List into Memory
 */
error_log('fetching list');
$url = CHIMP_API . http_build_query(array(
    'apikey' => CHIMP_KEY,
    'id'   => CHIMP_LIST
));

if(!$memory = fopen('php://memory', 'rw')){
    error_log('could not open memory');
    return;
}

if(!$response  = fopen($url, 'r')){
    error_log('could not access list');
    return;
}

stream_copy_to_stream($response, $memory);
fclose($response);
rewind($memory);

/**
 * Iterate Through the List, and Sent SMS
 */
error_log('processing subscribers');
$fields = array();
while($line = fgets($memory)){
    if(NEXMO_DELAY){
        sleep(NEXMO_DELAY);
    }
    
    //grab the header
    if(empty($fields)){
        $fields = json_decode($line, true);
        if(isset($fields['error'])){
            error_log("mailchimp error: {$fields['error']}");
            return;
        }
        continue;
    }
    
    //combine the header with the data
    $user = array_combine($fields, json_decode($line));
    
    //send the sms
    $text = 'MailChimp Users can SMS with Nexmo!';
    $url = NEXMO_API . http_build_query(array(
        'username' => NEXMO_KEY,
        'password' => NEXMO_SECRET,
        'text' => $text,
        'to' => $user[CHIMP_FIELD],
        'from' => NEXMO_FROM
    ));
    
    error_log('sending message to: ' . $user[CHIMP_FIELD]);
    
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    
    if(!isset($response['message-count'])){
        error_log('  unexpected response from nexmo');
        continue;
    }
    
    error_log("  {$response['message-count']} messsages sent");
    
    foreach($response['messages'] as $message){
        if(isset($message['error-text'])){
            error_log('  error: ' . $message['error-text']);
            continue;
        }
        
        error_log('  message id: ' . $message['message-id']);
    }
}