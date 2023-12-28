#!/bin/bash
#
# Configuration script to be run when a new client is first checked out

function copyFromSampleFile() {
  if [ ! -e $1 ]; then
    echo "* copying $1.sample to $1"
    cp $1.sample $1
  else
    echo "* $1 already exists, skipping"
  fi
}

# for OS X compatibility, do not use readlink
cd `dirname $0`
CWD=`pwd`
ROOT_DIR=`dirname $CWD`
cd $ROOT_DIR
echo "The root of your client appears to be $ROOT_DIR"

copyFromSampleFile 'Config.php'
copyFromSampleFile 'eval/config.php'
copyFromSampleFile 'www/.htaccess'

# Make the Smarty compiled templates directory world-writable
echo "* making some directories and files world-writable"
mkdir -p /tmp/templates_c
chmod 777 /tmp/templates_c

# Make the resized images directory world-writable
chmod 777 www/static/images/resized/

echo "* compiling lcs.cpp"
g++ common/lcs.cpp -O2 -static -o common/lcs
