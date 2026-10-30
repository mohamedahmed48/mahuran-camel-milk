<?php
$file = 'process_actions.php';
$content = file_get_contents($file);

// Replace $_POST['some_id'] assignments with null if empty string
$content = preg_replace('/\$([a-zA-Z0-9_]+_id)\s*=\s*\$_POST\[\'\1\'\];/', '$$1 = (isset($_POST[\'$1\']) && $_POST[\'$1\'] !== \'\') ? $_POST[\'$1\'] : null;', $content);

// Also replace numeric/date fields that might cause issues if empty strings
// Like $_POST['age'], $_POST['salary'], $_POST['weight'], $_POST['daily_quantity']
$content = preg_replace('/\$([a-zA-Z0-9_]+)\s*=\s*\$_POST\[\'\1\'\];/', '$$1 = (isset($_POST[\'$1\']) && $_POST[\'$1\'] !== \'\') ? $_POST[\'$1\'] : null;', $content);

file_put_contents($file, $content);
echo "Fixed process_actions.php\n";
?>
