<?php
$topic_id = isset($_GET['t']) ? preg_replace('/[^0-9]/', '', $_GET['t']) : '';

if ($topic_id) {
    // 1. Check for the literal question mark filename (how wget saved real topics)
    $filename_literal = "viewtopic.php?t=" . $topic_id . ".html";
    // 2. Check for the URL-encoded filename as a backup
    $filename_encoded = "viewtopic.php%3Ft=" . $topic_id . ".html";

    if (file_exists($filename_literal) && filesize($filename_literal) > 0) {
        include($filename_literal);
        exit;
    } elseif (file_exists($filename_encoded) && filesize($filename_encoded) > 0) {
        include($filename_encoded);
        exit;
    }
}

// 3. Missing/Deleted Topic: Dynamically output the fully branded error page with banner and search bar
header("HTTP/1.0 200 OK");

$error_content = '
<div class="navbar" role="navigation">
    <div class="inner">
        <ul id="nav-breadcrumbs" class="nav-breadcrumbs linklist navlinks" role="menubar">
            <li class="breadcrumbs">
                <span class="crumb"><a href="index.php" data-navbar-reference="index">Board index</a></span>
            </li>
        </ul>
    </div>
</div>
<div class="panel">
    <div class="inner">
        <h2>Information</h2>
        <div class="content">
            <p>The requested topic does not exist.</p>
        </div>
    </div>
</div>';

// Find a real crawled file on disk to borrow the site design from
$sample_path = '';
foreach (scandir(__DIR__) as $file) {
    $full_path = __DIR__ . '/' . $file;
    if ((strpos($file, 'viewtopic.php?t=') === 0 || strpos($file, 'viewtopic.php%3Ft=') === 0) && is_file($full_path) && filesize($full_path) > 1000) {
        $sample_path = $full_path;
        break;
    }
}

if ($sample_path) {
    $html = file_get_contents($sample_path);
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>Information &bull; PyBullet Forum Archive</title>', $html);
    
    $body_pos = stripos($html, '<div id="page-body"');
    $footer_pos = stripos($html, '<div id="page-footer"');
    
    if ($body_pos !== false && $footer_pos !== false) {
        $header_part = substr($html, 0, $body_pos);
        $footer_part = substr($html, $footer_pos);
        
        echo $header_part . '<div id="page-body" class="page-body" role="main">' . "\n" . $error_content . "\n" . '</div>' . "\n" . $footer_part;
        exit;
    }
}

// Fallback if sample parsing fails
echo '<!DOCTYPE html><html><head><title>Information</title><link rel="stylesheet" href="styles/prosilver/theme/stylesheet.css"></head><body id="phpbb"><div id="wrap" class="wrap"><div id="page-body" class="page-body">' . $error_content . '</div></div></body></html>';
?>

