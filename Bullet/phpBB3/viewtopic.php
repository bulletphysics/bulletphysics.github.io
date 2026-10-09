<?php
$topic_id = isset($_GET['t']) ? preg_replace('/[^0-9]/', '', $_GET['t']) : '';

if ($topic_id) {
    $filename_literal = "viewtopic.php?t=" . $topic_id . ".html";
    $filename_encoded = "viewtopic.php%3Ft=" . $topic_id . ".html";

    if (file_exists($filename_literal) && filesize($filename_literal) > 0) {
        include($filename_literal);
        exit;
    } elseif (file_exists($filename_encoded) && filesize($filename_encoded) > 0) {
        include($filename_encoded);
        exit;
    }
}

// Missing/Deleted Topic: Load a real working file as a complete master template
header("HTTP/1.0 200 OK");

$sample_path = '';
foreach (scandir(__DIR__) as $file) {
    $full_path = __DIR__ . '/' . $file;
    if ((strpos($file, 'viewtopic.php?t=') === 0 || strpos($file, 'viewtopic.php%3Ft=') === 0) && is_file($full_path) && filesize($full_path) > 2000) {
        $sample_path = $full_path;
        break;
    }
}

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
<div class="panel">
    <div class="inner">
        <h2>Information</h2>
        <div class="content">
            <p>The requested topic does not exist.</p>
        </div>
    </div>
</div>';

if ($sample_path) {
    $html = file_get_contents($sample_path);
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>Information &bull; PyBullet Forum Archive</title>', $html);
    
    // Instead of cutting, let's find the post block container class typical of prosilver (e.g. .post or .panel) and replace the main content block
    // We can locate the breadcrumbs or the start of the topic posts and replace them.
    // Let's target the post container or wrap div cleanly.
    $html = preg_replace('/<div class="post [^"]*">.*?<\/div>\s*(?=<\/div>\s*<\/div>\s*<form)/s', $error_panel, $html, 1, $count);
    
    if ($count > 0) {
        echo $html;
        exit;
    }
    
    // Alternative universal swap: replace everything between header/nav and footer using standard markers
    $start_pos = stripos($html, 'id="page-body"');
    $end_pos = stripos($html, 'id="page-footer"');
    if ($start_pos !== false && $end_pos !== false) {
        // Find the opening tag closing '>' of page-body
        $tag_end = strpos($html, '>', $start_pos);
        if ($tag_end !== false) {
            $output = substr($html, 0, $tag_end + 1) . "\n" . $error_panel . "\n" . substr($html, $end_pos);
            echo $output;
            exit;
        }
    }
    
    // If all else fails, just output the unmodified sample file so the banner and search bar are 100% guaranteed to be there
    echo $html;
    exit;
}

// Absolute emergency fallback
echo '<!DOCTYPE html><html><head><title>Information</title><link rel="stylesheet" href="styles/prosilver/theme/stylesheet.css"></head><body id="phpbb"><div id="wrap" class="wrap"><div id="page-body">' . $error_panel . '</div></div></body></html>';
?>

