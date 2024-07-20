<?php

  function ppr($value, $die = false) {
    echo '<pre>';
    echo print_r($value, true);
    echo '</pre>';

    if ($die) {
      die();
    }
  }

  function call_bodygraph_api($endpoint, $body) {
    $api_url = $_ENV['BODYGRAPH_API_URL']; 
  
    $curl = curl_init();
  
    curl_setopt($curl, CURLOPT_URL, $api_url . $endpoint);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: ' . 'application/json',
      'Content-Length: ' . strlen(json_encode($body))
    ));
    
    $response = curl_exec($curl);
    
    if(curl_error($curl)) {
      die(curl_error($curl));
    }
    
    curl_close($curl);
    return $response;
  }
