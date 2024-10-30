<?php

if ($invelity_feed_options['enabled_google'] == 'on') {
  add_action('init', 'invelity_google_category_taxonomy', 0);
}


function invelity_google_category_taxonomy()
{

  $labels = array(
    'name' => _x('Google Category', 'taxonomy general name'),
    'singular_name' => _x('Google Category', 'taxonomy singular name'),
    'search_items' => __('Search Google Category'),
    'popular_items' => __('Popular Google Category'),
    'all_items' => __('All Google Categories'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __('Edit Google Category'),
    'update_item' => __('Update Google Category'),
    'add_new_item' => __('Add New Google Category'),
    'new_item_name' => __('New Google Category'),
    'separate_items_with_commas' => __('Separate Google Categories with commas'),
    'add_or_remove_items' => __('Add or remove Google Categories'),
    'choose_from_most_used' => __('Choose from the most used Google Categories'),
    'menu_name' => __('Google Category'),
  );


  register_taxonomy('google_category', 'product', array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => false,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array('slug' => 'google_category'),
  ));
}

////google//

add_filter('manage_edit-product_columns', 'add_invelity_google_column', 20);
function add_invelity_google_column($columns_array)
{

	// I want to display Brand column just after the product name column
  return array_slice($columns_array, 0, 11, true)
    + array('google_category' => 'Google')
    + array_slice($columns_array, 3, null, true);


}

add_action('manage_posts_custom_column', 'manage_invelity_google_column');
function manage_invelity_google_column($column_name)
{

  if ($column_name == 'google_category') {
		// if you suppose to display multiple brands, use foreach();
    $x = get_the_terms(get_the_ID(), 'google_category'); // taxonomy name
    if ($x) {
      echo '<span class="dashicons dashicons-yes"></span>';
    } else {
      echo '<span class="dashicons dashicons-no"></span>';
    }

  }

}