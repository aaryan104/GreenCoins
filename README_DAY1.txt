GreenCoin — Day 1 Starter

1) Install XAMPP (Windows) or MAMP/LAMP (Mac/Linux). Start Apache + MySQL.
2) Copy the 'greencoin' folder into your web root:
   - Windows (XAMPP): C:\xampp\htdocs\greencoin
   - Linux: /var/www/html/greencoin
   - Mac (MAMP default): /Applications/MAMP/htdocs/greencoin  (update BASE_URL if needed)
3) Open http://localhost/phpmyadmin  -> Import  -> select _sql/greencoin_schema.sql
4) Visit http://localhost/greencoin  -> You should see 'Setup Check' page.
5) If DB tables missing, re-import the SQL. Edit config.php if your MySQL password isn't empty.

Next (Day 2): build login/register pages using password_hash/password_verify.
