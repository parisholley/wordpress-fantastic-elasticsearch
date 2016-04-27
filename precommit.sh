VERSION=4\\.0\\.0

echo "-- Ensure git is in good state"

git submodule init
git submodule update

echo "-- Updating dependencies"

php composer.phar update

echo "-- Executing Tests"

tests/test.sh

if [ $? -eq 0 ]; then
	echo "-- Updating version numbers in files."

	grep -rl "@version" src/elasticsearch | xargs sed -i.bak "s/@version .*/@version $VERSION/g"
	sed -i.bak "s/Version .*/Version $VERSION/g" elasticsearch.php

	# mac require -i extension
	find . | grep .bak | xargs rm

	echo "-- Generating documentation."

	docker pull herloct/php-apigen

	docker run -v $(pwd):/app herloct/php-apigen generate -s /app/src/elasticsearch/ -d /app/docs

	echo "-- It is safe to commit! Make sure to commit the docs directory first."
else
	echo "-- THE TESTS FAILED, DO NOT COMMIT!"
fi

