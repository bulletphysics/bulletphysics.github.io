import os
import re

OUTPUT_DIR = "/var/www/html/bulletphysics.com/public_html/Bullet/phpBB3/"

# 1. Find a real working topic file as our master template
template_file = ""
for f in os.listdir(OUTPUT_DIR):
    full_path = os.path.join(OUTPUT_DIR, f)
    if (f.startswith("viewtopic.php?t=") or f.startswith("viewtopic.php%3Ft=")) and os.path.isfile(full_path) and os.path.getsize(full_path) > 2000:
        template_file = full_path
        break

if not template_file:
    print("Error: No working template file found on disk.")
    exit(1)

print("Using template file: {}".format(template_file))
with open(template_file, "r", encoding="utf-8", errors="ignore") as f:
    html = f.read()

# 2. Update page title
html = re.sub(r'<title>.*?</title>', '<title>Information &bull; PyBullet Forum Archive</title>', html, flags=re.DOTALL)

# 3. Define the official phpBB error panel block
error_content = '''<div class="navbar" role="navigation">
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
</div>'''

# 4. Safely split the master template at page-body and page-footer
parts_body = re.split(r'(<div[^>]*id="page-body"[^>]*>)', html, flags=re.IGNORECASE)
if len(parts_body) >= 3:
    header_part = parts_body[0] + parts_body[1]
    rest = parts_body[2]
    parts_footer = re.split(r'(<div[^>]*id="page-footer"[^>]*>)', rest, flags=re.IGNORECASE)
    if len(parts_footer) >= 3:
        footer_part = parts_footer[1] + parts_footer[2]
        final_error_page = header_part + "\n" + error_content + "\n" + footer_part
    else:
        final_error_page = html
else:
    final_error_page = html

# 5. Create the file explicitly for topic 3714 (both literal and encoded variants just to be completely safe)
target_literal = os.path.join(OUTPUT_DIR, "viewtopic.php?t=3714.html")
target_encoded = os.path.join(OUTPUT_DIR, "viewtopic.php%3Ft=3714.html")

with open(target_literal, "w", encoding="utf-8") as out:
    out.write(final_error_page)
with open(target_encoded, "w", encoding="utf-8") as out:
    out.write(final_error_page)

print("Successfully created test file for topic 3714!")
#python3 test_single.py
#sudo chown www-data:www-data /var/www/html/bulletphysics.com/public_html/Bullet/phpBB3/viewtopic.php*

