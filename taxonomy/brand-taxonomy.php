<?php

$invelity_feed_options = get_option('invelity_product_feeds_setting_values');

if ($invelity_feed_options['enabled_brand'] == 'on') {
  add_action('init', 'invelity_brand_taxonomy', 0);
}



function invelity_brand_taxonomy()
{

  $labels = array(
    'name' => _x('Brand', 'taxonomy general name'),
    'singular_name' => _x('Brand', 'taxonomy singular name'),
    'search_items' => __('Search Brands'),
    'popular_items' => __('Popular Brand'),
    'all_items' => __('All Brands'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __('Edit Brand'),
    'update_item' => __('Update Brand'),
    'add_new_item' => __('Add New Brand'),
    'new_item_name' => __('New Brand'),
    'separate_items_with_commas' => __('Separate Brands with commas'),
    'add_or_remove_items' => __('Add or remove Brand'),
    'choose_from_most_used' => __('Choose from the most used Brands'),
    'menu_name' => __('Brand'),
  );


  register_taxonomy('brand', 'product', array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => false,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array('slug' => 'brand'),
  ));
}


//////BRAND ////

add_filter('manage_edit-product_columns', 'add_invelity_brand_column', 20);
function add_invelity_brand_column($columns_array)
{

	// I want to display Brand column just after the product name column
  return array_slice($columns_array, 0, 11, true)
    + array('brand' => 'Brand')
    + array_slice($columns_array, 3, null, true);


}

add_action('manage_posts_custom_column', 'manage_invelity_brand_column');
function manage_invelity_brand_column($column_name)
{

  if ($column_name == 'brand') {
		// if you suppose to display multiple brands, use foreach();
    $x = get_the_terms(get_the_ID(), 'brand'); // taxonomy name
    if ($x) {
      echo '<span class="dashicons dashicons-yes"></span>';
    } else {
      echo '<span class="dashicons dashicons-no"></span>';
    }

  }

}