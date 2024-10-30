<?php

class InvelityFeedDynamicSearchAds
{

  public $data;

  public function __construct($data)
  {
    add_filter('w3tc_can_print_comment', '__return_false', 10, 1);
    $this->generateProductsToFile($data);
  }

  private function generateProductsToFile($data)
  {
    $products = new InvelityGenerateProductLoop($data);
    $upload_dir = wp_upload_dir();
    $user_url = $upload_dir['baseurl'] . '/product-feeds';
    $user_dirname = $upload_dir['basedir'];
    $data['filename'] = preg_replace('#[^a-z0-9_]#', "", sanitize_text_field($data['filename']));
    $dirName = $data['filename'] . '.csv';
    $feed_dir = $user_dirname . '/product-feeds';

    if (!file_exists($user_dirname . '/product-feeds')) {
      mkdir($user_dirname . '/product-feeds');
    }

    $csv = fopen($feed_dir . '/' . $dirName, 'w');
    fputcsv($csv, [
      'Page URL',
      'Custom label',
    ], "\t");

    if (is_array($products->productsList) || is_object($products->productsList)) {
      foreach ($products->productsList as $key) {
        if (isset($data['checkbox'])) {

          if ($data['checkbox'] == 'on') {

            if ($key['availability'] == 'in stock') {
              $csv_data = $this->getProductsForDSA($key, $data);
              fputcsv($csv, $csv_data, "\t");

            } else {
              continue;
            }

          } else {
            $csv_data = $this->getProductsForDSA($key, $data);
            fputcsv($csv, $csv_data, "\t");
          }

        } else {
          $csv_data = $this->getProductsForDSA($key, $data);
          fputcsv($csv, $csv_data, "\t");
        }

      }

      fclose($csv);

    } else {
      $results = [
        'data' => $data,
        'message' => 'error',
      ];
      error_log(print_r($results, true));
    }

    if (isset($_POST['filename']) && !empty($_POST['filename'])) {
      echo '<div class="notice notice-success">';
      echo '<h5>Feed was succesfully generated here ->';
      echo '<a href="' . $user_url . '/' . $dirName . '"><h3>' . $user_url . '/' . $dirName . '</h3></a>';
      echo '</h5>';
      echo '</div>';
      die();

    }
  }

  private function getProductsForDSA($key, $data)
  {

    $customLabel = '';
    if ($data['custom_label'] == 'cat') {
      $customLabel = $key['product_type'];
    } elseif ($data['custom_label'] == 'availability') {
      $customLabel = $key['availability'];
    } elseif ($data['custom_label'] == 'id') {
      $customLabel = $key['id'];
    } elseif ($data['custom_label'] == 'title') {
      $customLabel = $key['title'];
    } elseif ($data['custom_label'] == 'description') {
      $customLabel = clear_description($key['description']);
    } elseif ($data['custom_label'] == 'image_link') {
      $customLabel = $key['image'];
    } elseif ($data['custom_label'] == 'condition') {
      $customLabel = $key['condition'];
    } elseif ($data['custom_label'] == 'type') {
      $customLabel = $key['type'];
    } elseif ($data['custom_label'] == 'quantity') {
      $customLabel = $key['quantity'];
    } elseif ($data['custom_label'] == 'sale_price') {
      $customLabel = $key['sale_price'];
    } elseif ($data['custom_label'] == 'price') {
      $customLabel = $key['price'];
    } elseif ($data['custom_label'] == 'price') {
      $customLabel = $key['price'];
    }

    $csv_data = [];
    $csv_data[] = $key['link'];
    $csv_data[] = $customLabel;
    return $csv_data;
  }



}