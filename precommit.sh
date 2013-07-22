VERSION=2\\.0\\.0

echo "-- Updating version numbers in files."

grep -rl "@version" src/elasticsearch | xargs sed -i.bak "s/@version .*/@version $VERSION/g"
sed -i.bak "s/Version .*/Version $VERSION/g" elasticsearch.php

# mac require -i extension
find . | grep .bak | xargs rm

echo "-- Generating documentation."

type apigen >/dev/null 2>&1 || { echo >&2 "ApiGen is not installed."; exit 1; }

apigen --source=src/elasticsearch/ --destination=docs/api

