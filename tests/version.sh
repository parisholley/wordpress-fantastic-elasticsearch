if [ ! -d "elasticsearch-$1" ]; then
	echo "Downloading ElasticSearch $1."
	wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-$1.tar.gz;
	tar -xvf elasticsearch-$1.tar.gz;
	rm elasticsearch-$1.tar.gz;
fi

if [ -f "es.pid" ]; then
	ps -ef | grep `cat es.pid` | grep -v "grep" >/dev/null 2>&1
	
	if [ $? -eq 1 ]; then
		echo "Process defined in es.pid is invalid."
		rm es.pid;
	fi
fi

if [ ! -f "es.pid" ]; then
	echo "Launching ElasticSearch $1."
	elasticsearch-$1/bin/elasticsearch -p es.pid >/dev/null &

	echo "Waiting for ElasticSearch to launch."
	sleep 10
fi

echo "Starting PHPUnit Tests."
phpunit --configuration=../../phpunit.xml $ARGS

PASSED=$?

echo "Killing ElasticSearch $1 instance"
kill -9 `cat es.pid`
rm es.pid

exit $PASSED
