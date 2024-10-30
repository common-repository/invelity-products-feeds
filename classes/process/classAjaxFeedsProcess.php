<?php

class AjaxFeedsProcess
{


  public function __construct()
  {

    $this->wp_feed_init_ajax_products();
    $this->wp_feed_init_ajax_categories();


  }


  public function wp_feed_init_ajax_products()
  {

    add_action('wp_ajax_my_search', array($this, 'invelity_ajax_products'));
    add_action('wp_ajax_nopriv_my_search', array($this, 'invelity_ajax_products'));

  }


  public function invelity_ajax_products()
  {

    $term = $_REQUEST['q'];

    global $wpdb;
    $products = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}posts WHERE post_title LIKE '%%$term%%' AND  post_type = 'product' ");

    $pro = [];
    foreach ($products as $product) {

      $pro[] = array(
        'id' => $product->ID,
        'name' => $product->post_title
      );
    }
    $response = json_encode($pro);
    echo $response;
    exit();

  }


  public function wp_feed_init_ajax_categories()
  {

    add_action('wp_ajax_change_language', array($this, 'invelity_change_language'));
    add_action('wp_ajax_nopriv_change_language', array($this, 'invelity_change_language'));


  }


  public function invelity_change_language()
  {

    if (function_exists('icl_object_id')) {

   // generate all categories by lang
    //send response to js
      $currentLanguage = apply_filters('wpml_current_language', null);
  //  var_dump($currentLanguage);

      global $sitepress;

      if (isset($_GET['language'])) {
        $feedLang = $_GET['lang'];
      } else {
        $feedLang = $currentLanguage;
      }

      $sitepress->switch_lang($feedLang, true);
   // var_dump($currentLanguage);
    }

    $args = array('type' => 'product', 'taxonomy' => 'product_cat');
    $categories = get_categories($args);
            //$cats       = [];
    $category = [];
    foreach ($categories as $cat) {
   // echo '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
      $category[] = array(
        'id' => $cat->term_id,
        'name' => $cat->name,
      );
    }
    $response = json_encode($category);
    echo $response;
    exit();


  }


/*
function invelity_ajax_categories()
{

  $term = $_REQUEST['q'];


  global $wpdb;
  $categories = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}terms WHERE name LIKE '%%$term%%' AND term_id in( SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy WHERE taxonomy= 'product_cat') ");

  $pro = [];
  foreach ($categories as $category) {

    $pro[] = array(
      'id' => $category->term_id,
      'name' => $category->name
    );
  }
  $response = json_encode($pro);
  echo $response;
  exit();

} */



}

new AjaxFeedsProcess();