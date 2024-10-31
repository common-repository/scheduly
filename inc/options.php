<?php /**
 * Plugin Options page
 *
 * @package    Scheduly
 * @author     Scheduly <support@scheduly.com>
 * @copyright  Copyright (c) 2021, Scheduly
 * @link       https://scheduly.com/
 * @license    GPL
 */ ?>
<?php 
wp_register_style( 'style_css', plugins_url('style.css',__FILE__ )); 
wp_enqueue_style('style_css');
?>
<div class="wrap">
    <?php
        // Define the active tab by the $_GET parameter
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
    ?>
    <h2><?php _e('Connect to your Scheduly account', 'scheduly'); ?> </h2>
    <h2 class="nav-tab-wrapper">
    <a href="?page=scheduly/skywidget.php&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'scheduly'); ?></a>
    <a href="?page=scheduly/skywidget.php&tab=shortcodes" class="nav-tab <?php echo $active_tab == 'shortcodes' ? 'nav-tab-active' : ''; ?>"><?php _e('Shortcodes', 'scheduly'); ?></a>
    </h2>
    <hr />

    <?php
    // Check if the 'settings-updated' GET parameter is set and true
    if( isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings updated successfully!', 'scheduly'); ?></p>
        </div>
    <?php endif; ?>
    <!-- Settings Tab Content -->

    <?php
    // Display content based on the selected tab
    if( $active_tab == 'settings' ): ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="postbox">
                    <div class="inside">
                        <?php if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ) { ?>
                            <div class="notice notice-success is-dismissible">
                                <p><?php _e('Client Id connected!.', 'shapeSpace'); ?></p>
                            </div>
                        <?php } ?>

                        <form class="validate" name="dofollow" action="options.php" method="post">

                            <?php settings_fields('scheduly_settings_group'); ?>

                            <h3 class="shfs-labels footerlabel" for="schedulyInsertFooter"><?php _e('Please enter your partner ID:', 'scheduly'); ?></h3>
                            <div class="form-required term-name-wrap">
                                <input style="width:40%;" rows="10" cols="57" id="schedulyInsertFooter" name="schedulyInsertFooter" value="<?php echo esc_html(get_option('schedulyInsertFooter')); ?>" aria-required="true" required>
                            </div>
                            <br>
                            <div class="form-required term-name-wrap">
                                <input type="checkbox" class="switch-button__checkbox" id="schedulyPluginActive" name="schedulyPluginActive" value="1" <?php checked(get_option('schedulyPluginActive', 1)); ?> />
                                <label for="schedulyPluginActive"><?php _e('Activate the floating button.', 'scheduly'); ?></label>
                            </div>

                            

                             <p class="submit">
                                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save', 'scheduly'); ?>" />
                            </p>
                            <p><?php _e('- To get a partner ID you need a Scheduly account. You can register for a free account at <a target="_blank" href="https://scheduly.com/signup">scheduly.com</a>', 'scheduly'); ?></p> 
                            <p><?php _e('- To get a partner ID <a target="_blank" href="https://scheduly.com/admin"><b>login</b></a> to your Scheduly Admin Panel -> Settings -> Integrations', 'scheduly'); ?></p>
                            <p><?php _e('- For more information see our <a target="_blank" href="https://scheduly.com/help"><b>Help Center</b></a>', 'scheduly'); ?></p>
                        </form>
                    </div>
                </div>
            </div>            
        </div>
    </div>

    <?php elseif( $active_tab == 'shortcodes' ): ?>
        <!-- Shortcodes Tab Content -->
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
            <div class="postbox">
                    <div class="inside">
                        
                        <form class="validate" name="dofollow" action="options.php" method="post">
                            <?php settings_fields('scheduly_shortcodes_group'); ?>
                            <br>
                            <!-- Title for categories -->
                            <h2><?php _e('Category', 'scheduly'); ?></h2>
                            <div id="scheduly-links-wrapper">
                                <?php
                                $links = get_option('scheduly_links', array());
                                if (!empty($links)) {
                                    foreach ($links as $index => $link_data) {
                                        ?>
                                        <div class="scheduly-link-row">
                                            <input type="text" name="scheduly_links[<?php echo $index; ?>][name]" value="<?php echo esc_attr($link_data['name']); ?>" placeholder="Nombre" style="width: 15%;" />
                                            <input type="text" name="scheduly_links[<?php echo $index; ?>][url]" value="<?php echo esc_url($link_data['url']); ?>" placeholder="Link" style="width: 38%;" />
                                            <input type="text" class="shortcode-display" name="scheduly_links[<?php echo $index; ?>][shortcode]" value="[scheduly_iframe name='<?php echo esc_attr($link_data['name']); ?>' height='230' ]" readonly style="width: 25%;" />
                                            
                                            <button type="button" class="copy-button">Copy</button>
                                            <button type="button" class="remove-row">X</button>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="scheduly-link-row">
                                        <input type="text" name="scheduly_links[0][name]" value="" placeholder="Nombre" />
                                        <input type="text" name="scheduly_links[0][url]" value="" placeholder="Link" />
                                        <input type="text" class="shortcode-display" name="scheduly_links[0][shortcode]" value="" readonly />
                                        <button type="button" class="remove-row">X</button>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        <button id="add-new-url" type="button">+</button>

                            

                             <p class="submit">
                                <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Create Shortcode & Save', 'scheduly'); ?>" />
                            </p>
                            <p><?php _e('- To get a partner ID you need a Scheduly account. You can register for a free account at <a target="_blank" href="https://scheduly.com/signup">scheduly.com</a>', 'scheduly'); ?></p> 
                            <p><?php _e('- To get a partner ID <a target="_blank" href="https://scheduly.com/admin"><b>login</b></a> to your Scheduly Admin Panel -> Settings -> Integrations.', 'scheduly'); ?></p>
                           
                            <p><?php _e('- For more information see our <a target="_blank" href="https://scheduly.com/help"><b>Help Center</b></a>', 'scheduly'); ?></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var linkIndex = <?php echo count($links); ?>;
        $('#add-new-url').click(function() {
            var newRow = '<div class="scheduly-link-row">' +
                '<input type="text" name="scheduly_links[' + linkIndex + '][name]" value="" placeholder="Nombre" />' +
                '<input type="text" name="scheduly_links[' + linkIndex + '][url]" value="" placeholder="Link" />' +
                '<input type="text" class="shortcode-display" name="scheduly_links[' + linkIndex + '][shortcode]" value="" readonly />' +
                '<button type="button" class="remove-row">X</button>' +
                '</div>';
            $('#scheduly-links-wrapper').append(newRow);
            linkIndex++;
        });

        // Delegated event to capture clicks on buttons that may not yet exist
        $('#scheduly-links-wrapper').on('click', '.remove-row', function() {
            $(this).closest('.scheduly-link-row').remove();
        });
    });
</script>
<script>
    // Function to copy shortcode field content to clipboard
    function copyToClipboard(inputElement) {
        inputElement.select();
        document.execCommand('copy');
    }
    
    document.querySelectorAll('.copy-button').forEach(function(button) {
        button.addEventListener('click', function() {
            var shortcodeInput = this.previousElementSibling; 
            copyToClipboard(shortcodeInput);
        });
    });
</script>