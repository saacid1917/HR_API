<?php

$url = 'https://daily-petrol-diesel-lpg-cng-fuel-prices-in-india.p.rapidapi.com/v1/fuel-prices/today/india/maharashtra/mumbai';
$headers = [
    'x-rapidapi-key: 4dc55378c0msh631bdb2e67171dbp135aa4jsn49cdba9ba6cc',
    'x-rapidapi-host: daily-petrol-diesel-lpg-cng-fuel-prices-in-india.p.rapidapi.com'
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);