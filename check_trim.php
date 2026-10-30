<?php
$c = file_get_contents('process_actions.php');
preg_match_all('/trim\(\\$_POST\[\'([^\']+)\'\]\)/', $c, $m);
$ids = array_filter(array_unique($m[1]), function($v) { return substr($v, -3) === '_id'; });
print_r($ids);
?>
