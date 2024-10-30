<?php

class InvelityGenerateProductLoop
{

  public $data;
  public $childID;
  public $parentID;
  public $productsList;
 // public $currentLanguage;
  //public $siteLanguage;

  public function __construct($data)
  {

    $this->generateProductLoop($data);

  }

  private function generateProductLoop($data)
  {

   // var_dump($data);


    $price_rule = $data['product_price_rule'];
    $price_value = $data['price_product'];
    $product_cat = $data['product_cat'];

    if (isset($data['id_product']) && $data['id_product']) {

      $integerIDs = array_map('intval', $data['id_product']);

    } else {
      $integerIDs = [];
    }


    if (is_array($data['product_cat'])) {
      if (isset($data['product_cat']) && $data['product_cat']) {
        $product_cat = array_map('intval', $data['product_cat']);

      } else {
        $product_cat = [];

      }
    } else {
      $product_cat[0] = $data['product_cat'];
    }


    if (isset($data['exclude_product_cat']) && $data['exclude_product_cat']) {
      $except_cat = array_map('intval', $data['exclude_product_cat']);
    } else {
      $except_cat = [];
    }

    $getIDs = get_option("wf_check_duplicate");

    $arg = array(
      'post_type' => array('product', 'product_variation'),
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'date',
      'order' => 'desc',
      'fields' => 'ids',
      'cache_results' => false,
      'update_post_term_cache' => false,
      'update_post_meta_cache' => false,
    );
    if (!empty($integerIDs)) {
      $arg['post__in'] = $integerIDs;
    }
        /*if(! empty( $product_cat)) {
        $arg['category__in'] = $product_cat;
        }*/
    if (!empty($price_value) && !empty($price_rule)) {
      $arg['meta_query'] = array(
        array(
          'key' => '_price',
          'value' => $price_value,
          'compare' => $price_rule,
          'type' => 'NUMERIC',
        ),
      );
    }


    if (!empty($product_cat)) {
      $arg['tax_query'] = array(
        'relation' => 'AND',
        array(
          'taxonomy' => 'product_cat',
          'field' => 'term_id',
          'terms' => $product_cat,
        ),
        array(
          'taxonomy' => 'product_cat',
          'field' => 'term_id',
          'terms' => $except_cat,
          'operator' => 'NOT IN',
        ),
      );
    }

    $loop = new WP_Query($arg);
    
        //$test = new WP_Query(array('category__in'=> array(  97,98 ) ));
    $i = 0;


    while ($loop->have_posts()) : $loop->the_post();
    //var_dump($loop);


    $this->childID = get_the_ID();
    $this->parentID = (wp_get_post_parent_id($this->childID)) ? wp_get_post_parent_id($this->childID) : $this->childID;
    $invelity_feed_options = get_option('invelity_product_feeds_setting_values');
    global $product;
    if (!is_object($product) || !$product->is_visible()) {
      continue;
    }

    $type1 = "";
    if (is_object($product) && $product->is_type('simple')) {
                # No variations to product
      $type1 = "simple";
    } elseif (is_object($product) && $product->is_type('variable')) {
            # Product has variations
      $type1 = "variable";
    } elseif (is_object($product) && $product->is_type('grouped')) {
      $type1 = "grouped";
    } elseif (is_object($product) && $product->is_type('external')) {
      $type1 = "external";
    } elseif (is_object($product) && $product->is_downloadable()) {
      $type1 = "downloadable";
    } elseif (is_object($product) && $product->is_virtual()) {
      $type1 = "virtual";
    }

    $post = get_post($this->parentID);

    if (!is_object($post)) {
      continue;
    }

    if ($post->post_status == 'trash') {
      continue;
    }

    if (get_post_type() == 'product_variation' && $this->data['provider'] != 'facebook') {
      if ($this->parentID != 0) {

        $status = get_post($this->childID);
        if (!$status || !is_object($status)) {
          continue;
        }

        if ($status->post_status == "trash") {
          continue;
        }

        $parentStatus = get_post($this->parentID);
        if ($parentStatus && is_object($parentStatus) && $parentStatus->post_status != 'publish') {
          continue;
        }

                # Check Valid URL
        $mainImage = wp_get_attachment_url($product->get_image_id());
        $link = $product->get_permalink($this->childID);

        if ($this->data['provider'] != 'custom') {
          if (substr(trim($link), 0, 4) !== "http" && substr(
            trim($mainImage),
            0,
            4
          ) !== "http") {
            continue;
          }
        }

        $this->productsList[$i]['id'] = $this->childID;
        $this->productsList[$i]['variation_type'] = "child";
        $this->productsList[$i]['item_group_id'] = $this->parentID;
        $this->productsList[$i]['sku'] = $this->getAttributeValue($this->childID, "_sku");
        $this->productsList[$i]['parent_sku'] = $this->getAttributeValue($this->parentID, "_sku");
        $this->productsList[$i]['title'] = $post->post_title;
        $this->productsList[$i]['description'] = $post->post_content;

                # Short Description to variable description
        $vDesc = $this->getAttributeValue($this->childID, "_variation_description");
        if (!empty($vDesc)) {
          $this->productsList[$i]['short_description'] = $vDesc;
        } else {
          $this->productsList[$i]['short_description'] = $post->post_excerpt;
        }

        $this->productsList[$i]['product_type'] = $this->get_product_term_list(
          $post->ID,
          'product_cat',
          "",
          ">"
        );
                // $this->categories($this->parentID);//TODO
        $this->productsList[$i]['link'] = $link;
        $this->productsList[$i]['ex_link'] = "";
        $this->productsList[$i]['image'] = $this->get_formatted_url($mainImage);


        if (!isset($invelity_feed_options['use_this_heureka_taxonomy'])) {
          $heureka = (taxonomy_exists('heureka_category')) ? $this->get_product_term_list($post->ID, 'heureka_category') : "";
        } else {
          $heureka = (taxonomy_exists($invelity_feed_options['use_this_heureka_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_heureka_taxonomy']) : "";
        }



        $this->productsList[$i]['heureka_category'] = $heureka;


        if (!isset($invelity_feed_options['use_this_google_taxonomy'])) {
          $google = (taxonomy_exists('google_category')) ? $this->get_product_term_list($post->ID, 'google_category') : "";
        } else {
          $google = (taxonomy_exists($invelity_feed_options['use_this_google_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_google_taxonomy']) : "";
        }



        $this->productsList[$i]['google_category'] = $google;


        if (!isset($invelity_feed_options['use_this_brand_taxonomy'])) {
          $brand = (taxonomy_exists('brand')) ? $this->get_product_term_list($post->ID, 'brand') : "";
        } else {
          $brand = (taxonomy_exists($invelity_feed_options['use_this_brand_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_brand_taxonomy']) : "";
        }



        $this->productsList[$i]['brand'] = $brand;
        $this->productsList[$i]['category_ids'] = $terms_categories = get_the_terms($post->ID, 'product_cat');

                # Featured Image
        if (has_post_thumbnail($post->ID)) :
          $image = wp_get_attachment_image_src(
          get_post_thumbnail_id($post->ID),
          'single-post-thumbnail'
        );
        $this->productsList[$i]['feature_image'] = $this->get_formatted_url($image[0]);
        else :
          $this->productsList[$i]['feature_image'] = $this->get_formatted_url($mainImage);
        endif;

                # Additional Images
        $imageLinks = array();
        $images = $this->additionalImages($this->childID);
        if ($images && is_array($images)) {
          $mKey = 1;
          foreach ($images as $key => $value) {
            if ($value != $this->productsList[$i]['image']) {
              $imgLink = $this->get_formatted_url($value);
              $this->productsList[$i]["image_$mKey"] = $imgLink;
              if (!empty($imgLink)) {
                array_push($imageLinks, $imgLink);
              }
            }
            $mKey++;
          }
        }
        $this->productsList[$i]['images'] = implode(",", $imageLinks);
        $this->productsList[$i]['condition'] = "New";
        $this->productsList[$i]['type'] = $product->get_type();
        $this->productsList[$i]['visibility'] = $this->getAttributeValue(
          $this->childID,
          "_visibility"
        );
        $this->productsList[$i]['rating_total'] = $product->get_rating_count();
        $this->productsList[$i]['rating_average'] = $product->get_average_rating();
        $this->productsList[$i]['tags'] = $this->get_product_term_list(
          $post->ID,
          'product_tag'
        );
        $this->productsList[$i]['shipping'] = $product->get_shipping_class();

        $this->productsList[$i]['availability'] = $this->availability($this->childID);
        $this->productsList[$i]['quantity'] = $this->get_quantity($this->childID, "_stock");
        $this->productsList[$i]['sale_price_sdate'] = $this->get_date(
          $this->childID,
          "_sale_price_dates_from"
        );
        $this->productsList[$i]['sale_price_edate'] = $this->get_date(
          $this->childID,
          "_sale_price_dates_to"
        );
        $this->productsList[$i]['price'] = ($product->get_regular_price()) ? $product->get_regular_price() : $product->get_price();
        $this->productsList[$i]['sale_price'] = ($product->get_sale_price()) ? $product->get_sale_price() : "";
        $this->productsList[$i]['weight'] = ($product->get_weight()) ? $product->get_weight() : "";
        $this->productsList[$i]['width'] = ($product->get_width()) ? $product->get_width() : "";
        $this->productsList[$i]['height'] = ($product->get_height()) ? $product->get_height() : "";
        $this->productsList[$i]['length'] = ($product->get_length()) ? $product->get_length() : "";

        $from = $this->sale_price_effective_date($this->childID, '_sale_price_dates_from');
        $to = $this->sale_price_effective_date($this->childID, '_sale_price_dates_to');
        if (!empty($from) && !empty($to)) {
          $from = date("c", strtotime($from));
          $to = date("c", strtotime($to));
          $this->productsList[$i]['sale_price_effective_date'] = "$from" . "/" . "$to";
        } else {
          $this->productsList[$i]['sale_price_effective_date'] = "";
        }

      }
    } elseif (get_post_type() == 'product') {
      if ($type1 == 'simple') {

        $mainImage = wp_get_attachment_url($product->get_image_id());
        $link = get_permalink($post->ID);

        if ($this->data['provider'] != 'custom') {
          if (substr(trim($link), 0, 4) !== "http" && substr(
            trim($mainImage),
            0,
            4
          ) !== "http") {
            continue;
          }
        }

        $this->productsList[$i]['id'] = $post->ID;
        $this->productsList[$i]['variation_type'] = "simple";
        $this->productsList[$i]['title'] = $product->get_title();
        $this->productsList[$i]['description'] = $post->post_content;

        $this->productsList[$i]['short_description'] = $post->post_excerpt;
        $this->productsList[$i]['product_type'] = $this->get_product_term_list(
          $post->ID,
          'product_cat',
          "",
          ">"
        ); // $this->categories($this->parentID);//
        $this->productsList[$i]['link'] = $link;
        $this->productsList[$i]['ex_link'] = "";
        $this->productsList[$i]['image'] = $this->get_formatted_url($mainImage);




        if (!isset($invelity_feed_options['use_this_heureka_taxonomy'])) {
          $heureka = (taxonomy_exists('heureka_category')) ? $this->get_product_term_list($post->ID, 'heureka_category') : "";
        } else {
          $heureka = (taxonomy_exists($invelity_feed_options['use_this_heureka_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_heureka_taxonomy']) : "";
        }


        $this->productsList[$i]['heureka_category'] = $heureka;


        if (!isset($invelity_feed_options['use_this_google_taxonomy'])) {
          $google = (taxonomy_exists('google_category')) ? $this->get_product_term_list($post->ID, 'google_category') : "";
        } else {
          $google = (taxonomy_exists($invelity_feed_options['use_this_google_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_google_taxonomy']) : "";
        }


        $this->productsList[$i]['google_category'] = $google;


        if (!isset($invelity_feed_options['use_this_brand_taxonomy'])) {
          $brand = (taxonomy_exists('brand')) ? $this->get_product_term_list($post->ID, 'brand') : "";
        } else {
          $brand = (taxonomy_exists($invelity_feed_options['use_this_brand_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_brand_taxonomy']) : "";
        }


        $this->productsList[$i]['brand'] = $brand;

        $this->productsList[$i]['category_ids'] = $terms_categories = get_the_terms($post->ID, 'product_cat');

        if (has_post_thumbnail($post->ID)) :
          $image = wp_get_attachment_image_src(
          get_post_thumbnail_id($post->ID),
          'single-post-thumbnail'
        );
        $this->productsList[$i]['feature_image'] = $this->get_formatted_url($image[0]);
        else :
          $this->productsList[$i]['feature_image'] = $this->get_formatted_url($mainImage);
        endif;

        $imageLinks = array();
        $images = $this->additionalImages($post->ID);
        if ($images && is_array($images)) {
          $mKey = 1;
          foreach ($images as $key => $value) {
            if ($value != $this->productsList[$i]['image']) {
              $imgLink = $this->get_formatted_url($value);
              $this->productsList[$i]["image_$mKey"] = $imgLink;
              if (!empty($imgLink)) {
                array_push($imageLinks, $imgLink);
              }
            }
            $mKey++;
          }
        }
        $this->productsList[$i]['images'] = implode(",", $imageLinks);

        $this->productsList[$i]['condition'] = "New";
        $this->productsList[$i]['type'] = $product->get_type();
        $this->productsList[$i]['visibility'] = $this->getAttributeValue($post->ID, "_visibility");
        $this->productsList[$i]['rating_total'] = $product->get_rating_count();
        $this->productsList[$i]['rating_average'] = $product->get_average_rating();
        $this->productsList[$i]['tags'] = $this->get_product_term_list(
          $post->ID,
          'product_tag'
        );

        $this->productsList[$i]['item_group_id'] = $post->ID;
        $this->productsList[$i]['sku'] = $this->getAttributeValue($post->ID, "_sku");

        $this->productsList[$i]['availability'] = $this->availability($post->ID);
        $this->productsList[$i]['quantity'] = $this->get_quantity($post->ID, "_stock");
        $this->productsList[$i]['sale_price_sdate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_from"
        );
        $this->productsList[$i]['sale_price_edate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_to"
        );
        $this->productsList[$i]['price'] = ($product->get_regular_price()) ? $product->get_regular_price() : $product->get_price();
        $this->productsList[$i]['sale_price'] = ($product->get_sale_price()) ? $product->get_sale_price() : "";
        $this->productsList[$i]['weight'] = ($product->get_weight()) ? $product->get_weight() : "";
        $this->productsList[$i]['width'] = ($product->get_width()) ? $product->get_width() : "";
        $this->productsList[$i]['height'] = ($product->get_height()) ? $product->get_height() : "";
        $this->productsList[$i]['length'] = ($product->get_length()) ? $product->get_length() : "";



        if (!isset($invelity_feed_options['use_this_heureka_taxonomy'])) {
          $heureka = (taxonomy_exists('heureka_category')) ? $this->get_product_term_list($post->ID, 'heureka_category') : "";
        } else {
          $heureka = (taxonomy_exists($invelity_feed_options['use_this_heureka_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_heureka_taxonomy']) : "";
        }


        $this->productsList[$i]['heureka_category'] = $heureka;


        if (!isset($invelity_feed_options['use_this_google_taxonomy'])) {
          $google = (taxonomy_exists('google_category')) ? $this->get_product_term_list($post->ID, 'google_category') : "";
        } else {
          $google = (taxonomy_exists($invelity_feed_options['use_this_google_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_google_taxonomy']) : "";
        }


        $this->productsList[$i]['google_category'] = $google;


        if (!isset($invelity_feed_options['use_this_brand_taxonomy'])) {
          $brand = (taxonomy_exists('brand')) ? $this->get_product_term_list($post->ID, 'brand') : "";
        } else {
          $brand = (taxonomy_exists($invelity_feed_options['use_this_brand_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_brand_taxonomy']) : "";
        }


        $this->productsList[$i]['brand'] = $brand;


        $this->productsList[$i]['category_ids'] = $terms_categories = get_the_terms($post->ID, 'product_cat');

        $from = $this->sale_price_effective_date($post->ID, '_sale_price_dates_from');
        $to = $this->sale_price_effective_date($post->ID, '_sale_price_dates_to');
        if (!empty($from) && !empty($to)) {
          $from = date("c", strtotime($from));
          $to = date("c", strtotime($to));
          $this->productsList[$i]['sale_price_effective_date'] = "$from" . "/" . "$to";
        } else {
          $this->productsList[$i]['sale_price_effective_date'] = "";
        }

      } else if ($type1 == 'external') {

        $mainImage = wp_get_attachment_url($product->get_image_id());

        $getLink = new WC_Product_External($post->ID);
        $EX_link = $getLink->get_product_url();
        $link = get_permalink($post->ID);
        if ($this->data['provider'] != 'custom') {
          if (substr(trim($link), 0, 4) !== "http" && substr(
            trim($mainImage),
            0,
            4
          ) !== "http") {
            continue;
          }
        }

        $this->productsList[$i]['id'] = $post->ID;
        $this->productsList[$i]['variation_type'] = "external";
        $this->productsList[$i]['title'] = $product->get_title();
        $this->productsList[$i]['description'] = do_shortcode($post->post_content);

        $this->productsList[$i]['short_description'] = $post->post_excerpt;
        $this->productsList[$i]['product_type'] = $this->get_product_term_list(
          $post->ID,
          'product_cat',
          "",
          ">"
        ); // $this->categories($this->parentID);//TODO
        $this->productsList[$i]['link'] = $link;
        $this->productsList[$i]['ex_link'] = $EX_link;
        $this->productsList[$i]['image'] = $this->get_formatted_url($mainImage);

        if (has_post_thumbnail($post->ID)) :
          $image = wp_get_attachment_image_src(
          get_post_thumbnail_id($post->ID),
          'single-post-thumbnail'
        );
        $this->productsList[$i]['feature_image'] = $this->get_formatted_url($image[0]);
        else :
          $this->productsList[$i]['feature_image'] = $this->get_formatted_url($mainImage);
        endif;

        $imageLinks = array();
        $images = $this->additionalImages($post->ID);
        if ($images && is_array($images)) {
          $mKey = 1;
          foreach ($images as $key => $value) {
            if ($value != $this->productsList[$i]['image']) {
              $imgLink = $this->get_formatted_url($value);
              $this->productsList[$i]["image_$mKey"] = $imgLink;
              if (!empty($imgLink)) {
                array_push($imageLinks, $imgLink);
              }
            }
            $mKey++;
          }
        }
        $this->productsList[$i]['images'] = implode(",", $imageLinks);

        $this->productsList[$i]['condition'] = "New";
        $this->productsList[$i]['type'] = $product->get_type();
        $this->productsList[$i]['visibility'] = $this->getAttributeValue($post->ID, "_visibility");
        $this->productsList[$i]['rating_total'] = $product->get_rating_count();
        $this->productsList[$i]['rating_average'] = $product->get_average_rating();
        $this->productsList[$i]['tags'] = $this->get_product_term_list(
          $post->ID,
          'product_tag'
        );

        $this->productsList[$i]['item_group_id'] = $post->ID;
        $this->productsList[$i]['sku'] = $this->getAttributeValue($post->ID, "_sku");

        $this->productsList[$i]['availability'] = $this->availability($post->ID);

        $this->productsList[$i]['quantity'] = $this->get_quantity($post->ID, "_stock");
        $this->productsList[$i]['sale_price_sdate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_from"
        );
        $this->productsList[$i]['sale_price_edate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_to"
        );
        $this->productsList[$i]['price'] = ($product->get_regular_price()) ? $product->get_regular_price() : $product->get_price();
        $this->productsList[$i]['sale_price'] = ($product->get_sale_price()) ? $product->get_sale_price() : "";
        $this->productsList[$i]['weight'] = ($product->get_weight()) ? $product->get_weight() : "";
        $this->productsList[$i]['width'] = ($product->get_width()) ? $product->get_width() : "";
        $this->productsList[$i]['height'] = ($product->get_height()) ? $product->get_height() : "";
        $this->productsList[$i]['length'] = ($product->get_length()) ? $product->get_length() : "";

        $from = $this->sale_price_effective_date($post->ID, '_sale_price_dates_from');
        $to = $this->sale_price_effective_date($post->ID, '_sale_price_dates_to');
        if (!empty($from) && !empty($to)) {
          $from = date("c", strtotime($from));
          $to = date("c", strtotime($to));
          $this->productsList[$i]['sale_price_effective_date'] = "$from" . "/" . "$to";
        } else {
          $this->productsList[$i]['sale_price_effective_date'] = "";
        }

      } elseif ($type1 == 'grouped') {

        $grouped = new WC_Product_Grouped($post->ID);
        $children = $grouped->get_children();
        $this->parentID = $post->ID;
        if ($children) {
          foreach ($children as $cKey => $child) {

            $product = new WC_Product($child);
            $this->childID = $child;
            $post = get_post($this->childID);

            if ($post->post_status == 'trash') {
              continue;
            }

            if (!empty($this->ids_in) && !in_array($post->ID, $this->ids_in)) {
              continue;
            }

            if (!empty($this->ids_not_in) && in_array($post->ID, $this->ids_in)) {
              continue;
            }

            if (!$product->is_visible()) {
              continue;
            }

            $i++;

            $mainImage = wp_get_attachment_url($product->get_image_id());
            $link = get_permalink($post->ID);
            if ($this->data['provider'] != 'custom') {
              if (substr(trim($link), 0, 4) !== "http" && substr(
                trim($mainImage),
                0,
                4
              ) !== "http") {
                continue;
              }
            }

            $this->productsList[$i]['id'] = $post->ID;
            $this->productsList[$i]['variation_type'] = "grouped";
            $this->productsList[$i]['title'] = $product->get_title();
            $this->productsList[$i]['description'] = do_shortcode($post->post_content);

            $this->productsList[$i]['short_description'] = $post->post_excerpt;
            $this->productsList[$i]['product_type'] = $this->get_product_term_list(
              $post->ID,
              'product_cat',
              "",
              ">"
            ); // $this->categories($this->parentID);//TODO
            $this->productsList[$i]['link'] = $link;
            $this->productsList[$i]['ex_link'] = "";
            $this->productsList[$i]['image'] = $this->get_formatted_url($mainImage);

            if (has_post_thumbnail($post->ID)) :
              $image = wp_get_attachment_image_src(
              get_post_thumbnail_id($post->ID),
              'single-post-thumbnail'
            );
            $this->productsList[$i]['feature_image'] = $this->get_formatted_url($image[0]);
            else :
              $this->productsList[$i]['feature_image'] = $this->get_formatted_url($mainImage);
            endif;

            $imageLinks = array();
            $images = $this->additionalImages($this->childID);
            if ($images and is_array($images)) {
              $mKey = 1;
              foreach ($images as $key => $value) {
                if ($value != $this->productsList[$i]['image']) {
                  $imgLink = $this->get_formatted_url($value);
                  $this->productsList[$i]["image_$mKey"] = $imgLink;
                  if (!empty($imgLink)) {
                    array_push($imageLinks, $imgLink);
                  }
                }
                $mKey++;
              }
            }
            $this->productsList[$i]['images'] = implode(",", $imageLinks);
            $this->productsList[$i]['condition'] = "New";
            $this->productsList[$i]['type'] = $product->get_type();
            $this->productsList[$i]['visibility'] = $this->getAttributeValue(
              $post->ID,
              "_visibility"
            );
            $this->productsList[$i]['rating_total'] = $product->get_rating_count();
            $this->productsList[$i]['rating_average'] = $product->get_average_rating();
            $this->productsList[$i]['tags'] = $this->get_product_term_list(
              $post->ID,
              'product_tag'
            );

            $this->productsList[$i]['item_group_id'] = $this->parentID;
            $this->productsList[$i]['sku'] = $this->getAttributeValue($post->ID, "_sku");

            $this->productsList[$i]['availability'] = $this->availability($post->ID);

            $this->productsList[$i]['quantity'] = $this->get_quantity($post->ID, "_stock");
            $this->productsList[$i]['sale_price_sdate'] = $this->get_date(
              $post->ID,
              "_sale_price_dates_from"
            );
            $this->productsList[$i]['sale_price_edate'] = $this->get_date(
              $post->ID,
              "_sale_price_dates_to"
            );
            $this->productsList[$i]['price'] = ($product->get_regular_price()) ? $product->get_regular_price() : $product->get_price();
            $this->productsList[$i]['sale_price'] = ($product->get_sale_price()) ? $product->get_sale_price() : "";
            $this->productsList[$i]['weight'] = ($product->get_weight()) ? $product->get_weight() : "";
            $this->productsList[$i]['width'] = ($product->get_width()) ? $product->get_width() : "";
            $this->productsList[$i]['height'] = ($product->get_height()) ? $product->get_height() : "";
            $this->productsList[$i]['length'] = ($product->get_length()) ? $product->get_length() : "";

            $from = $this->sale_price_effective_date($post->ID, '_sale_price_dates_from');
            $to = $this->sale_price_effective_date($post->ID, '_sale_price_dates_to');
            if (!empty($from) && !empty($to)) {
              $from = date(
                "c",
                strtotime($from)
              );
              $to = date("c", strtotime($to));
              $this->productsList[$i]['sale_price_effective_date'] = "$from" . "/" . "$to";
            } else {
              $this->productsList[$i]['sale_price_effective_date'] = "";
            }
          }
        }
      } else if ($type1 == 'variable' && $product->has_child()) {

        $mainImage = wp_get_attachment_url($product->get_image_id());
        $link = get_permalink($post->ID);

        if ($this->data['provider'] != 'custom') {
          if (substr(trim($link), 0, 4) !== "http" && substr(
            trim($mainImage),
            0,
            4
          ) !== "http") {
            continue;
          }
        }

        $this->productsList[$i]['id'] = $post->ID;
        $this->productsList[$i]['variation_type'] = "parent";
        $this->productsList[$i]['title'] = $post->post_title;
        $this->productsList[$i]['description'] = $post->post_content;

        $this->productsList[$i]['short_description'] = $post->post_excerpt;
        $this->productsList[$i]['product_type'] = $this->get_product_term_list(
          $post->ID,
          'product_cat',
          "",
          ">"
        ); // $this->categories($this->parentID);//TODO

        $this->productsList[$i]['link'] = $link;
        $this->productsList[$i]['ex_link'] = "";
        $this->productsList[$i]['image'] = $this->get_formatted_url($mainImage);


        if (!isset($invelity_feed_options['use_this_heureka_taxonomy'])) {
          $heureka = (taxonomy_exists('heureka_category')) ? $this->get_product_term_list($post->ID, 'heureka_category') : "";
        } else {
          $heureka = (taxonomy_exists($invelity_feed_options['use_this_heureka_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_heureka_taxonomy']) : "";
        }


        $this->productsList[$i]['heureka_category'] = $heureka;


        if (!isset($invelity_feed_options['use_this_google_taxonomy'])) {
          $google = (taxonomy_exists('google_category')) ? $this->get_product_term_list($post->ID, 'google_category') : "";
        } else {
          $google = (taxonomy_exists($invelity_feed_options['use_this_google_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_google_taxonomy']) : "";
        }


        $this->productsList[$i]['google_category'] = $google;



        if (!isset($invelity_feed_options['use_this_brand_taxonomy'])) {
          $brand = (taxonomy_exists('brand')) ? $this->get_product_term_list($post->ID, 'brand') : "";
        } else {
          $brand = (taxonomy_exists($invelity_feed_options['use_this_brand_taxonomy'])) ? $this->get_product_term_list($post->ID, $invelity_feed_options['use_this_brand_taxonomy']) : "";
        }


        $this->productsList[$i]['brand'] = $brand;

        $this->productsList[$i]['category_ids'] = $terms_categories = get_the_terms($post->ID, 'product_cat');

        if (has_post_thumbnail($post->ID)) :
          $image = wp_get_attachment_image_src(
          get_post_thumbnail_id($post->ID),
          'single-post-thumbnail'
        );
        $this->productsList[$i]['feature_image'] = $this->get_formatted_url($image[0]);
        else :
          $this->productsList[$i]['feature_image'] = $this->get_formatted_url($mainImage);
        endif;

        $imageLinks = array();
        $images = $this->additionalImages($post->ID);
        if ($images and is_array($images)) {
          $mKey = 1;
          foreach ($images as $key => $value) {
            if ($value != $this->productsList[$i]['image']) {
              $imgLink = $this->get_formatted_url($value);
              $this->productsList[$i]["image_$mKey"] = $imgLink;
              if (!empty($imgLink)) {
                array_push($imageLinks, $imgLink);
              }
            }
            $mKey++;
          }
        }
        $this->productsList[$i]['images'] = implode(",", $imageLinks);

        $this->productsList[$i]['condition'] = "New";
        $this->productsList[$i]['type'] = $product->get_type();
        $this->productsList[$i]['visibility'] = $this->getAttributeValue($post->ID, "_visibility");
        $this->productsList[$i]['rating_total'] = $product->get_rating_count();
        $this->productsList[$i]['rating_average'] = $product->get_average_rating();
        $this->productsList[$i]['tags'] = $this->get_product_term_list(
          $post->ID,
          'product_tag'
        );

        $this->productsList[$i]['item_group_id'] = $post->ID;
        $this->productsList[$i]['sku'] = $this->getAttributeValue($post->ID, "_sku");

        $this->productsList[$i]['availability'] = $this->availability($post->ID);
        $this->productsList[$i]['quantity'] = $this->get_quantity($post->ID, "_stock");
        $this->productsList[$i]['sale_price_sdate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_from"
        );
        $this->productsList[$i]['sale_price_edate'] = $this->get_date(
          $post->ID,
          "_sale_price_dates_to"
        );

        $price = ($product->get_price()) ? $product->get_price() : false;


        $this->productsList[$i]['price'] = ($product->get_regular_price()) ? $product->get_regular_price() : $price;
        $this->productsList[$i]['sale_price'] = ($product->get_sale_price()) ? $product->get_sale_price() : "";
        $this->productsList[$i]['weight'] = ($product->get_weight()) ? $product->get_weight() : "";
        $this->productsList[$i]['width'] = ($product->get_width()) ? $product->get_width() : "";
        $this->productsList[$i]['height'] = ($product->get_height()) ? $product->get_height() : "";
        $this->productsList[$i]['length'] = ($product->get_length()) ? $product->get_length() : "";

        $from = $this->sale_price_effective_date($post->ID, '_sale_price_dates_from');
        $to = $this->sale_price_effective_date($post->ID, '_sale_price_dates_to');
        if (!empty($from) && !empty($to)) {
          $from = date("c", strtotime($from));
          $to = date("c", strtotime($to));
          $this->productsList[$i]['sale_price_effective_date'] = "$from" . "/" . "$to";
        } else {
          $this->productsList[$i]['sale_price_effective_date'] = "";
        }
      }
    }
    $i++;
    endwhile;
    wp_reset_query();

    return $this->productsList;

  }

  public function get_product_term_list($id, $taxonomy, $before = '', $sep = ',', $after = '')
  {
    $terms = get_the_terms($id, $taxonomy);

    if (is_wp_error($terms)) {
      return $terms;
    }

    if (empty($terms)) {
      return false;
    }

    $links = array();

    foreach ($terms as $term) {
      $links[] = $term->name;
    }
    ksort($links);

    return $before . join($sep, $links) . $after;
  }

  public function get_formatted_url($url = "")
  {
    if (!empty($url)) {
      if (substr(trim($url), 0, 4) === "http" || substr(
        trim($url),
        0,
        3
      ) === "ftp" || substr(trim($url), 0, 4) === "sftp") {
        return rtrim($url, "/");
      } else {
        $base = get_site_url();
        $url = $base . $url;

        return rtrim($url, "/");
      }
    }

    return $url;
  }

  public function additionalImages($Id)
  {
    $ids = $this->getAttributeValue($Id, "_product_image_gallery");
    $imgIds = !empty($ids) ? explode(",", $ids) : "";

    $images = array();
    if (!empty($imgIds)) {
      foreach ($imgIds as $key => $value) {
        if ($key < 10) {
          $images[$key] = wp_get_attachment_url($value);
        }
      }

      return $images;
    }

    return false;
  }

  public function getAttributeValue($id, $name)
  {
    if (strpos($name, 'attribute_pa') !== false) {
      $taxonomy = str_replace("attribute_", "", $name);
      $meta = get_post_meta($id, $name, true);
      $term = get_term_by('slug', $meta, $taxonomy);

      return $term->name;
    } else {
      return get_post_meta($id, $name, true);
    }

  }

  public function sale_price_effective_date($id, $name)
  {
    return ($date = $this->getAttributeValue($id, $name)) ? date_i18n('Y-m-d', $date) : "";
  }

  public function availability($id)
  {
    $status = $this->getAttributeValue($id, "_stock_status");
    if ($status) {
      if ($status == 'instock') {
        return "in stock";
      } elseif ($status == 'outofstock') {
        return "out of stock";
      }
    }

    return "out of stock";
  }

  public function get_quantity($id, $name)
  {
    $qty = $this->getAttributeValue($id, $name);
    if ($qty) {
      return $qty + 0;
    }

    return "0";
  }

  public function get_date($id, $name)
  {
    $date = $this->getAttributeValue($id, $name);
    if ($date) {
      return date("Y-m-d", $date);
    }

    return false;
  }

}
