#!/usr/bin/env bash
yum -y update

# Install nano editor
yum -y install nano

# Install default web server (apache2)
yum -y install httpd

# Add epel packages to allow installation of PHP 5.6
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
yum -y install php56w php56w-opcache
yum -y install php56w-xml
yum -y install php56w-pdo

# Give php.ini a timezone to stop php configuration moans
sed -i "s/;date.timezone =/date.timezone = Europe\/Berlin/g" /etc/php.ini

# Set up virtual hosts directories for apache
mkdir /etc/httpd/sites-available
mkdir /etc/httpd/sites-enabled

# Add basic virtual host file for our restaurant search
cat > /etc/httpd/sites-available/restaurantsearch.davehamber.com.conf << EOF
<VirtualHost *:80>
    ServerName restaurantsearch.davehamber.local
    ServerAlias restaurantsearch.davehamber.local

    DocumentRoot /vagrant/web
    <Directory /vagrant/web>
        # enable the .htaccess rewrites
        AllowOverride All
        Require all granted
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/restaurantsearch.davehamber.com/html>
    #    Option FollowSymlinks
    # </Directory>

    ErrorLog /var/log/httpd/restaurantsearch.davehamber.com_error.log
    CustomLog /var/log/httpd/restaurantsearch.davehamber.com_access.log combined
</VirtualHost>
EOF

# Link available virtual host to enabled virtual host
ln -s /etc/httpd/sites-available/restaurantsearch.davehamber.com.conf /etc/httpd/sites-enabled/restaurantsearch.davehamber.com.conf

# Tell httpd.conf to look for config files in sites-enabled
echo "IncludeOptional sites-enabled/*.conf" >> /etc/httpd/conf/httpd.conf

# Alter SELinux settings to allow web access to vagrant directory
chcon -Rv --type=httpd_sys_rw_content_t /vagrant/
restorecon -v /vagrant/web

# Install and run database
yum -y install mariadb-server mariadb
systemctl start mariadb.service
systemctl enable mariadb.service

# Make a restaurantsearch user + db and set root db password
mysql -u root -e "CREATE USER 'restaurantsearch'@'localhost' IDENTIFIED BY 'restaurantsearch';"
mysql -u root -e "GRANT USAGE ON *.* TO 'restaurantsearch'@'localhost' IDENTIFIED BY 'restaurantsearch' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;"
mysql -u root -e "CREATE DATABASE IF NOT EXISTS restaurantsearch;"
mysql -u root -e "GRANT ALL PRIVILEGES ON restaurantsearch.* TO 'restaurantsearch'@'localhost';"
mysql -u root -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('devpassword');"

# Install phpmyadmin
yum -y install phpmyadmin

# Allow phpmyadmin to be accessed from any ip and not just localhost, for dev purposes
sed -i "s/Require ip 127.0.0.1/# Require ip 127.0.0.1/g" /etc/httpd/conf.d/phpMyAdmin.conf
sed -i "s/Require ip ::1/# Require ip ::1\\n       Require all granted/g" /etc/httpd/conf.d/phpMyAdmin.conf

# Allow the app/cache and app/logs to be writeable by both apache and the vagrant user
setfacl -R -m u:apache:rwX -m u:vagrant:rwX /vagrant/app/cache /vagrant/app/logs /vagrant/web/streetview
setfacl -dR -m u:apache:rwX -m u:vagrant:rwX /vagrant/app/cache /vagrant/app/logs /vagrant/web/streetview

# Create restaurantsearch tables
php /vagrant/app/console doctrine:schema:update --force

# Add apache to boot and start
systemctl enable httpd.service
service httpd start
