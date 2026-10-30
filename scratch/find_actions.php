<?php
$lines = file('process_actions.php');
foreach ($lines as $num => $line) {
    if (strpos($line, "action == '") !== false) {
        preg_match("/action == '([^']+)'/", $line, $matches);
        if (isset($matches[1])) {
            echo $matches[1] . "\n";
        }
    }
}
