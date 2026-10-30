<?php
$c = file_get_contents('process_actions.php');
preg_match_all('/_POST\[\'([^\']+)\'\]/', $c, $m);
print_r(array_unique($m[1]));
?>
