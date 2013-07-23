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
	ps -ef | grep `cat selenium.pid` | grep -v "grep" >/dev/null 2>&1
	
	if [ $? -eq 1 ]; then
		echo "Process defined in selenium.pid is invalid."
		rm selenium.pid;
	fi
fi

if [ ! -f "selenium.pid" ]; then
	java -jar $VERSION -role hub &>/dev/null &

	echo "$!" > selenium.pid

	sleep 5s
fi

if [ -f "phantom.pid" ]; then
	ps -ef | grep `cat phantom.pid` | grep -v "grep" >/dev/null 2>&1
	
	if [ $? -eq 1 ]; then
		echo "Process defined in phantom.pid is invalid."
		rm phantom.pid;
	fi
fi

if [ ! -f "phantom.pid" ]; then
	phantomjs --webdriver=127.0.0.1:8910 --webdriver-selenium-grid-hub=http://127.0.0.1:4444 &>/dev/null &

	echo "$!" > phantom.pid

	sleep 2s
fi

type pear >/dev/null 2>&1 || { echo >&2 "Pear is not installed."; exit 1; }

echo '<?php require "PHPUnit/Autoload.php"; require "PHPUnit/Extensions/Selenium2TestCase.php"; ?>' | php &>/dev/null

if [ ! $? -eq 0 ]; then
	pear install phpunit/PHPUnit_Selenium
fi

wget https://github.com/parisholley/vagrantpress/archive/master.zip
unzip master.zip
rm master.zip

cd vagrantpress-master
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

phpunit --no-configuration --verbose --bootstrap="selenium-tests/bootstrap.php" selenium-tests
