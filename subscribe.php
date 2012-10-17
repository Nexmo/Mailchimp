<?php
/**
 * Subscribe an Email Address to a MailChimp List
 * Expects configuration details to be defined in config.php
 */
error_log('getting config');
require_once __DIR__ . '/config.php';

/**
 * Validate inbound message.
 */
if(!isset($_REQUEST['text']) OR !isset($_REQUEST['msisdn'])){
    error_log('invalid message: ' . var_export($_REQUEST, true));
    return;
}

/**
 * Search for email address.
 */
//just a simple match from http://bit.ly/Qrbaq5
if(!preg_match('#\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b#i', $_REQUEST['text'], $matches)){
    error_log('could not find address');
    return;
}

$email = $matches[0];
$phone = $_REQUEST['msisdn'];

/**
 * Assemble API Requests
 */
//assemble request for mailchimp
error_log('subscribing user: ' . $email);
$chimp = CHIMP_API . '1.3/?' . http_build_query(array(
    'apikey' => CHIMP_KEY,
    'id'   => CHIMP_LIST,
    'method' => 'listSubscribe',
    'email_address' => $email,
    'merge_vars['.CHIMP_FIELD.']' => $phone
));

//assemble request for nexmo
$text = 'Thanks! You should receive a confirmation email.';
$nexmo = NEXMO_API . http_build_query(array(
    'username' => NEXMO_KEY,
    'password' => NEXMO_SECRET,
    'text' => $text,
    'to' => $phone,
    'from' => NEXMO_FROM
));

/**
 * Make Requests
 */
try{
    $response = file_get_contents($chimp);
    $response = json_decode($response, true);
    
    if(isset($response['error'])){
        throw new Exception($response['error'], $response['code']);
    }
    
    error_log('subscribe request sent');
    
    $response = file_get_contents($nexmo);
    $response = json_decode($response, true);
    
    if(!isset($response['message-count'])){
        error_log('  unexpected response from nexmo');
        continue;
    }    
    
    error_log("{$response['message-count']} messsages sent to: " . $phone);
    
    foreach($response['messages'] as $message){
        if(isset($message['error-text'])){
            error_log('  error: ' . $message['error-text']);
            continue;
        }
        
        error_log('  message id: ' . $message['message-id']);
    }
        
} catch(Exception $e) {
    error_log($e->getMessage());
}