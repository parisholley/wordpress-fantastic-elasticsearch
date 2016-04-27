docker pull elasticsearch:2.3

docker ps | grep fantastices >/dev/null 2>&1

if [ $? -eq 1 ]; then
    EXISTING=`docker ps -a | grep fantastices | awk '{print $1}'`

    if [ -z "$EXISTING" ]; then
        echo "Creating ElasticSearch Container"
        docker run -d --name fantastices -p 127.0.0.1:9200:9200 elasticsearch
    else
        echo "Restarting ElasticSearch Container"

        docker start $EXISTING
    fi

    sleep 15
else
    echo "ElasticSearch already running"
fi

docker run --link fantastices -v $(pwd):/app phpunit/phpunit --configuration=phpunit.xml --coverage-html ./report