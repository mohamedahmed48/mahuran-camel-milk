<?php
$content = file_get_contents('api_dashboard.php');
$content = str_replace("require_once 'auth.php';", "// require_once 'auth.php';", $content);
file_put_contents('scratch/api_dashboard_test.php', $content);
