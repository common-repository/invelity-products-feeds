<?php

class InvelityFeedHeureka
{

    private $xml;
    public $heureka;
    public $data;
    public $product;

    public function __construct($data)
    {

        add_filter('w3tc_can_print_comment', '__return_false', 10, 1);
        $this->xml = new INVELITY_SIMPLE_XML("<?xml version=\"1.0\" encoding=\"utf-8\" ?><SHOP></SHOP>");
        $this->mapProductsByRules($data);
        $this->generateHeurekaXML($data);

    }

    private function mapProductsByRules($data)
    {

        $products = new InvelityGenerateProductLoop($data);


        $bu = [];
        $productsFeed = [];

        if (is_array($products->productsList) || is_object($products->productsList)) {
            foreach ($products->productsList as $key) {

                $invelity_heureka_category = $this->productHeurekaCategories($key, $data);

                if (isset($data['product_availability'])) {
                    $availability = $this->getProductAvailability($key['availability'], $data['product_availability']);
                } else {
                    $availability = $this->getProductAvailability($key['availability'], "");
                }
                $heureka_cat = (empty($data['heureka_category']) ? $invelity_heureka_category : $key['heureka_category']);


                if (isset($data['checkbox'])) {
                    if ($data['checkbox'] == 'on') {


                        if ($key['availability'] == 'in stock') {

                            $productsFeed[] = $this->setProduct($key, $heureka_cat, $availability);


                        } else {
                            //if out of stock

                            $productsFeed[] = $this->setProduct($key, $heureka_cat, $availability);

                        }
                    } else {
                        //if checkbox not on
                        $productsFeed[] = $this->setProduct($key, $heureka_cat, $availability);


                    }

                } else {
                    //if not set data['checkbox']

                    $productsFeed[] = $this->setProduct($key, $heureka_cat, $availability);

                }


            }
        } else {
            //if not array or object
            $results = [

                'data' => $data,
                'message' => 'error',
            ];
            error_log(print_r($results, true));
        }


        foreach ($productsFeed as $key => $products) {

            foreach ($products as $product) {

                if (isset($product['variation'])) {

                    $shopItem = $this->xml->addChild('SHOPITEM');
                    $shopItem->addChild('ITEM_ID', $product['variation']['variation_id']);
                    $shopItem->addChild('ITEMGROUP_ID', $product['id']);

                    if (isset($product['variation']['variation_name'])) {
                        $shopItem->addChildWithCDATA('PRODUCTNAME', $product['variation']['variation_name']);
                    } else {
                        $shopItem->addChildWithCDATA('PRODUCTNAME', $product['title']);
                    }
                    $shopItem->addChildWithCDATA('DESCRIPTION', $product['variation']['variation_description']);
                    $shopItem->addChild('URL', htmlspecialchars($product['variation']['variation_url']));
                    $shopItem->addChild('IMGURL', $product['variation']['variation_image']);

                    $shopItem->addChild('CATEGORYTEXT', $product['variation']['heureka_category']);
                    $shopItem->addChild('PRICE_VAT', $product['variation']['variation_price']);

                    $shopItem->addChild('DELIVERY_DATE', $product['variation']['variation_availability']);


                    if (isset($product['variation']['params'])) {
                        foreach ($product['variation']['params'] as $key => $val) {
                            $param = $shopItem->addChild('PARAM');
                            $param->addChild('PARAM_NAME', $key);
                            $param->addChild('VAL', $val);

                        }


                    }
                } else {
                    $shopItem = $this->xml->addChild('SHOPITEM');
                    $shopItem->addChild('ITEM_ID', $product['id']);
                    $shopItem->addChildWithCDATA('PRODUCTNAME', $product['title']);
                    $shopItem->addChildWithCDATA('DESCRIPTION', $product['description']);
                    $shopItem->addChild('URL', htmlspecialchars($product['url']));
                    $shopItem->addChild('IMGURL', $product['image']);
                    $shopItem->addChild('CATEGORYTEXT', $product['heureka_category']);
                    $shopItem->addChild('PRICE_VAT', $product['price']);
                    $shopItem->addChild('DELIVERY_DATE', $product['availability']);
                }

            }

        }

    }

    private function productHeurekaCategories($key, $data)
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
        $get_term_result = '';
        $get_term = get_term_meta(
            $cat_id,
            'invelity_heureka_category',
            true
        );

        if (is_array($get_term)) {
            foreach ($get_term as $term) {
                $get_term_result = $term;
            }
        } else {
            $get_term_result = get_term_meta(
                $cat_id,
                'invelity_heureka_category',
                true
            );
        }

        $invelity_heureka_category = $get_term_result ? $get_term_result : sanitize_text_field($data['heureka_cat']);

        return $invelity_heureka_category;

    }

    private function setProduct($key, $heureka_cat, $availability)
    {

        $this->product = [];
        if ($key['variation_type'] == 'parent') {

            $variable_product = new WC_Product_Variable($key['id']);
            $variations = $variable_product->get_children();

            if ($variations) {
                foreach ($variations as $variation) {

                    $variable_product = new WC_Product_Variation($variation);
                    $string = wc_get_formatted_variation($variable_product->get_attributes());
                    $string = str_replace('</dd><dt>', '</dd>**<dt>', $string);
                    $string = strip_tags($string);
                    $string_arr = explode('**', $string);
                    $final_arr = [];


                    if ($string_arr) {
                        foreach ($string_arr as $s) {
                            $params_arr = explode(':', $s);

                            if (!empty($params_arr[1])) {
                                $final_arr[$params_arr[0]] = $params_arr[1];
                            }
                            if (!empty($params_arr[2])) {
                                $final_arr[$params_arr[0]] = $params_arr[2];
                            }
                        }
                    }

                    $params = [];
                    if ($final_arr) {
                        foreach ($final_arr as $key1 => $val) {
                            $params = [$key1 => $val,];
                        }
                    }

                    $this->product[] = [
                        'id' => $key['id'],
                        'title' => $key['title'],
                        'description' => htmlspecialchars($key['description']),
                        'url' => htmlspecialchars($key['link']),
                        'image' => htmlspecialchars($key['image']),
                        'heureka_category' => $heureka_cat,
                        'price' => $variable_product->get_price(),
                        'availability' => $availability,
                        'variation' => [
                            'variation_id' => $variation,
                            'parent_id' => $key['id'],
                            'variation_name' => $key['title'],
                            'variation_description' => $key['description'],
                            'variation_url' => $key['link'],
                            'variation_image' => $key['image'],
                            'heureka_category' => $heureka_cat,
                            'variation_price' => $variable_product->get_price(),
                            'variation_availability' => $availability,
                            'params' => (isset($params) ? $params : []),
                        ],
                    ];



                }
            }
            return $this->product;

        } else {

            $price = $key['sale_price'] ? $key['sale_price'] : $key['price'];
            $this->product[] = [
                'id' => $key['id'],
                'title' => $key['title'],
                'description' => htmlspecialchars($key['description']),
                'url' => htmlspecialchars($key['link']),
                'image' => htmlspecialchars($key['image']),
                'heureka_category' => $heureka_cat,
                'price' => $price,
                'availability' => $availability,

            ];
            return $this->product;
        }


    }

    public function getProductAvailability($product_availability, $data_availability)
    {


        if ($product_availability == 'in stock') {

            $availability = '0';

        } elseif ($data_availability == '1-3' && $product_availability !== 'in stock') {

            $availability = '3';

        } elseif ($data_availability == '4-7' && $product_availability !== 'in stock') {

            $availability = '7';

        } elseif ($data_availability == '8-14' && $product_availability !== 'in stock') {

            $availability = '14';

        } elseif ($data_availability == '15-30' && $product_availability !== 'in stock') {

            $availability = '30';

        } elseif ($data_availability == '31 and more' && $product_availability !== 'in stock') {

            $availability = '31';

        } else {

            $availability = ' ';

        }
        return $availability;


    }


    private function generateHeurekaXML($data)
    {

        $data['filename'] = preg_replace('#[^a-z0-9_]#', "", sanitize_text_field($data['filename']));
        $fileName = $data['filename'] . '.xml';

        $asXML = $this->xml->asXML();


        $upload_dir = wp_upload_dir();
        $user_url = $upload_dir['baseurl'] . '/product-feeds';
        $user_dirname = $upload_dir['basedir'];

        if (!file_exists($user_dirname . '/product-feeds')) {
            mkdir($user_dirname . '/product-feeds');
        }

        $feed_dir = $user_dirname . '/product-feeds';

        $file = $feed_dir . '/' . $fileName;

        if (!file_exists($file)) {
            $open = fopen($file, "w+");
            fwrite($open, $asXML);
            fclose($open);
        } else {
            file_put_contents($file, $asXML);
        }


        if (isset($_POST['filename']) && !empty($_POST['filename'])) {
            echo '<div class="notice notice-success">';
            echo '<h5>Feed was succesfully generated here ->';
            echo '<a href="' . $user_url . '/' . $fileName . '"><h3>' . $user_url . '/' . $fileName . '</h3></a>';
            echo '</h5>';
            echo '</div>';
            die();
        }

    }


}

class INVELITY_SIMPLE_XML extends SimpleXMLElement
{

    public function addChildWithCDATA($name, $value = null)
    {
        $new_child = $this->addChild($name);

        if ($new_child !== null) {
            $node = dom_import_simplexml($new_child);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }

        return $new_child;
    }

}
