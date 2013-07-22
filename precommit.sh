VERSION=2\\.0\\.0

echo "-- Ensure git is in good state"

git submodule init
git submodule update

echo "-- Updating dependencies"

composer update

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

	apigen --source=src/elasticsearch/ --destination=docs

	echo "-- It is safe to commit! Make sure to commit the docs directory first."
else
	echo "-- THE TESTS FAILED, DO NOT COMMIT!"
fi

