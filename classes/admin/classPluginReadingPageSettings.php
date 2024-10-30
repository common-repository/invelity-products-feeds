<?php

class PluginReadingPageSettings
{

  public function __construct($launcher)
  {
    //var_dump('PLUGIN_READING_PAGE_SETTINGS');

    add_action('admin_init', array($this, 'add_custom_setting_page_feed'));
  }

	// ADD SETTINGS TO READING PAGE IN ADMIN

  public function add_custom_setting_page_feed()
  {
   // var_dump('bu');
    add_settings_section(
      'feed_custom_setting_section',
      'Invelity Product Feeds',
      array($this, 'feed_custom_setting_section'),
      'reading'
    );

		//HEUREKA
    add_settings_field(
      'feed_custom_setting_enable_taxonomy_heureka',
      'Enable Using Heureka Categories',
      array($this, 'feed_custom_setting_enabled_heureka'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_enable_custom_taxonomy_heureka',
      'Use your Heureka Category',
      array($this, 'feed_custom_setting_enable_custom_taxonomy_heureka'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_taxonomy_heureka',
      'Select Heureka Category',
      array($this, 'feed_custom_setting_taxonomy_heureka'),
      'reading',
      'feed_custom_setting_section'
    );

		//BRAND
    add_settings_field(
      'feed_custom_setting_enable_taxonomy_brand',
      'Enable Using Brand Categories',
      array($this, 'feed_custom_setting_enabled_brand'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_enable_custom_taxonomy_brand',
      'Use your Brand Category',
      array($this, 'feed_custom_setting_enable_custom_taxonomy_brand'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_taxonomy_brand',
      'Select Brand Category',
      array($this, 'feed_custom_setting_taxonomy_brand'),
      'reading',
      'feed_custom_setting_section'
    );
		//GOOOGLE
    add_settings_field(
      'feed_custom_setting_enable_taxonomy_google',
      'Enable Using Google Categories',
      array($this, 'feed_custom_setting_enabled_google'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_enabled_custom_taxonomy_google',
      'Use your Google Category',
      array($this, 'feed_custom_setting_enabled_custom_taxonomy_google'),
      'reading',
      'feed_custom_setting_section'
    );
    add_settings_field(
      'feed_custom_setting_taxonomy_google',
      'Select Google Category',
      array($this, 'feed_custom_setting_taxonomy_google'),
      'reading',
      'feed_custom_setting_section'
    );

    register_setting(
      'reading',
      'invelity_product_feeds_setting_values',
      array($this, 'feed_custom_sanitize_settings')
    );
  }

  public function feed_custom_sanitize_settings($input)
  {

    $input['enabled_heureka'] = ($input['enabled_heureka'] == 'on') ? 'on' : '';
    $input['use_own_heureka'] = ($input['use_own_heureka'] == 'on') ? 'on' : '';

    $input['enabled_brand'] = ($input['enabled_brand'] == 'on') ? 'on' : '';
    $input['use_own_brand'] = ($input['use_own_brand'] == 'on') ? 'on' : '';

    $input['enabled_google'] = ($input['enabled_google'] == 'on') ? 'on' : '';
    $input['use_own_google'] = ($input['use_own_google'] == 'on') ? 'on' : '';


    return $input;
  }

  public function feed_custom_setting_section()
  {
    echo '<p>Configurate the Invelity Product Feeds plugin </p>';
  }

//////////////////////HEUREKA
  public function feed_custom_setting_enabled_heureka()
  {
    $invelity_feed_options_heureka = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_heureka['enabled_heureka'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[enabled_heureka]" type="checkbox"/>';
  }

  public function feed_custom_setting_enable_custom_taxonomy_heureka()
  {
    $invelity_feed_options_heureka = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_heureka['use_own_heureka'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[use_own_heureka]" type="checkbox"/>';
  }

  public function feed_custom_setting_taxonomy_heureka()
  {
    $invelity_feed_options_heureka = get_option('invelity_product_feeds_setting_values');

    $args = array(
      'public' => true,
      '_builtin' => false

    );
    $output = 'objects'; // or names
    $taxonomies = get_taxonomies($args, $output);

    if ($invelity_feed_options_heureka['use_own_heureka'] == 'on') {
      echo '<select name="invelity_product_feeds_setting_values[use_this_heureka_taxonomy]">';

      foreach ($taxonomies as $taxonomy) {
        if ($invelity_feed_options_heureka['use_this_heureka_taxonomy'] == $taxonomy->name) {
          echo '<option selected value="' . $invelity_feed_options_heureka['use_this_heureka_taxonomy'] . '" >' . $invelity_feed_options_heureka['use_this_heureka_taxonomy'] . '</option>';
        } else {
          echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';

        }

      }
      echo '</select>';

    } else {
      echo '<select disabled>';
      foreach ($taxonomies as $taxonomy) {
        echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';
      }
      echo '</select>';
    }

  }

	//////////////////////BRAND
  public function feed_custom_setting_enabled_brand()
  {

    $invelity_feed_options_brand = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_brand['enabled_brand'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[enabled_brand]" type="checkbox"/> ';
  }

  public function feed_custom_setting_enable_custom_taxonomy_brand()
  {
    $invelity_feed_options_brand = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_brand['use_own_brand'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[use_own_brand]" type="checkbox"/> ';
  }


  public function feed_custom_setting_taxonomy_brand()
  {
    $invelity_feed_options = get_option('invelity_product_feeds_setting_values');

    $args = array(
      'public' => true,
      '_builtin' => false

    );
    $output = 'objects'; // or names
    $taxonomies = get_taxonomies($args, $output);

    if ($invelity_feed_options['use_own_brand'] == 'on') {
      echo '<select name="invelity_product_feeds_setting_values[use_this_brand_taxonomy]">';

      foreach ($taxonomies as $taxonomy) {

        if ($invelity_feed_options['use_this_brand_taxonomy'] == $taxonomy->name) {
          echo '<option selected="selected" value="' . $invelity_feed_options['use_this_brand_taxonomy'] . '" >' . $invelity_feed_options['use_this_brand_taxonomy'] . '</option>';
        } else {
          echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';

        }

      }
      echo '</select>';
    } else {
      echo '<select disabled>';
      foreach ($taxonomies as $taxonomy) {
        echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';
      }
      echo '</select>';
    }

  }

	//////////////////////GOOGLE
  public function feed_custom_setting_enabled_google()
  {

    $invelity_feed_options_brand = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_brand['enabled_google'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[enabled_google]" type="checkbox"/>';
  }

  public function feed_custom_setting_enabled_custom_taxonomy_google()
  {
    $invelity_feed_options_brand = get_option('invelity_product_feeds_setting_values');
    echo '<input' . checked(
      $invelity_feed_options_brand['use_own_google'],
      'on',
      false
    ) . ' name="invelity_product_feeds_setting_values[use_own_google]" type="checkbox"/>';
  }


  public function feed_custom_setting_taxonomy_google()
  {
    $invelity_feed_options = get_option('invelity_product_feeds_setting_values');

    $args = array(
      'public' => true,
      '_builtin' => false

    );
    $output = 'objects'; // or names
    $taxonomies = get_taxonomies($args, $output);

    if ($invelity_feed_options['use_own_google'] == 'on') {
      echo '<select name="invelity_product_feeds_setting_values[use_this_google_taxonomy]">';

      foreach ($taxonomies as $taxonomy) {

        if ($invelity_feed_options['use_this_google_taxonomy'] == $taxonomy->name) {
          echo '<option selected value="' . $invelity_feed_options['use_this_google_taxonomy'] . '" >' . $invelity_feed_options['use_this_google_taxonomy'] . '</option>';
        } else {
          echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';

        }

      }
      echo '</select>';
    } else {
      echo '<select disabled>';
      foreach ($taxonomies as $taxonomy) {
        echo '<option value="' . $taxonomy->name . '">' . $taxonomy->name . '</option>';
      }
      echo '</select>';
    }

  }


}