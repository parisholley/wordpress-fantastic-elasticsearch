SUSPEND=0

if [ -f "$HOME/.fes.sh" ]; then
	source $HOME/.fes.sh
fi

cd `dirname $0`

echo "Detecting if phantomjs is installed."

type phantomjs --help >/dev/null 2>&1 

if [ ! $? -eq 0 ]; then
	echo "Installing phantomjs."

	brew update && brew install phantomjs
fi

if [ ! -d "work" ]; then
	mkdir "work"
fi

cd work

if [ ! -f "selenium.jar" ]; then
	echo "Downloading selenium."
	wget http://selenium-release.storage.googleapis.com/2.43/selenium-server-standalone-2.43.1.jar -O selenium.jar
fi

if [ -f "selenium.pid" ]; then
	kill -9 `cat selenium.pid` &>/dev/null

	rm selenium.pid
fi

if [ -f "selenium.log" ]; then
	rm selenium.log
fi

ps -ef | grep "java -jar selenium-server" | grep -v grep &>/dev/null

if [ $? -eq 0 ]; then
	echo "WARNING: A version of selenium is already running."
fi

echo -n "Starting selenium service."

java -jar selenium.jar -role hub &> selenium.log &

echo "$!" > selenium.pid

REGISTERED=0

while [ $REGISTERED -eq 0 ]; do
	grep 'Started SocketConnector@0.0.0.0:4444' selenium.log &>/dev/null

	if [ $? -eq 0 ]; then
		echo -ne "(Complete)\n"
		REGISTERED=1
	else
		echo -n "."
		sleep 1s

		ps -ef | grep "selenium.jar" | grep -v grep  &>/dev/null

		if [ $? -eq 1 ]; then
			echo "ERROR: selenium died"
			exit 1
		fi
	fi
done

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

echo -n "Waiting for phantomjs to register with grid."

REGISTERED=0

while [ $REGISTERED -eq 0 ]; do
	curl -XGET http://localhost:4444/grid/api/proxy/?id=http://127.0.0.1:8910 2>/dev/null | grep 'registerCycle' &>/dev/null

	if [ $? -eq 0 ]; then
		echo -ne "(Complete)\n"
		REGISTERED=1
	else
		echo -n "."
		sleep 1s

		ps -ef | grep "phantomjs" | grep -v grep  &>/dev/null

		if [ $? -eq 1 ]; then
			echo "ERROR: phantomjs died"
			exit 1
		fi
	fi
done

echo "Detecting phpunit/PHPUnit_Selenium pear extension is installed."

type pear >/dev/null 2>&1 || {
	echo "Installing PEAR"

	curl -O http://pear.php.net/go-pear.phar
	sudo php -d detect_unicode=0 go-pear.phar
}

#Need a way to detect this
sudo pear channel-discover pear.phpunit.de
sudo pear install phpunit/PHPUnit_Selenium

if [ ! -d "vagrantpress-wordpress-fantastic-elasticsearch" ]; then
	echo "Downloading vagrant information."
	wget https://github.com/parisholley/vagrantpress/archive/wordpress-fantastic-elasticsearch.zip
	unzip wordpress-fantastic-elasticsearch.zip
	rm wordpress-fantastic-elasticsearch.zip
fi

cd vagrantpress-wordpress-fantastic-elasticsearch

echo "Waiting for VM to launch."

vagrant up &>/dev/null

echo "Restoring database incase it was modified."

vagrant ssh -c "mysql -uwordpress -pwordpress wordpress < /vagrant/puppet/modules/wordpress/files/wordpress-db.sql" &>/dev/null

PLUGIN_DIR="wordpress/wp-content/plugins/wordpress-fantastic-elasticsearch"

if [ -d $PLUGIN_DIR ]; then
	rm -rf $PLUGIN_DIR
fi

echo "Copying plugin into VM's wordpress plugin folder."

mkdir $PLUGIN_DIR
cp -rf ../../../elasticsearch.php $PLUGIN_DIR/
cp -rf ../../../src/ $PLUGIN_DIR/src
cp -rf ../../../vendor/ $PLUGIN_DIR/vendor
cp -rf ../../../wp/ $PLUGIN_DIR/wp

cd ..

echo "Running tests."

phpunit --no-configuration --verbose --bootstrap="../selenium-tests/bootstrap.php" ../selenium-tests;

if [ -f "phantom.pid" ]; then
	kill -9 `cat phantom.pid`
	
	rm phantom.pid;
fi

if [ -f "selenium.pid" ]; then
	kill -9 `cat selenium.pid`

	rm selenium.pid;
fi

if [ -f "selenium.log" ]; then
	rm selenium.log
fi

if [ $? -eq 0 ] && [ $SUSPEND -eq 1 ]; then
	echo "Suspending VM."

	cd vagrantpress-wordpress-fantastic-elasticsearch;

	vagrant suspend
fi
