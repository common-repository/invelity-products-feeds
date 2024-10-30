<?php


function custom_invelity_feed_taxonomy_add_new_meta_field()
{
  ?>

		<div class="form-field">
			<label for="custom_invelity_feed_meta_title">Heureka Category</label>
			<input type="text" name="invelity_heureka_category" id="invelity_heureka_category">
			<p class="description">Enter Heureka Category</p>
		</div>
		<div class="form-field">
			<label for="custom_invelity_feed_meta_desc">Google Category</label>
			<input type="text" name="invelity_google_category" id="invelity_google_category">
			<p class="description">Enter Google Category</p>
		</div>
		<?php

}

//Product Cat Edit page
function custom_invelity_feed_taxonomy_edit_meta_field($term)
{

		//getting term ID
  $term_id = $term->term_id;

		// retrieve the existing value(s) for this meta field.
  $invelity_heureka_category = get_term_meta($term_id, 'invelity_heureka_category', true);
  $invelity_google_category = get_term_meta($term_id, 'invelity_google_category', true);
  ?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="invelity_heureka_category">Heureka Category</label></th>
			<td>
				<input type="text" name="invelity_heureka_category" id="invelity_heureka_category"
					   value="<?php echo esc_attr($invelity_heureka_category) ? esc_attr($invelity_heureka_category) : ''; ?>">
				<p class="description">Enter Heureka Category</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="invelity_google_category">Google Category</label></th>
			<td>
				<input type="text" name="invelity_google_category" id="invelity_google_category"
					   value="<?php echo esc_attr($invelity_google_category) ? esc_attr($invelity_google_category) : ''; ?>">
				<p class="description">Enter Google Category</p>
			</td>
		</tr>
		<?php

}


// Save extra taxonomy fields callback function.
function custom_invelity_feed_save_taxonomy_custom_meta($term_id)
{

  $invelity_heureka_category = filter_input(INPUT_POST, 'invelity_heureka_category');
  $invelity_google_category = filter_input(INPUT_POST, 'invelity_google_category');

  update_term_meta($term_id, 'invelity_heureka_category', $invelity_heureka_category);
  update_term_meta($term_id, 'invelity_google_category', $invelity_google_category);
}

