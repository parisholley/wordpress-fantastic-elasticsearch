VERSION="0.90.2"
ARGS=""

echo "Checking for XDebug.";

echo "<?php phpinfo(); ?>" | php | grep "xdebug support => enabled" >/dev/null 2>&1

if [ $? -eq 1 ]; then
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

if [ ! -f "es.pid" ]; then
	echo "Launching ElasticSearch."
	elasticsearch-$VERSION/bin/elasticsearch -p es.pid

	echo "Waiting for ElasticSearch to launch."
	sleep 5
fi

echo "Starting PHPUnit Tests."
phpunit $ARGS

echo "Killing ElasticSearch instance"
kill -9 `cat es.pid`
rm es.pid
