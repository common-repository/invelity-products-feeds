<?php

class PluginSettingsGenerateFeedPage
{

  public function __construct($launcher)
  {
   // var_dump('PluginSettingsGenerateFeedPage');
    $this->createGenerateFeedPage();
  }


  public function createGenerateFeedPage()
  {

    if (isset($_POST['provider'])) {

      $this->data = $_POST;
      $_POST['filename'] = preg_replace('#[^a-z0-9_]#', "", sanitize_text_field($_POST['filename']));
      $fileName = "wf_config_" . $_POST['filename'];


      if (!get_option($fileName)) {

        update_option($fileName, $_POST);
        if (function_exists('icl_object_id')) {
          global $sitepress;
          $myCurrentLang = apply_filters('wpml_current_language', null);

          $feedLang = $_POST['language'];

          $sitepress->switch_lang($feedLang, true);
        }

        if ($_POST['provider'] == 'Heureka') {
          new InvelityFeedHeureka($this->data);
        }

        if ($_POST['provider'] == 'Facebook') {

          new InvelityFeedFacebook($this->data);

        }

        if ($_POST['provider'] == 'Google Merchant Center') {
          new InvelityFeedGoogle($this->data);
        }
        if ($_POST['provider'] == 'Custom Feed') {
          new InvelityFeedCustomData($this->data);
        }
        if ($_POST['provider'] == 'Dynamic Search Ads') {
          new InvelityFeedDynamicSearchAds($this->data);
        }

        if (function_exists('icl_object_id')) {
          $sitepress->switch_lang($myCurrentLang, true);
        }

      } else {
        echo '<div class="notice notice-error"><p>Name of file already exists</p></div>';
        $this->create_admin_page();
      }

    } else {

      $this->create_admin_page();

    }


  }

  protected function create_admin_page()
  {
    if (class_exists('WC_Shipping_Zones')) {
      $array = WC_Shipping_Zones::get_zones();
    } else {
      $array = [];
    }

    foreach ($array as $ar) {
      $shipping_array = $ar['shipping_methods'];

      $shipping_methods = array_map(function ($o) {
        return $o->id;
      }, $shipping_array);

      //array_map($o, $shipping_array);

      $shipping_class = array_map(function ($o) {
        return $o->instance_settings;
      }, $shipping_array);

      foreach ($shipping_methods as $shipping_method) {
        if ($shipping_method == 'flat_rate') {

          foreach ($shipping_class as $class) {
            $class_shipping_price = [];
            if (array_key_exists('type', $class)) {
              if (!empty($class['cost'])) {
                $input = preg_quote('class_cost_', '~');
                $results = preg_grep('~' . $input . '~', array_keys($class));
                foreach ($results as $result) {
                  if (array_key_exists($result, $class)) {
                    $class_shipping_price = $class[$result];
                  }
                }


              } else {
                $class_shipping_price[] = $class['cost'];
              }

            }
          }
        }
      }
    }


    ?>
		<style>
			body {
				background: #f1f1f1;
			}
		</style>
		<div class="wrap invelity-products-feeds">
			<h1>Invelity Product Feeds</h1>
			<p>Generate Feed</p>
			<br><br>

			<form action="" id="generateFeed" class="generateFeed" method="post">
				<div>
					<table class="widefat fixed ">
						<thead class="tr-heading">
						<tr>
							<td colspan="2"><h4>Feed settings</h4></td>
						</tr>
						</thead>
						<tbody>

						<tr></tr>

              <?php
              if (function_exists('icl_object_id')) { ?>
                  <tr>
                            <td width="30%"><b> Choose language <span class="requiredIn">*</span></b></td>
                            <td>
                              <div class="selectDiv">
                                <?php $languages = icl_get_languages('skip_missing=0&orderby=code');
                              //  var_dump($languages);

                                ?>
                                <select name="language" id="language" data-toggle="tooltip"
                                    title="Select language"
                                    class="generalInput dropdown" required>
                                    <?php
                                    foreach ($languages as $lang => $array) {

                                      echo '<option>' . $lang . '</option>';
                                    }
                                    ?>
                                </select>
                              </div>
                            </td>

                          </tr>
                  <?php

                }
                ?>

						<tr>
							<td width="30%"><b> Feed Merchant Type <span class="requiredIn">*</span></b></td>
							<td>
								<div class="selectDiv">
									<select name="provider" id="provider" data-toggle="tooltip"
											title="Select merchander"
											class="generalInput dropdown" required>
										<option></option>
										<option>Heureka</option>
										<option>Facebook</option>
										<option>Google Merchant Center</option>
										<option>Custom Feed</option>
										<option>Dynamic Search Ads</option>
									</select>
								</div>
							</td>

						</tr>

						<tr>
							<td><b>File name<span class="requiredIn">*</span></b></td>
							<td><input name="filename" type="text" class="generalInput" required="required"
									   placeholder="Input unique name for file">
							</td>
						</tr>
						<tr>
							<td><b>Description</b></td>
							<td><textarea name="feed_description" id="feed_description"></textarea></td>
						</tr>


						<tr class="tr-heading">
							<td colspan="2"><h4>Products settings</h4></td>

						</tr>


						<tr>
							<td><b>Products </b></td>
							<td><select type="text" class="generalInput product_select" name="id_product[]"
										multiple="multiple" data-toggle="tooltip"
										title="If you want specific products start typing and select."
										data-placement="top"></select></td>
						</tr>

						<tr>
							<td><b>Price filter</b></td>

							<td>
								<div class="smallDiv">
									<select name="product_price_rule" wp-feed-tip="Choose comparative symbol">
										<option></option>
										<option> ></option>
										<option> =</option>
										<option> <</option>
									</select>
									<input type="text" class="generalInput" name="price_product" id="price_field"
										   placeholder="For specific price range of products" data-placement="top">
								</div>

							</td>
						</tr>

						<tr>
							<td><b>Products category filter</b></td>

							<td>
								<div class="ui-widget">
									<!--<select type="text" class="generalInput product_cat_select" name="product_cat[]"
											multiple="multiple"></select>-->
									<!--								<input id="my-search" type="search" name="product_cat" class="generalInput" autocomplete="on">-->
									<select type="text" class="generalInput product_cat_select" name="product_cat[]"
											id="multiselect_cat" multiple="multiple" data-toggle="tooltip"
											title="Choose categories" data-placement="top">
										<?php
      /*     $args = array('type' => 'product', 'taxonomy' => 'product_cat');
          $categories = get_categories($args);
					//$cats       = [];

          foreach ($categories as $cat) {
            echo '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
          } */
          ?>
									</select>
								</div>
							</td>

						</tr>

						<tr>
							<td><b>Exclude category</b></td>
							<td>
								<div class="ui-widget">

									<select type="text" class="generalInput product_cat_select" name="exclude_product_cat[]"
											id="multiexcerptselect_cat" multiple="multiple" data-toggle="tooltip"
											title="Choose categories" data-placement="top">
										<?php

          ?>
									</select>
								</div>
							</td>

						</tr>

						<tr>
							<td><b>Check if you want generate only products in stock</b></td>
							<td><input type="checkbox" name="checkbox"></td>
						</tr>


						<tr class="heurekaCat tr-heading">
							<td colspan="2"><h4>Heureka settings</h4></td>
						</tr>


						<tr class="heurekaCat">
							<td><b> Heureka Category </b> <a
										href="https://www.heureka.sk/direct/xml-export/shops/heureka-sekce.xml"> : list
									here</a></td>
							<td>
								<input data-toggle="tooltip" title="Add category specific for Heureka" type="text"
									   class="generalInput"
									   name="heureka_cat"
									   placeholder="" data-placement="top">

							</td>
						</tr>

						<tr class="heurekaCat">
							<td><b>Products availability</b></td>
							<td>
								<div class="selectDiv">
									<select name="product_availability" data-toggle="tooltip"
											title="Add product availability if is out of stock" data-placement="top">
										<option>1-3</option>
										<option>4-7</option>
										<option>8-14</option>
										<option>15-30</option>
										<option>31 and more</option>
									</select>
								</div>
							</td>
						</tr>

						<tr class="wf_csvtxt tr-heading">
							<td colspan="2"><h4>Brand and Google Category settings</h4></td>
						</tr>


						<tr class="wf_csvtxt">
							<td><b>Google category</b><a
										href="http://google.com/basepages/producttype/taxonomy-with-ids.en-US.xls"> :
									list here</a></td>
							<td><input type="text" class="generalInput"
									   name="google_cat" data-toggle="tooltip"
									   title="Add category specific for Google." data-placement="top"></td>
						</tr>

						<tr class="wf_csvtxt">
							<td><b>Brand</b></td>
							<td><input data-toggle="tooltip" title=" Add brand of your products" type="text"
									   class="generalInput"
									   name="feed_brand" data-placement="top"></td>
						</tr>
						<tr class="wf_shipping">
							<td><b>Shipping</b></td>
							<td>
								<select name="shipping">
									<?php
        $array = WC_Shipping_Zones::get_zones();

        foreach ($array as $ar) {
          $shipping_array = $ar['shipping_methods'];



          $shipping_methods = array_map(function ($o) {
            return $o->id;
          }, $shipping_array);

          //array_map($o, $shipping_array);

          $shipping_class = array_map(function ($o) {
            return $o->instance_settings;
          }, $shipping_array);
          //array_map($o, $shipping_array);


       /*    $shipping_class = array_map(create_function(
            '$o',
            'return $o->instance_settings;'
          ), $shipping_array); */

          //var_dump($shipping_class);

          foreach ($shipping_methods as $shipping_method) {
            if ($shipping_method == 'flat_rate') {

              foreach ($shipping_class as $class) {
                $class_shipping_price = [];
                if (array_key_exists('type', $class)) {
                  if (!empty($class['cost'])) {
                    $class_shipping_price = $class['cost'];
                    echo '<option>' . $class_shipping_price . '</option>';

                  } else {
                    $input = preg_quote('class_cost_', '~');
                    $results = preg_grep(
                      '~' . $input . '~',
                      array_keys($class)
                    );
                    foreach ($results as $result) {
                      if (array_key_exists($result, $class)) {
                        $class_shipping_price = $class[$result];
                        echo '<option>' . $class_shipping_price . '</option>';

                      }

                    }


                  }
                  echo '<option>0</option>';
                }
              }
            }
          }
        } ?>
								</select>
							</td>
						</tr>
						<tr class="tr-heading custom_label">
							<td colspan="2"><h4>Custom Label settings</h4></td>
						</tr>
						<tr class="custom_label">
							<td><b>Custom Label</b></td>
							<td>
								<div class="selectDiv">
									<select name="custom_label" id="custom_label" class="generalInput"
											data-toggle="tooltip"
											title=" Select what you want in custom label fields" data-placement="top">
										<option value=""></option>
										<option value="id">ID</option>
										<option value="title">Title</option>
										<option value="description">Description</option>
										<option value="image_link">Image URL</option>
										<option value="condition">Condition</option>
										<option value="type"> Product type</option>
										<option value="quantity">Quantity</option>
										<option value="sale_price">Discount price</option>
										<option value="price">Regular price</option>
										<option value="availability">Availability</option>
										<option value="cat">Category</option>
									</select>
								</div>
							</td>
						</tr>
						<input name="date" id="date" type="hidden" value="<?= date('d.m.Y'); ?>">

						</tbody>
					</table>
					<br/><br/>
					<button type="submit" id="wp_feed_submit" class="button button-primary button-large">
						Generate Feed
					</button>
				</div>
				<div style="width:30%;"></div>
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
			</form>

		</div>


		<?php

}


}