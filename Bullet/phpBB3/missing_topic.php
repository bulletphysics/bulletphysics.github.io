<?php
$topic_id = isset($_GET['t']) ? preg_replace('/[^0-9]/', '', $_GET['t']) : '';
header("HTTP/1.0 200 OK");

$error_panel = '
<div class="navbar" role="navigation">
    <div class="inner">
        <ul id="nav-breadcrumbs" class="nav-breadcrumbs linklist navlinks" role="menubar">
            <li class="breadcrumbs">
                <span class="crumb"><a href="index.php" data-navbar-reference="index">Board index</a></span>
            </li>
        </ul>
    </div>
</div>
<div id="page-body" class="page-body" role="main">
    <div class="panel">
        <div class="inner">
            <h2>Information</h2>
            <div class="content">
                <p>The requested topic does not exist.</p>
            </div>
        </div>
    </div>
</div>';

// Find a real crawled file on disk to borrow the site design, banner, and search bar from
$sample_file = '';
foreach (scandir(__DIR__) as $file) {
    if (strpos($file, 'viewtopic.php%3Ft=') === 0 && filesize($file) > 1000) {
        $sample_file = $file;
        break;
    }
}

if ($sample_file) {
    $html = file_get_contents($sample_file);
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>Information &bull; PyBullet Forum Archive</title>', $html);
    
    // Replace the main page body container with our error panel
    $pattern = '/(<div id="page-body"[^>]*>)(.*?)(<\/div>\s*<div id="page-footer">)/s';
    if (preg_match($pattern, $html)) {
        $html = preg_replace($pattern, '$1' . "\n" . $error_panel . "\n" . '$3', $html);
        echo $html;
        exit;
    }
}

// Fallback if no sample is found
echo '<!DOCTYPE html><html><head><title>Information</title><link rel="stylesheet" href="styles/prosilver/theme/stylesheet.css"></head><body id="phpbb"><div id="wrap" class="wrap"><div id="page-body">' . $error_panel . '</div></div></body></html>';
?>

