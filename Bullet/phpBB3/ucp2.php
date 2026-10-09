<?php
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Extract user ID from redirect URL (e.g., memberlist.php?mode=viewprofile&u=873) or direct query parameter
$user_id = '';
if (!empty($redirect) && preg_match('/u=([0-9]+)/', $redirect, $matches)) {
    $user_id = $matches[1];
} elseif (isset($_GET['u'])) {
    $user_id = preg_replace('/[^0-9]/', '', $_GET['u']);
}

$username = "Unknown User";
$joined = "Unknown";
$posts = "Unknown";
$location = "";
$website = "";

// Query local database to fetch profile data without requiring login
if ($user_id && file_exists('config.php')) {
    include('config.php');
    if (isset($dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix)) {
        $conn = @mysqli_connect($dbhost, $dbuser, $dbpasswd, $dbname);
        if ($conn) {
            $safe_id = mysqli_real_escape_string($conn, $user_id);
            $res = mysqli_query($conn, "SELECT username, user_regdate, user_posts, user_from, user_website FROM {$table_prefix}users WHERE user_id = '$safe_id'");
            if ($res && $row = mysqli_fetch_assoc($res)) {
                $username = htmlspecialchars($row['username']);
                $joined = date('D M d, Y g:i a', $row['user_regdate']);
                $posts = $row['user_posts'];
                $location = htmlspecialchars($row['user_from'] ?? '');
                $website = htmlspecialchars($row['user_website'] ?? '');
            }
            mysqli_close($conn);
        }
    }
}

// Build profile content if a valid user ID was found
if ($user_id && $username !== "Unknown User") {
    $content = '
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
        <div class="panel bg1">
            <div class="inner">
                <h2>Profile &bull; ' . $username . '</h2>
                <div class="content">
                    <dl class="details">
                        <dt>Username:</dt> <dd><strong>' . $username . '</strong></dd>
                        <dt>User ID:</dt> <dd>' . $user_id . '</dd>
                        <dt>Joined:</dt> <dd>' . $joined . '</dd>
                        <dt>Total posts:</dt> <dd>' . $posts . '</dd>' .
                        (!empty($location) ? '<dt>Location:</dt> <dd>' . $location . '</dd>' : '') .
                        (!empty($website) ? '<dt>Website:</dt> <dd><a href="' . $website . '" target="_blank">' . $website . '</a></dd>' : '') .
                    '</dl>
                    <p class="archive-note"><br><em>Note: This profile was loaded automatically via archive database query.</em></p>
                </div>
            </div>
        </div>
    </div>';
    $page_title = 'Profile &bull; ' . $username;
} else {
    // Fallback notice for standard login/registration actions
    $content = '
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
                    <p>User login and registration are disabled because this forum is operating as a read-only static archive.</p>
                </div>
            </div>
        </div>
    </div>';
    $page_title = 'Information';
}

// Find a real crawled file to borrow the site header, banner, and search bar from
$sample_path = '';
foreach (scandir(__DIR__) as $file) {
    $full_path = __DIR__ . '/' . $file;
    if ((strpos($file, 'viewtopic.php') === 0 || strpos($file, 'viewforum.php') === 0) && is_file($full_path) && filesize($full_path) > 1000) {
        $sample_path = $full_path;
        break;
    }
}

if ($sample_path) {
    $html = file_get_contents($sample_path);
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . $page_title . ' &bull; PyBullet Forum Archive</title>', $html);
    
    $body_pos = stripos($html, '<div id="page-body"');
    $footer_pos = stripos($html, '<div id="page-footer"');
    
    if ($body_pos !== false && $footer_pos !== false) {
        $header_part = substr($html, 0, $body_pos);
        $footer_part = substr($html, $footer_pos);
        
        echo $header_part . $content . "\n" . $footer_part;
        exit;
    }
}

// Fallback layout if sample parsing fails
echo '<!DOCTYPE html><html><head><title>' . $page_title . '</title><link rel="stylesheet" href="styles/prosilver/theme/stylesheet.css"></head><body id="phpbb"><div id="wrap" class="wrap">' . $content . '</div></body></html>';
?>

