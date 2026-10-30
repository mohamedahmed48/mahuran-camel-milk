<?php
$lines = file('sales_pages.php');
foreach ($lines as $num => $line) {
    if (strpos($line, 'notifications') !== false) {
        echo ($num + 1) . ": " . $line;
    }
}
