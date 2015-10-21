#!/usr/bin/env bash
apt-get install software-properties-common
apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
add-apt-repository 'deb http://ftp.osuosl.org/pub/mariadb/repo/10.1/ubuntu trusty main'

apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"

apt-get update

echo "Installing HHVM dependencies..."
export DEBIAN_FRONTEND=noninteractive
debconf-set-selections <<< 'mariadb-server-10.1 mysql-server/root_password password PASS'
debconf-set-selections <<< 'mariadb-server-10.1 mysql-server/root_password_again password PASS'

apt-get install -y nginx-full vim git mariadb-server mariadb-client
    language-pack-ro openjdk-7-jdk openjdk-7-jre g++

echo "Configuring MariaDB"
echo "[client]" >> /home/vagrant/.my.cnf
echo "user = root" >> /home/vagrant/.my.cnf
echo "password = PASS" >> /home/vagrant/.my.cnf

echo "Installing HHVM"
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
