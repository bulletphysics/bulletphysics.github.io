<?php
// Handle forum requests like viewforum.php?f=18
if (isset($_GET['f'])) {
    $forum_id = preg_replace('/[^0-9]/', '', $_GET['f']);
    $filename = "viewforum.php?f={$forum_id}.html";
    
    if (file_exists($filename)) {
        include($filename);
        exit;
    }
}

header("HTTP/1.0 404 Not Found");
echo "Forum not found.";
?>

