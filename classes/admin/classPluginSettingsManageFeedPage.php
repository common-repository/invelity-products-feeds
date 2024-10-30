<?php

class PluginSettigsManageFeedPage
{

  public function __construct($launcher)
  {
    //var_dump('PluginSettigsManageFeedPage');

    $this->createManageFeedPage();
  }

  public function createManageFeedPage()
  {

    ?>
		<style>
			body {
				background: #f1f1f1;
			}
		</style>
		<h1>List of Feeds</h1>
		<div class="wrapp invelity-products-feeds-manage">

			<div class="wp-feed-box">
				<table class="wp-list-table widefat fixed">
					<thead>
					<th>File name</th>
					<th>Provider</th>
          <th>Description</th>
        <?php if (function_exists('icl_object_id')) {
          echo '<th>Language</th>';
        } ?>

					<th>Date</th>
					<th>Last Update</th
					<th>Feed URL</th>

					<th></th>
					</thead>
					<tbody class="the-list-feed">
					<?php

    $upload_dir = wp_upload_dir();
    $user_dirname = $upload_dir['basedir'];
    $feed_dir = $user_dirname . '/product-feeds';
    $url = $upload_dir['baseurl'] . '/product-feeds';
    $admiUrl = admin_url('admin.php?page=invelity-products-manage-feeds');

    if (isset($_GET['name'])) {
      $name = $_GET['name'];
      $expl = explode('.', $name);

      global $wpdb;
      $wpdb->delete(
        $wpdb->options,
        array('option_name' => 'wf_config_' . $expl[0]),
        array('%s')
      );
      unlink($feed_dir . '/' . $name);
      echo '<div class="updated notice">';
      echo '<h3>' . $name . ' succesfully deleted </h3>';
      echo '</div> <br><br>';

    }


    global $wpdb;
    $var = "wf_config_";
    $query = $wpdb->prepare(
      "SELECT * FROM $wpdb->options WHERE option_name LIKE %s;",
      $var . "%"
    );
    $result = $wpdb->get_results($query, 'ARRAY_A');
    foreach ($result as $key => $value) {
      $data = unserialize($value['option_value']);

      $provider = $data['provider'];

      if ($provider == 'Heureka') {
        $filename = $data['filename'] . '.xml';
      } else {
        $filename = $data['filename'] . '.csv';
      }

      if (file_exists($feed_dir . '/' . $filename)) {

        echo '<tr><td>' . $filename . '</td>';
        echo '<td>' . $provider . '</td>';
        if (isset($data['feed_description'])) {
          echo '<td>' . $data['feed_description'] . '</td>';
        } else {
          echo '<td></td>';
        }
        if (function_exists('icl_object_id')) {
          if (isset($data['language'])) {
            echo '<td>' . strtoupper($data['language']) . '</td>';
          } else {
            echo '<td></td>';
          }
        }
        if (isset($data['date'])) {
          echo '<td>' . $data['date'] . '</td>';
        } else {
          echo '<td></td>';
        }

        if (isset($data['last_cron'])) {
          echo '<td>' . $data['last_cron'] . '</td>';
        } else {
          echo '<td></td>';
        }

        echo '<td><a href ="' . $url . '/' . $filename . '">' . $url . '/' . $filename . '</a><br />';
        echo '</td>';
        echo '<td><a href="' . $admiUrl . '&name=' . $filename . '" id="' . $filename . '" class="delete_feed"><span class="dashicons dashicons-trash"></span></a></td></tr>';

      } else {

        $wpdb->delete(
          $wpdb->options,
          array('option_name' => 'wf_config_' . $data['filename']),
          array('%s')
        );
      }
    }

    ?>
					</tbody>
				</table>

			</div>
			<div>
				<?php
    $adData = InvelityFeedPluginSettings::getRemoteAd();
    if ($adData) {
      ?>
					<a href="<?= $adData['adDestination'] ?>" target="_blank">
						<img src="<?= $adData['adImage'] ?>">
					</a>
					<?php

  }
  ?>
			</div>
		</div>


		<?php

}



}

