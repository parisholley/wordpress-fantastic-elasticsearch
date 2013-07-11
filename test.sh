export ARGS=""

echo "Checking for XDebug.";

echo "<?php phpinfo(); ?>" | php | grep "xdebug support => enabled" >/dev/null 2>&1
XDEBUG=$?

if [ $XDEBUG -eq 1 ]; then
	echo "Cannot generate code coverage because Xdebug is not installed.";
else
	ARGS="--coverage-html ./report"
fi

./version.sh "0.20.6"

PASSED=$?

if [ $PASSED -eq 0 ]; then
	./version.sh "0.90.2"

	PASSED=$?

	if [ $PASSED -eq 0 ]; then
		if [ $XDEBUG -eq 0 ]; then
			echo "Opening Coverage Report."
			open report/index.html
		fi
	fi
fi