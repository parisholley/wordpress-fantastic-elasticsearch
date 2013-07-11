VERSION="0.90.2"
ARGS=""

echo "Checking for XDebug.";

echo "<?php phpinfo(); ?>" | php | grep "xdebug support => enabled" >/dev/null 2>&1
XDEBUG=$?

if [ $XDEBUG -eq 1 ]; then
	echo "Cannot generate code coverage because Xdebug is not installed.";
else
	ARGS="--coverage-html ./report"
fi

if [ ! -d "elasticsearch-$VERSION" ]; then
	echo "Downloading ElasticSearch."
	wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-$VERSION.tar.gz;
	tar -xvf elasticsearch-$VERSION.tar.gz;
	rm elasticsearch-$VERSION.tar.gz;
fi

if [ -f "es.pid" ]; then
	ps -ef | grep `cat es.pid` | grep -v "grep" >/dev/null 2>&1
	
	if [ $? -eq 1 ]; then
		echo "Process defined in es.pid is invalid."
		rm es.pid;
	fi
fi

if [ ! -f "es.pid" ]; then
	echo "Launching ElasticSearch."
	elasticsearch-$VERSION/bin/elasticsearch -p es.pid

	echo "Waiting for ElasticSearch to launch."
	sleep 5
fi

echo "Starting PHPUnit Tests."
phpunit $ARGS

if [ "$1" == "kill" ]; then
	echo "Killing ElasticSearch instance"
	kill -9 `cat es.pid`
	rm es.pid
else
	echo "To kill ElasticSearch when running tests, use './test.sh kill'"
fi

if [ $XDEBUG -eq 0 ]; then
	echo "Opening Coverage Report."
	open report/index.html
fi
