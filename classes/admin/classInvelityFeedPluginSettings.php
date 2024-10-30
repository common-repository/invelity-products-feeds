<?php

class InvelityFeedPluginSettings
{


    private $launcher;
    public $data;


    public function __construct(InvelityProductsFeeds $launcher)
    {
        // var_dump('InvelityFeedPluginSettings');

        $this->launcher = $launcher;
        if (is_admin()) {

            add_action('admin_menu', array($this, 'add_plugin_page'));


            add_action(
                'product_cat_add_form_fields',
                'custom_invelity_feed_taxonomy_add_new_meta_field',
                10,
                1
            );
            add_action(
                'product_cat_edit_form_fields',
                'custom_invelity_feed_taxonomy_edit_meta_field',
                10,
                1
            );

            add_action('edited_product_cat', 'custom_invelity_feed_save_taxonomy_custom_meta', 10, 1);
            add_action('create_product_cat', 'custom_invelity_feed_save_taxonomy_custom_meta', 10, 1);

            if (isset($_GET['page'])) {
                if ($_GET['page'] == 'invelity-products-feeds' || $_GET['page'] == 'invelity-products-manage-feeds') {
                    add_action('admin_print_scripts', array($this, 'enqueue_invelity_feeds_assets'));
                    add_action('admin_print_styles', array($this, 'enqueue_invelity_feeds_styles'));
                }
            }
        }


    }

    public function enqueue_invelity_feeds_styles()
    {
        wp_enqueue_style('feed_css', plugin_dir_url(__FILE__) . '../../assets/css/feed-admin.css');
    }

    public function enqueue_invelity_feeds_assets()
    {
        wp_enqueue_style(
            'bootstrapcss',
            '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
        );
        wp_enqueue_script(
            'bootstrapjs',
            '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
            array('jquery'),
            '2.0',
            true
        );

        wp_enqueue_style(
            'multiselectcss',
            '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css'
        );
        wp_enqueue_script(
            'multiselect',
            '//cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.js',
            array('jquery'),
            '2.0',
            true
        );

        wp_register_style(
            'jquery-ui-styles',
            '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'
        );
        wp_enqueue_style('jquery-ui-styles');

        wp_register_style(
            'jquery-select2-styles',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'
        );
        wp_enqueue_style('jquery-select2-styles');

        wp_enqueue_script(
            'jquery-ui-script',
            'https://code.jquery.com/ui/1.12.0/jquery-ui.min.js',
            array('jquery'),
            '1.12',
            true
        );
        wp_enqueue_script(
            'jquery-select2-script',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js',
            array('jquery'),
            '4.0.6',
            true
        );

        wp_register_script(
            'my-autocomplete',
            plugin_dir_url(__FILE__) . '../../assets/css/feed-admin.js',
            array('jquery', 'jquery-ui-autocomplete'),
            '1.0',
            true
        );

        wp_localize_script(
            'my-autocomplete',
            'MyAutocomplete',
            array('url' => admin_url('admin-ajax.php'))
        );
        wp_enqueue_script('my-autocomplete');

        wp_enqueue_script(
            'feeds_js',
            plugin_dir_url(__FILE__) . '../../assets/js/feed-admin.js',
            array('jquery'),
            '1.0',
            true
        );
        //wp_enqueue_style( 'editable_select_css', '//rawgithub.com/indrimuska/jquery-editable-select/master/dist/jquery-editable-select.min.css' );

    }


    public function add_plugin_page()
    {
        add_submenu_page(
            'invelity-plugins',
            __('Generate Feeds', $this->launcher->getPluginSlug()),
            __('Generate Feeds', $this->launcher->getPluginSlug()),
            'manage_options',
            'invelity-products-feeds',
            array($this, 'wp_feed_create_generate_feed')
        );

        add_submenu_page(
            'invelity-plugins',
            __('Manage Feeds', $this->launcher->getPluginSlug()),
            __('Manage Feeds', $this->launcher->getPluginSlug()),
            'manage_options',
            'invelity-products-manage-feeds',
            array($this, 'wp_feed_create_manage_feed')
        );
    }

    public function wp_feed_create_generate_feed()
    {

        new PluginSettingsGenerateFeedPage($this);
    }

    public function wp_feed_create_manage_feed()
    {

        new PluginSettigsManageFeedPage($this);
    }


    public static function getRemoteAd()
    {
        $invelityIkrosInvoicesad = get_transient('invelity-ikros-invoices-ad');
        if (!$invelityIkrosInvoicesad) {
            $query = esc_url_raw(add_query_arg(
                array(),
                'https://licenses.invelity.com/plugins/invelity-ikros-invoices/invelityad.json'
            ));
            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));
            $response = wp_remote_retrieve_body($response);
            set_transient('invelity-ikros-invoices-ad', $response, 86400);/*Day*/
//            set_transient('invelity-ikros-invoices-ad', $response, 300);/*5 min*/
            $invelityIkrosInvoicesad = $response;
        }

        return json_decode($invelityIkrosInvoicesad, true);

    }


}