cd `dirname $0`

type phantomjs --help >/dev/null 2>&1 

if [ ! $? -eq 0 ]; then
	brew update && brew install phantomjs
fi

VERSION="selenium-server-standalone-2.33.0.jar"

if [ ! -f $VERSION ]; then
	wget https://selenium.googlecode.com/files/$VERSION
fi

if [ -f "selenium.pid" ]; then
	kill -9 `cat selenium.pid` &>/dev/null

	rm selenium.pid
fi

ps -ef | grep "java -jar selenium-server" | grep -v grep &>/dev/null

if [ $? -eq 0 ]; then
	echo "WARNING: A version of selenium is already running."
fi

java -jar $VERSION -role hub &>/dev/null &

echo "$!" > selenium.pid

sleep 5s

if [ -f "phantom.pid" ]; then
	kill -9 `cat phantom.pid` &>/dev/null
	
	rm phantom.pid;
fi

ps -ef | grep "phantomjs" | grep -v grep  &>/dev/null

if [ $? -eq 0 ]; then
	echo "WARNING: A version of phantomjs is already running."
fi

phantomjs --webdriver=127.0.0.1:8910 --webdriver-selenium-grid-hub=http://127.0.0.1:4444 &>/dev/null &

echo "$!" > phantom.pid

sleep 2s

type pear >/dev/null 2>&1 || { echo >&2 "Pear is not installed."; exit 1; }

echo '<?php require "PHPUnit/Autoload.php"; require "PHPUnit/Extensions/Selenium2TestCase.php"; ?>' | php &>/dev/null

if [ ! $? -eq 0 ]; then
	pear install phpunit/PHPUnit_Selenium
fi

if [ ! -d "vagrantpress-wordpress-fantastic-elasticsearch" ]; then
	wget https://github.com/parisholley/vagrantpress/archive/wordpress-fantastic-elasticsearch.zip
	unzip wordpress-fantastic-elasticsearch.zip
	rm wordpress-fantastic-elasticsearch.zip
fi

cd vagrantpress-wordpress-fantastic-elasticsearch
vagrant up

PLUGIN_DIR="wordpress/wp-content/plugins/wordpress-fantastic-elasticsearch"

if [ -d $PLUGIN_DIR ]; then
	rm -rf $PLUGIN_DIR
fi

mkdir $PLUGIN_DIR
cp -rf ../../elasticsearch.php $PLUGIN_DIR/
cp -rf ../../src/ $PLUGIN_DIR/src
cp -rf ../../vendor/ $PLUGIN_DIR/vendor
cp -rf ../../wp/ $PLUGIN_DIR/wp

cd ..

phpunit --no-configuration --verbose --bootstrap="selenium-tests/bootstrap.php" selenium-tests;

if [ -f "phantom.pid" ]; then
	kill -9 `cat phantom.pid`
	
	rm phantom.pid;
fi

if [ -f "selenium.pid" ]; then
	kill -9 `cat selenium.pid`

	rm selenium.pid;
fi

if [ $? -eq 0 ]; then
	cd vagrantpress-wordpress-fantastic-elasticsearch;

	vagrant suspend
fi
