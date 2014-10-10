#!/usr/bin/env bash

apt-get update
echo "Installing HHVM dependencies..."

sudo debconf-set-selections <<< 'mysql-server-5.5
    mysql-server/root_password password'
sudo debconf-set-selections <<< 'mysql-server-5.5
    mysql-server/root_password_again password'

apt-get install -y nginx-full vim git mysql-client-core-5.5 mysql-server\
    language-pack-ro openjdk-7-jdk openjdk-7-jre

echo "Installing HHVM"
wget -O - http://dl.hhvm.com/conf/hhvm.gpg.key | sudo apt-key add -
echo deb http://dl.hhvm.com/ubuntu saucy main | sudo tee \
    /etc/apt/sources.list.d/hhvm.list
apt-get update
apt-get install -y hhvm

/usr/share/hhvm/install_fastcgi.sh

echo "Setting up nginx"
cp /var/infoarena/repo/nginx/sites-available/infoarena \
    /etc/nginx/sites-available/infoarena

rm -rf /etc/nginx/sites-enabled/default

if [ ! -e "/etc/nginx/sites-enabled/infoarena" ]; then
    ln -s /etc/nginx/sites-available/infoarena \
        /etc/nginx/sites-enabled/infoarena
fi

sed -i 's/^#RUN_AS_USER="www-data"$/RUN_AS_USER="vagrant"/g' /etc/default/hhvm

echo "Restarting services"
/etc/init.d/nginx restart
/etc/init.d/hhvm restart

update-rc.d hhvm defaults

echo "Installing libphutil + arcanist"
cd /var/infoarena
if [ ! -d "/var/infoarena/libphutil" ]; then
    git clone git://github.com/facebook/libphutil.git
fi
cd libphutil
git pull

cd /var/infoarena
if [ ! -d "/var/infoarena/arcanist" ]; then
    git clone git://github.com/facebook/arcanist.git
fi
cd arcanist
git pull

if [ ! -e "/usr/bin/arc" ]; then
    ln -s /var/infoarena/arcanist/bin/arc /usr/bin/arc
fi
