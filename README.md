sudo apt-get update -y
sudo apt-get upgrade -y
sudo apt install nginx -y
sudo systemctl stop nginx
sudo systemctl start nginx
sudo systemctl stop nginx
sudo systemctl start nginx
sudo systemctl enable nginx
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php
sudo apt install php-fpm php-sqlite3 php-curl -y
sudo git clone https://github.com/karimi-amin/v2ray-api.git /v2ray-api
sudo chgrp -Rf www-data /v2ray-api/storage /v2ray-api/bootstrap/cache
sudo chmod -Rf ug+rwx /v2ray-api/storage /v2ray-api/bootstrap/cache
sudo chmod -Rf 775 /v2ray-api/storage/ /v2ray-api/bootstrap/
sudo chgrp -Rf www-data /etc/x-ui
sudo chmod -Rf 775 /etc/x-ui
sudo chgrp -Rf www-data /usr/local/x-ui/bin
sudo chmod -Rf 775 /usr/local/x-ui/bin

upload default file into /etc/nginx/sites-available
sudo systemctl restart nginx
