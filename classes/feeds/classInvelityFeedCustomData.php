<?php


class InvelityFeedCustomData
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
            'ID',
            'Item title',
            'Final URL',
            'Image URL',
            'Item description',
            'Item category',
            'Price',
        ], "\t");


        if (is_array($products->productsList) || is_object($products->productsList)) {

            foreach ($products->productsList as $key) {

                if (isset($data['checkbox'])) {
                    if ($data['checkbox'] == 'on') {
                        if ($key['availability'] == 'in stock') {

                            $csv_data = $this->productforCustomDataFeed($key);
                            fputcsv($csv, $csv_data, "\t");

                        } else {
                            continue;
                        }

                    } else {

                        $csv_data = $this->productforCustomDataFeed($key);
                        fputcsv($csv, $csv_data, "\t");
                    }
                } else {

                    $csv_data = $this->productforCustomDataFeed($key);
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


    public function productforCustomDataFeed($key)
    {

        $price = $key['sale_price'] ? $key['sale_price'] : $key['price'];

        $csv_data = [];
        $csv_data[] = $key['id'];
        $csv_data[] = $key['title'];
        $csv_data[] = $key['link'];
        $csv_data[] = $key['image'];
        $csv_data[] = clear_description($key['description']);
        $csv_data[] = $key['product_type'];
        $csv_data[] = $price . " " . get_option('woocommerce_currency');

        return $csv_data;

    }

}