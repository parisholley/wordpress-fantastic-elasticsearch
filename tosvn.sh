CURRENTDIR=`pwd`
SVNPATH="/tmp/elasticsearch"
SVNURL="http://plugins.svn.wordpress.org/fantastic-elasticsearch"
README=`grep "^Stable tag:" README.txt | awk -F' ' '{print $NF}'`
PHP=`grep "Version:" elasticsearch.php | awk -F' ' '{print $NF}'`

if [ "$README" != "$PHP" ]; then echo "Version in README.txt & elasticsearch.php don't match. Exiting...."; exit 1; fi

grep "= $PHP =" README.txt >/dev/null 2>&1

if [ ! $? -eq 0 ]; then
	echo "Forgot to add revision log entry"
fi

if [ ! -d $SVNPATH ]; then
	echo "Checking out plugin"
	svn co --non-recursive $SVNURL $SVNPATH

	cd $SVNPATH
	svn update trunk/
	svn update --depth=immediates tags/

	cd $CURRENTDIR

	echo "Exporting the HEAD of master from git to the trunk of SVN"
	git checkout-index -a -f --prefix=$SVNPATH/trunk/
fi

cd $SVNPATH

svn status | grep -v "^.[ \t]*\..*" | grep "^?" >/dev/null 2>&1

if [ $? -eq 0 ]; then
	echo "ERROR: There are new files in the SVN repository. Resolve then try again."
fi

cd trunk

svn commit -m "Preparing for $PHP release"

if [ $? -eq 0 ]; then
	echo "Creating new SVN tag and committing it"
	
	svn copy $SVNURL/trunk/ $SVNURL/tags/$PHP/ -m "Tagging version $PHP"
fi

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/