<?php
namespace elasticsearch;

add_filter('nhp-opts-saved-text-elasticsearch', function($default){
	if(get_transient('es-wiped') == 1){
		return '<strong>The index has been wiped.</strong>';

		delete_transient('es-wiped');
	}

	if(get_transient('es-indexed') == 1){
		return '<strong>The index has been populated.</strong>';

		delete_transient('es-indexed');
	}

	return $default;
});

add_action('nhp-opts-options-validate-elasticsearch', function(){
	if($_POST['wipe']){
		try{
			Indexer::clear();
			set_transient('es-wiped', 1, 30);
		}catch(\Exception $ex){
			$errors = get_transient('nhp-opts-errors-elasticsearch');
			$errors[] = array(
				'section_id' => 'index'
			);

			set_transient('nhp-opts-errors-elasticsearch', $errors, 1000 );

			set_transient('es-wiped-error', $ex->getMessage(), 30);
		}
	}

	if($_POST['reindex']){
		try{
			Indexer::reindex();
			set_transient('es-indexed', 1, 30);
		}catch(\Exception $ex){
			$errors = get_transient('nhp-opts-errors-elasticsearch');
			$errors[] = array(
				'section_id' => 'index'
			);

			set_transient('nhp-opts-errors-elasticsearch', $errors, 1000 );

			set_transient('es-indexed-error', $ex->getMessage(), 30);
		}
	}
});

ob_start();
?>

<div style="font-size: 12px; font-style: normal">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row">
					Wipe Data
					<span class="description">Wipes all information from the ElasticSearch server. (This cannot be undone)</span>
				</th>
				<td>
					<input type="submit" name="wipe" class="button button-primary" value="Wipe Data" />
					<?php if($error = get_transient('es-wiped-error')): delete_transient('es-wiped-error'); ?>
						<span class="nhp-opts-th-error">There was a problem wiping the data. (<strong><?php echo $error; ?></strong>)</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					Re-index Data
					<span class="description">Re-populate index</span>
				</th>
				<td>
					<input type="submit" name="reindex" class="button button-primary" value="Re-index Data" />
					<?php if($error = get_transient('es-indexed-error')): delete_transient('es-indexed-error'); ?>
						<span class="nhp-opts-th-error">There was a problem indexing the data. (<strong><?php echo $error; ?></strong>)</span>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<?php
$html = ob_get_contents();
ob_end_clean();

$sections['index'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_319_sort.png',
	'title' => 'Manage Index',
	'desc' => $html
);
?>