PLUGIN_DIR="tests/work/vagrantpress-wordpress-fantastic-elasticsearch/wordpress/wp-content/plugins/wordpress-fantastic-elasticsearch"

echo "Copying plugin to VM wordpress"

rm -rf $PLUGIN_DIR
mkdir $PLUGIN_DIR
cp -rf elasticsearch.php $PLUGIN_DIR
cp -rf src $PLUGIN_DIR
cp -rf vendor $PLUGIN_DIR
cp -rf wp $PLUGIN_DIR
