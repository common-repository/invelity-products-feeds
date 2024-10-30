<?php

$invelity_feed_options = get_option('invelity_product_feeds_setting_values');

if ($invelity_feed_options['enabled_heureka'] == 'on') {
  add_action('init', 'invelity_heureka_taxonomy', 0);
}


function invelity_heureka_taxonomy()
{


  $labels = array(
    'name' => _x('Heureka Category', 'taxonomy general name'),
    'singular_name' => _x('Heureka Category', 'taxonomy singular name'),
    'search_items' => __('Search Heureka Categories'),
    'popular_items' => __('Popular Heureka Category'),
    'all_items' => __('All Heureka Categories'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __('Edit Heureka Category'),
    'update_item' => __('Update Heureka Category'),
    'add_new_item' => __('Add New Heureka Category'),
    'new_item_name' => __('New Heureka Category '),
    'separate_items_with_commas' => __('Separate Heureka Categories with commas'),
    'add_or_remove_items' => __('Add or remove Heureka Category'),
    'choose_from_most_used' => __('Choose from the most used Heureka Categories'),
    'menu_name' => __('Heureka Category'),
  );


  register_taxonomy('heureka_category', 'product', array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => false,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array('slug' => 'heureka-category'),
  ));
}


//////HEUREKA ////

add_filter('manage_edit-product_columns', 'add_invelity_heureka_column', 20);
function add_invelity_heureka_column($columns_array)
{

	// I want to display Brand column just after the product name column
  return array_slice($columns_array, 0, 11, true)
    + array('heureka-category' => 'Heureka')
    + array_slice($columns_array, 5, null, true);


}

add_action('manage_posts_custom_column', 'manage_invelity_heureka_column');
function manage_invelity_heureka_column($column_name)
{

  if ($column_name == 'heureka-category') {
		// if you suppose to display multiple brands, use foreach();
    $x = get_the_terms(get_the_ID(), 'heureka-category'); // taxonomy name
    if ($x) {
      echo '<span class="dashicons dashicons-yes"></span>';
    } else {
      echo '<span class="dashicons dashicons-no"></span>';
    }

  }

}