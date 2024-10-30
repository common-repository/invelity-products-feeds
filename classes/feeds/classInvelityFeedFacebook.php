<?php

class InvelityFeedFacebook
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
            'price',
            'condition',
            'availability',
            'brand',
            'google_product_category',
        ], "\t");


        if (is_array($products->productsList) || is_object($products->productsList)) {

            foreach ($products->productsList as $key) {
                $get_google_term_result = $this->get_google_category($key['category_ids'], $data['product_cat']);

                $invelity_google_category = $get_google_term_result ? $get_google_term_result : sanitize_text_field($data['google_cat']);

                $brand_value = $this->brandForFacebook($key, $data);

                $google_value = $this->googleForFacebook($key, $invelity_google_category);

                if (isset($data['checkbox'])) {
                    if ($data['checkbox'] == 'on') {
                        if ($key['availability'] == 'in stock') {

                            $csv_data = $this->productsForFacebook($key, $data, $brand_value, $google_value);
                            fputcsv($csv, $csv_data, "\t");

                        } else {
                            continue;
                        }

                    } else {
                        $csv_data = $this->productsForFacebook($key, $data, $brand_value, $google_value);
                        fputcsv($csv, $csv_data, "\t");
                    }

                } else {

                    $csv_data = $this->productsForFacebook($key, $data, $brand_value, $google_value);
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


    public function get_google_category($googleCat, $product_cat)
    {
        $categories_details = $googleCat;
        $cat_id = '';

        if (is_array($categories_details)) {
            foreach ($categories_details as $cat_details) {
                if (is_array($product_cat)) {
                    if (in_array(strval($cat_details->term_id), $product_cat)) {
                        $cat_id = $cat_details->term_id;
                    }
                }
            }
        } else {
            $cat_id = $categories_details['term_id'];
        }
        $get_term = get_term_meta($cat_id, 'invelity_google_category', true);
        $get_term_result = "";
        if (is_array($get_term)) {
            foreach ($get_term as $term) {
                $get_term_result = $term;
            }
        } else {
            $get_term_result = $get_term;
        }

        return $get_term_result;

    }


    private function productsForFacebook($key, $data, $brand, $google)
    {

        $price = $key['sale_price'] ? $key['sale_price'] : $key['price'];

        $csv_data = [];
        $csv_data[] = $key['id'];
        $csv_data[] = $key['title'];
        $csv_data[] = clear_description($key['description']);
        $csv_data[] = $key['link'];
        $csv_data[] = $key['image'];
        $csv_data[] = $price;
        $csv_data[] = $key['condition'];
        $csv_data[] = $key['availability'];
        $csv_data[] = $brand;
        $csv_data[] = htmlspecialchars_decode($google);

        return $csv_data;
    }

    private function brandForFacebook($key, $data)
    {
        if (empty($key['brand'])) {
            $brand_value = sanitize_text_field($data['feed_brand']);
        } else {
            $brand_value = $key['brand'];
        }
        return $brand_value;

    }

    private function googleForFacebook($key, $invelity_google_category)
    {

        if (empty($key['google_category'])) {
            $google_value = $invelity_google_category;
        } else {
            $google_value = $key['google_category'];
        }

        return $google_value;
    }

}