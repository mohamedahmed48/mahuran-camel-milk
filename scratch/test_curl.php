<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/mahu/login.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=password&login=1"); // We need valid credentials... 
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, "http://localhost/mahu/api_dashboard.php");
curl_setopt($ch, CURLOPT_POST, 0);
$res = curl_exec($ch);
echo "RESPONSE:\n";
echo $res;
