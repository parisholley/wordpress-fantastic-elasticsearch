VERSION=2\\.0\\.0

echo "-- Executing Tests"

tests/test.sh

if [ $? -eq 0 ]; then
	echo "-- Updating version numbers in files."

	grep -rl "@version" src/elasticsearch | xargs sed -i.bak "s/@version .*/@version $VERSION/g"
	sed -i.bak "s/Version .*/Version $VERSION/g" elasticsearch.php

	# mac require -i extension
	find . | grep .bak | xargs rm

	echo "-- Generating documentation."

	type apigen >/dev/null 2>&1 || { echo >&2 "ApiGen is not installed."; exit 1; }

	apigen --source=src/elasticsearch/ --destination=docs/api

	echo "-- It is safe to commit!"
else
	echo "-- THE TESTS FAILED, DO NOT COMMIT!"
fi

