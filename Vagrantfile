# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

    config.vm.box = "ubuntu/xenial64"
    config.vm.network "private_network", ip: "192.168.33.13"
    config.vm.hostname = "nice"

    config.vm.provider :virtualbox do |v|
        v.customize ["modifyvm", :id, "--memory", 2048]
    end

    config.vm.synced_folder ".", "/var/www", :mount_options => ["dmode=777", "fmode=666"]

    config.vm.provision "shell", inline: <<-SHELL


        debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
        debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
        sudo apt update && sudo apt upgrade
        sudo apt install -y apache2 php7.0 libapache2-mod-php7.0 php7.0-mysql php7.0-curl php7.0-json php7.0-cgi php7.0-xml php-mbstring php-zip composer mysql-server mysql-client-core-5.7

        #############################################################
        # Apache config
        #############################################################

        sudo a2enmod rewrite expires headers
        sudo sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
        sudo tee /etc/apache2/sites-enabled/000-default.conf <<'apache-conf'

<VirtualHost *:80>
  ServerAdmin dustin638@gmail.com
  ServerAlias *.nice.dev
  ServerName nice.dev

  DocumentRoot /var/www/public
  ErrorLog /var/log/apache2/error.log

  # Possible values include: debug, info, notice, warn, error, crit,
  # alert, emerg.
  LogLevel notice

  CustomLog /var/log/apache2/access.log combined

  <Directory />
      Options Indexes FollowSymLinks
      AllowOverride All
      Require all granted
  </Directory>
</VirtualHost>
apache-conf

    cd /var/www && composer install
    rm -rf /var/www/html
    sudo service apache2 restart

   SHELL
end