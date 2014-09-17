export ARGS=""
cd `dirname $0`

echo "Checking for XDebug.";

echo "<?php phpinfo(); ?>" | php | grep "xdebug support => enabled" >/dev/null 2>&1
XDEBUG=$?

if [ $XDEBUG -eq 1 ]; then
	echo "Cannot generate code coverage because Xdebug is not installed.";
else
	ARGS="--coverage-html ./report"
fi

if [ ! -d "work" ]; then
	mkdir "work"
fi

cd work

../version.sh "1.3.0"

PASSED=$?

if [ $PASSED -eq 0 ]; then
	if [ $XDEBUG -eq 0 ]; then
        echo "Opening Coverage Report."
        open report/index.html
    fi

    exit 0
fi

exit 1
