# Log viewer

Password protected page at <https://eszleles.hu/naplok> for watching the requests and errors of the
live site. It is a single PHP file served by the same Apache, independent of the Yii app, so it works
when the app is down.

## What the pages show

| Page | Source | Content | Updates |
|---|---|---|---|
| **Kérések** | `/var/log/eszleles/access.log` | Every request: IP, time, URL, status code, referrer, browser. 4xx is yellow, 5xx is red. | every 2 s |
| **Yii hibák** | `runtime/logs/app.log` | Errors and warnings of the app, with stack traces. Normal requests are not logged here. | every 2 s |
| **Apache/PHP hibák** | `/var/log/eszleles/error.log` | Errors of Apache and PHP, including fatal errors that never reach Yii, and failed logins to the viewer. | every 2 s |
| **Statisztika** | `access.log` and its rotated files | GoAccess report of the last two weeks or so: visitors, most requested pages, 404s, IPs, browsers, referrers, status codes. | rebuilt every minute, reload to see it |

On the first three pages:

- The newest entry is on top. A page starts with the last 256 KB of the log and keeps 3000 entries.
- **Szűrés** shows only the entries that contain the text. **Szünet** stops the updates.
- **Képek, css, js elrejtése** (only on Kérések, on by default) hides the static files.
- Passwords, cookies, tokens and session ids are masked as `***`. This is best effort and only affects
  the page, the log files still contain them.

Good to know:

- The viewer's own requests are left out of Kérések and Statisztika.
- Visitors who only got 4xx responses are not counted as visitors in Statisztika (GoAccess default).

## Files

| File | Installed to | Purpose |
|---|---|---|
| `index.php` | `/var/www/logs.eszleles.hu/index.php` | The viewer |
| `naplok.conf` | `/etc/apache2/eszleles-naplok.conf` | `/naplok` URL, password protection, log locations |
| `cron` | `/etc/cron.d/eszleles-logs` | Builds Statisztika |
| `logrotate` | `/etc/logrotate.d/eszleles` | Rotates the Apache logs |
| `logrotate-timer.conf` | `/etc/systemd/system/logrotate.timer.d/override.conf` | Runs logrotate hourly |
| `logs.eszleles.hu.conf` | not installed | Vhost for using a subdomain instead, see below |

## Installation

For Debian/Ubuntu, with the app in `/var/www/html/eszleleshu` and Apache and PHP running as `www-data`.
Check the user with the command below. If PHP runs as another user (php-fpm), use that instead of
`www-data` everywhere, except for `logs.htpasswd`, which Apache reads.

```bash
ps -eo user,group,comm | grep -E 'apache2|httpd|php-fpm' | sort | uniq -c
```

### 1. Protect the app folder

The app is inside Apache's default web root, so without this the viewer, `.git` and the logs may be
reachable without a password at `http://SERVER_IP/eszleleshu/`. It requires the `DocumentRoot` of the
site to be `/var/www/html/eszleleshu/web`:

```bash
grep -Rn DocumentRoot /etc/apache2/sites-enabled/

sudo tee /etc/apache2/conf-available/eszleles-protect.conf > /dev/null <<'EOF'
<Directory /var/www/html/eszleleshu>
    Require all denied
</Directory>
<Directory /var/www/html/eszleleshu/web>
    Require all granted
</Directory>
EOF
sudo a2enconf eszleles-protect
sudo apache2ctl configtest && sudo systemctl reload apache2
```

No site may have `/var/www` itself as its `DocumentRoot`, that would expose the installed viewer.

### 2. Packages and folders

```bash
sudo apt update
sudo apt install goaccess apache2-utils logrotate cron
sudo install -d -o root -g www-data -m 2750 /var/log/eszleles
sudo install -d -o www-data -g www-data /var/lib/eszleles-logs
sudo install -d /var/www/logs.eszleles.hu
```

### 3. Files

```bash
cd /var/www/html/eszleleshu/deploy/logs
sudo install -m 644 index.php /var/www/logs.eszleles.hu/index.php
sudo sed -i 's#/var/www/eszleles.hu/runtime#/var/www/html/eszleleshu/runtime#' /var/www/logs.eszleles.hu/index.php
sudo install -m 644 naplok.conf /etc/apache2/eszleles-naplok.conf
sudo install -m 644 cron /etc/cron.d/eszleles-logs
sudo install -m 644 logrotate /etc/logrotate.d/eszleles
sudo install -D -m 644 logrotate-timer.conf /etc/systemd/system/logrotate.timer.d/override.conf
sudo systemctl daemon-reload
sudo systemctl restart logrotate.timer
```

### 4. Password

```bash
sudo htpasswd -cB /etc/apache2/logs.htpasswd USERNAME
sudo chown root:www-data /etc/apache2/logs.htpasswd
sudo chmod 640 /etc/apache2/logs.htpasswd
```

### 5. Apache

Find the HTTPS vhost of the site with `sudo apache2ctl -S` (port 443) and make a backup of its file.
In the `<VirtualHost *:443>` block delete the `ErrorLog` and `CustomLog` lines, and add:

```apache
Include /etc/apache2/eszleles-naplok.conf
```

Do not add it to the port 80 vhost, the password would travel unencrypted. Then:

```bash
sudo apache2ctl configtest && sudo systemctl reload apache2
```

If fail2ban is used, its Apache jails have to watch `/var/log/eszleles/error.log`.

### 6. Check

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://eszleles.hu/naplok/                     # 401
curl -s -o /dev/null -w '%{http_code}\n' https://eszleles.hu/naplok/stats                # 401
curl -s -o /dev/null -w '%{http_code}\n' http://SERVER_IP/eszleleshu/composer.json       # not 200
curl -sk -o /dev/null -w '%{http_code}\n' https://SERVER_IP/eszleleshu/composer.json     # not 200
sudo logrotate -d /etc/logrotate.d/eszleles                                              # no errors
systemctl list-timers logrotate.timer                                                    # NEXT: next full hour
```

Only the requests made after the reload appear. Statisztika takes about a minute.

## Maintenance

**Update:** the files are copied, so a `git pull` alone changes nothing. Repeat step 3 for the changed
files (after `index.php` run the `sed` line too), and reload Apache if `naplok.conf` changed.

**Users:**

```bash
sudo htpasswd -B /etc/apache2/logs.htpasswd USERNAME   # add, or change password (-c would erase the others)
sudo htpasswd -D /etc/apache2/logs.htpasswd USERNAME   # remove
```

**Log sizes:** the Apache logs are rotated daily, or at the next hourly run when bigger than 100 MB, and
14 old files are kept. Yii rotates `app.log` itself at 10 MB and keeps 5 old files.

**Uninstall:** restore the vhost backup and reload Apache, then:

```bash
sudo rm /etc/cron.d/eszleles-logs /etc/systemd/system/logrotate.timer.d/override.conf
sudo systemctl daemon-reload && sudo systemctl restart logrotate.timer
```

## Using a subdomain instead

The same viewer works at `logs.eszleles.hu`. After the steps above and adding the DNS record:

```bash
sudo install -m 644 logs.eszleles.hu.conf /etc/apache2/sites-available/logs.eszleles.hu.conf
sudo a2ensite logs.eszleles.hu
sudo apache2ctl configtest && sudo systemctl reload apache2
sudo certbot --apache -d logs.eszleles.hu   # choose redirect to HTTPS, log in only after this
```

To turn off `/naplok`, remove the `Alias` and `<Location>` parts from `/etc/apache2/eszleles-naplok.conf`,
and keep the rest, it writes the logs.
