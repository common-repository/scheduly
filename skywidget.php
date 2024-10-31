<?php
/**
 * Plugin Name: Scheduly - Booking Widget
 * Plugin URI: https://scheduly.com
 * Description: Let clients book appointments and make payments through your website. Lightweight and powerful.
 * Version: 2.0
 * Author: Scheduly
 * Author URI: https://scheduly.com/
 * Text Domain: scheduly.com
 * Domain Path: scheduly.com
 * License: GPL
 */
/*
  Scheduly
  Copyright (C) 2024, Scheduly <support@scheduly.com>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('SCHEDULY_PLUGIN_BOOKING_DIR', str_replace('\\', '/', dirname(__FILE__)));

if (!class_exists('schedulyWidgetBookings')) {
    
    
    class schedulyWidgetBookings  {

        function __construct() {
            add_action('init', array(&$this, 'schedulyInit'));
            add_action('admin_init', array(&$this, 'schedulyAdminInit'));
            add_action('admin_menu', array(&$this, 'schedulyAdminMenu'));
            add_action('wp_footer', array(&$this, 'schedulyFooter'));
          
        }

        function schedulyInit() {
            load_plugin_textdomain('scheduly', false, dirname(plugin_basename(__FILE__)) . '/lang');

            // Registra e incluye el archivo CSS
            wp_register_style('scheduly-styles', plugin_dir_url(__FILE__) . 'inc/style.css');
            wp_enqueue_style('scheduly-styles');

            // Añade un script para manipular el contenido del iframe después de cargar
             wp_enqueue_script('scheduly-iframe-script', plugin_dir_url(__FILE__) . 'inc/iframe-script.js', array('jquery'), null, true);
        }

        function schedulyAdminInit() {

            // register settings for sitewide script
            register_setting('scheduly_settings_group', 'schedulyInsertFooter', 'trim');
            register_setting('scheduly_settings_group', 'schedulyPluginActive');

            register_setting('scheduly_shortcodes_group', 'scheduly_links');

            if(isset($_POST) && !empty($_POST['schedulyInsertFooter'])) {
                $schedulyInsertFooter = sanitize_text_field($_POST['schedulyInsertFooter']);
                $base_ruta = "https://scheduly.com/" ;
                    $url = $base_ruta.'api/V_1/plugin/checkClientId/'.$schedulyInsertFooter.'';
                    $response = wp_remote_get($url, 
                    array(
                        'blocking' => true,
                        'redirection' => '10',
                        'timeout' => '30',
                        'method' => "GET",
                        'headers' => array(
                            'cache-control' => "no-cache",
                            'content-type' => "multipart/form-data"
                        ),
                        ));
    
                    $body = wp_remote_retrieve_body($response);
                    $result = json_decode($body,true);
                    if(!empty($result))
                    {
                        $error_code = $result['error_code'];
                        $status = $result['status'];
                        
                        if($error_code == 0 && $status == 'false'){
                            wp_redirect(admin_url('admin.php?page=scheduly/skywidget.php&update=0') );
                            exit;
                        }
                    }
            }
            // add meta box to all post types
            foreach (get_post_types('', 'names') as $type) 
            {
                if (isset($_POST) && !empty($_POST['schedulyInsertFooter'])) {
                    $schedulyInsertFooter = sanitize_text_field($_POST['schedulyInsertFooter']);
                    add_meta_box('shfs_all_post_meta', esc_html( __('Insert Script to &lt;head&gt;', 'scheduly')), 'shfs_meta_setup', $type, 'normal', 'high');
                    add_action('save_post', 'schedulyPostMetaSave');
                }
            }
        }

        // adds menu item to admin dashboard
        function schedulyAdminMenu() {
            $page = add_menu_page(__('Scheduly Widget', 'scheduly'), __('Scheduly Widget', 'scheduly'), 'manage_options', __FILE__, array(&$this, 'schedulyOptionPanel'),'dashicons-calendar-alt');
        }

        function schedulyFooter() {
         
            if (get_option('schedulyPluginActive') && !is_admin() && !is_feed() && !is_robots() && !is_trackback()) {
                $textScheduly = get_option('schedulyInsertFooter', '');
                $textScheduly = convert_smilies($textScheduly);
                $textScheduly = do_shortcode($textScheduly);

                if ($textScheduly != '') 
                {
                    ?>
                    <script type="text/javascript">
                        (function () {
                            var wgt = document.createElement("script");
                            wgt.type = "text/javascript";
                            wgt.async = true;
                            wgt.id = "widgetJs";
                            wgt.src = "https://scheduly.com/themes/widget/js/widget.js?clientId=<?= $textScheduly; ?>";
                            var s = document.getElementsByTagName("script")[0];
                            s.parentNode.insertBefore(wgt, s);
                        })();
                    </script>
                    <?php
                }
            }else{
    
                ?>
                <div style="display: none;">
                    <script type="text/javascript">
                        (function () {
                            var wgt = document.createElement("script");
                            wgt.type = "text/javascript";
                            wgt.async = true;
                            wgt.id = "widgetJs";
                            wgt.src = "https://scheduly.com/themes/widget/js/widget.js?clientId=<?= $textScheduly; ?>";
                            var s = document.getElementsByTagName("script")[0];
                            s.parentNode.insertBefore(wgt, s);
                        })();
                    </script>
                </div>
                <?php
            }
        }

        function schedulyOptionPanel() {
            // Load options page
            require_once(SCHEDULY_PLUGIN_BOOKING_DIR . '/inc/options.php');
        }

    }

    
    function schedulyPostMetaSave($schedulyPostId) {
        
        // check user permissions
        if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
            if (!current_user_can('edit_page', $schedulyPostId))
                return $schedulyPostId;
        } else {
            if (!current_user_can('edit_post', $schedulyPostId))
                return $schedulyPostId;
        }

        $schedulyCurrentData = get_post_meta($schedulyPostId, '_inpost_head_script', TRUE);
        $schedulyNewData = sanitize_text_field($_POST['_inpost_head_script']);
        schedulyPostMetaClean($schedulyNewData);
        
        if ($schedulyCurrentData) {
            if (is_null($schedulyNewData))
                delete_post_meta($schedulyPostId, '_inpost_head_script');
            else
                update_post_meta($schedulyPostId, '_inpost_head_script', $schedulyNewData);
        } elseif (!is_null($schedulyNewData)) {
            add_post_meta($schedulyPostId, '_inpost_head_script', $schedulyNewData, TRUE);
        }
        
        return $schedulyPostId;
    }

    function schedulyPostMetaClean(&$arr) {

        if (is_array($arr)) {

            foreach ($arr as $i => $v) {

                if (is_array($arr[$i])) {
                    schedulyPostMetaClean($arr[$i]);

                    if (!count($arr[$i])) {
                        unset($arr[$i]);
                    }
                } else {

                    if (trim($arr[$i]) == '') {
                        unset($arr[$i]);
                    }
                }
            }

            if (!count($arr)) {
                $arr = NULL;
            }
        }
    }

    // display default admin notice
    function schedulyYourAdminNoticesAction() {
        if($_GET['update'] == '0' && $_GET['settings-updated'] != 'true')
        {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Sorry, Client Id does not match. Please try again.', 'shapeSpace'); ?></p>
            </div>
            <?php
        }
    }
    add_action('admin_notices', 'schedulyYourAdminNoticesAction');


    register_uninstall_hook(__FILE__, 'schedulyMyPluginRemoveDatabase');
    function schedulyMyPluginRemoveDatabase() {
        global $wpdb;
        delete_option("schedulyInsertFooter");
    }

    function scheduly_register_settings() {
        register_setting('scheduly', 'scheduly_links');
        register_setting('scheduly', 'scheduly_link_main'); 
    }
    
    add_action('admin_init', 'scheduly_register_settings');

    function scheduly_shortcode_handler($atts) {
        // $atts = shortcode_atts(array('name' => ''), $atts, 'scheduly_iframe');
        $atts = shortcode_atts(array('name' => '', 'height' => '230'), $atts, 'scheduly_iframe');
        $links = get_option('scheduly_links', array());
        foreach ($links as $link) {
            if ($link['name'] == $atts['name']) {
                $height_attribute = '';

                if (!empty($atts['height'])) {
                    $height_attribute = "height='{$atts['height']}'";
                }
    
                // return "<div class='embedded-iframe-container'> <iframe src='{$link['url']}' allowfullscreen style='height: 628.498px;'></iframe></div>"; 
                return "
                <div class='iframe-container-sch' style='min-width: 100%;'>
                    <iframe id='dynamic-iframe' class='iframe-responsivo-sch' src='{$link['url']}'
                    style='
                    min-width: 100%; 
                    display: block;
                    overflow: hidden;
                ' $height_attribute ></iframe>
                </div>
            "; 
            }
        }
        return ''; 
    }
    
    add_shortcode('scheduly_iframe', 'scheduly_shortcode_handler');
    
    function scheduly_shortcode_all_handler() {
        $links = get_option('scheduly_links', array());
        $output = '';
        foreach ($links as $link) {
            $output .= "<iframe src='{$link['url']}' allowfullscreen></iframe>\n";
        }
        return $output;
    }
    
    add_shortcode('shortcode_all', 'scheduly_shortcode_all_handler');

    $scedulyHeaderAndFooterScripts = new schedulyWidgetBookings();
}

