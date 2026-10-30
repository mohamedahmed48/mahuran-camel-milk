<?php
foreach (file('shop_pages.php') as $i => $l) {
    if (strpos($l, "subpage == 'invoice'") !== false || strpos($l, 'invoice') !== false) {
        echo ($i+1) . ': ' . trim($l) . "\n";
    }
}
