PLUGIN_DIR="tests/work/vagrantpress-wordpress-fantastic-elasticsearch/wordpress/wp-content/plugins/wordpress-fantastic-elasticsearch"

echo "Copying plugin edited plugin from VM's wordpress plugin folder."

mkdir $PLUGIN_DIR
cp -rf $PLUGIN_DIR/elasticsearch.php .
cp -rf $PLUGIN_DIR/src/* src/
cp -rf $PLUGIN_DIR/wp/* wp/