<?php

class InvelityFeedGoogle
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
      'id',
      'title',
      'description',
      'link',
      'image_link',
      'condition',
      'availability',
      'price',
      'brand',
      'google_product_category',
      'mpn',
      'identifier_exists',
      'shipping',
    ], "\t");

    if (is_array($products->productsList) || is_object($products->productsList)) {

      foreach ($products->productsList as $key) {

        $get_term_result = $this->setGoogleCats($key, $data);

        $invelity_google_category = $get_term_result ? $get_term_result : sanitize_text_field($data['google_cat']);

        if (isset($data['checkbox'])) {

          if ($data['checkbox'] == 'on') {

            if ($key['availability'] == 'in stock') {

              $csv_data = $this->productsForGoogle($key, $data, $invelity_google_category);

              fputcsv($csv, $csv_data, "\t");

            } else {
              continue;
            }
          } else {

            $csv_data = $this->productsForGoogle($key, $data, $invelity_google_category);
            fputcsv($csv, $csv_data, "\t");
          }
        } else {
          $csv_data = $this->productsForGoogle($key, $data, $invelity_google_category);
          fputcsv($csv, $csv_data, "\t");
        }

      }
    } else {
      $results = [

        'data' => $data,
        'message' => 'error',
      ];
      error_log(print_r($results, true));
    }

    fclose($csv);

    if (isset($_POST['filename']) && !empty($_POST['filename'])) {
      echo '<div class="notice notice-success">';
      echo '<h5>Feed was succesfully generated here ->';
      echo '<a href="' . $user_url . '/' . $dirName . '"><h3>' . $user_url . '/' . $dirName . '</h3></a>';
      echo '</h5>';
      echo '</div>';
      die();
    }

  }


  private function getBrandForGoogleFeed($key, $data)
  {
    if (!$key['brand']) {
      if (empty($data['feed_brand'])) {
        $brand_value = '';
      } else {
        $brand_value = sanitize_text_field($data['feed_brand']);
      }
    } else {
      $brand_value = $key['brand'];
    }

    return $brand_value;
  }

  private function getGoogleCatForGoogleFeed($key, $invelity_google_category)
  {

    if (empty($key['google_category'])) {
      $google_value = $invelity_google_category;
    } else {
      $google_value = $key['google_category'];
    }

    return $google_value;
  }

  private function productsForGoogle($key, $data, $invelity_google_category)
  {
    $extra_column = true;
    $brand_value = $this->getBrandForGoogleFeed($key, $data);
    $google_value = $this->getGoogleCatForGoogleFeed($key, $invelity_google_category);

    if (empty($brand_value)) {
      $extra_column = 'false';
    }

    $mpn = get_post_meta($key['id'], '_wpmr_mpn', true);

    if (empty($mpn)) {
      $extra_column = 'false';
    }

    $price = $key['sale_price'] ? $key['sale_price'] :$key['price'];
    $csv_data = [];
    $csv_data[] = $key['id'];
    $csv_data[] = $key['title'];
    $csv_data[] = clear_description($key['description']);
    $csv_data[] = $key['link'];
    $csv_data[] = $key['image'];
    $csv_data[] = $key['condition'];
    $csv_data[] = $key['availability'];
    $new_price = number_format((float)$price, 2, '.', '');
    $csv_data[] = $new_price . " " . get_option('woocommerce_currency');
    $csv_data[] = $brand_value;
    $csv_data[] = htmlspecialchars_decode($google_value);
    $csv_data[] = $mpn;
    $csv_data[] = $extra_column;
    $country = wc_get_base_location();

    if ($data['shipping']) {
      $csv_data[] = $country['country'] . ':::' . $data['shipping'] . ' ' . get_option('woocommerce_currency');
    }

      /*
      if ( $key['variation_type'] == 'parent' ) {
            $variable_product = new WC_Product_Variable( $key['id'] );
            $variations       = $variable_product->get_children();

              if ( $variations ) {
                foreach ( $variations as $variation ) {

                $variable_product = new WC_Product_Variation( $variation );
                $csv_data[] = $variation;
                $csv_data[] = $key['title'];
                $csv_data[] = clear_description($key['description']);
                $csv_data[] =  htmlspecialchars($key['link']);
                $csv_data[] =  htmlspecialchars($key['image']);
                $csv_data[] = $key['condition'];
                $csv_data[] = $key['availability'];
                $new_price = number_format((float)$key['price'], 2, '.', '');
                $csv_data[] = $new_price . " ". get_option('woocommerce_currency');
                $csv_data[] = $brand_value;
                $csv_data[] = $google_value;
                $csv_data[] = get_post_meta($key['id'],'_wpmr_mpn',true);
                $country =wc_get_base_location();
                $csv_data[] = $country['country']  .':::'. $data['shipping'] .' '. get_option('woocommerce_currency') ;
          }
        }
    } */

    return $csv_data;

  }

  private function setGoogleCats($key, $data)
  {

    $categories_details = $key['category_ids'];
    $cat_id = '';

    if (is_array($categories_details)) {
      foreach ($categories_details as $cat_details) {

        if (is_array($data['product_cat'])) {
          if (in_array(strval($cat_details->term_id), $data['product_cat'])) {
            $cat_id = $cat_details->term_id;
          }
        }

      }
    } else {
      $cat_id = $categories_details['term_id'];
    }

    $get_term = get_term_meta(
      $cat_id,
      'invelity_google_category',
      true
    );

    if (is_array($get_term)) {
      foreach ($get_term as $term) {
        $get_term_result = $term;
      }
    } else {
      $get_term_result = $get_term;
    }

    return $get_term_result;

  }



}
