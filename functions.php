
<?php
function create_menu_category_taxonomy() {
    $labels = array(
        'name' => _x( 'دسته بندی ها', 'taxonomy general name', 'textdomain' ),
        'singular_name' => _x( 'Menu Category', 'taxonomy singular name', 'textdomain' ),
        'search_items' =>  __( 'Search Menu Categories', 'textdomain' ),
        'all_items' => __( 'دسته بندی ها', 'textdomain' ),
        'parent_item' => __( 'Parent Menu Category', 'textdomain' ),
        'parent_item_colon' => __( 'Parent Menu Category:', 'textdomain' ),
        'popular_items' => null,
    
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'popular_items' => null,
    );

    register_taxonomy( 'menu_category', array( 'restaurant' ), $args );
}
add_action( 'init', 'create_menu_category_taxonomy', 0 );




//  add menu category taxonomy foreach menu item to انتخاب دسته بندی

// change icon to restaurant icon
function create_restaurant_cpt() {
    $args = array(
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'label'  => 'رستوران ها',
        'supports' => array('title', 'editor', 'author'),
        'menu_icon' => 'dashicons-store',
    );
    register_post_type('restaurant', $args);
}
add_action('init', 'create_restaurant_cpt');

function add_menu_items_meta_box() {
    add_meta_box(
        'menu_items_meta_box',
        'منو',
        'show_menu_items_meta_box',
        'restaurant',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_menu_items_meta_box');



function show_menu_items_meta_box($post) {
    wp_enqueue_media();
    $menu_items = get_post_meta($post->ID, 'menu_items', true);
    if (!is_array($menu_items)) {
        $menu_items = array();
    }
    ?>
    <div id="menu_items">
        <?php foreach ($menu_items as $i => $menu_item) : ?>
            <div class="menu-item">
                نام: <input type="text" name="menu_item_name[]" value="<?php echo esc_attr($menu_item['name']); ?>" class="menu-item-input"><br>
                توضیحات: <textarea name="menu_item_description[]" class="menu-item-textarea"><?php echo esc_textarea($menu_item['description']); ?></textarea><br>
                قیمت: <input type="text" name="menu_item_price[]" value="<?php echo esc_attr($menu_item['price']); ?>" class="menu-item-input"><br>
                <input type="hidden" name="menu_item_image_id[]" value="<?php echo esc_attr($menu_item['image_id']); ?>">
                <!-- show selected category for this menu items and a list to select from categories -->
                <img src="<?php echo wp_get_attachment_url($menu_item['image_id']); ?>" class="menu-item-image">
                <div class="buttons-container">
                    <button type="button" class="custom-button upload_image_button">انتخاب تصویر</button>
                    <button type="button" class="custom-button move_up_button">انتقال به بالا</button>
                    <button type="button" class="custom-button move_down_button">انتقال به پایین</button>
                    <button type="button" class="custom-button delete_menu_item_button">حذف</button>
                    <select name="menu_item_category[]">
                        <option value="">انتخاب دسته بندی</option>
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'menu_category',
                            'hide_empty' => false,
                        ));

                        // filter the categories and just show which they have checked checkbox
                        $selected_category = $menu_item['category'];

                        foreach ($categories as $category) {
                            $selected = $category->term_id == $selected_category ? 'selected' : '';
                            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                        }
                        
                        ?>
                    </select>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="custom-button" type="button" id="add_menu_item">افزودن آیتم جدید </button>

    <script>
        document.getElementById('add_menu_item').addEventListener('click', function() {
            var menuItem = document.createElement('div');
            menuItem.className = 'menu-item';
            menuItem.innerHTML = 'نام: <input type="text" name="menu_item_name[]" class="menu-item-input"><br>توضیحات: <textarea name="menu_item_description[]" class="menu-item-textarea"></textarea><br>قیمت: <input type="text" name="menu_item_price[]" class="menu-item-input"><br><input type="hidden" name="menu_item_image_id[]"><img src="" class="menu-item-image"><div class="buttons-container"><button type="button" class="custom-button upload_image_button">انتخاب تصویر</button><button type="button" class="custom-button move_up_button">انتقال به بالا</button><button type="button" class="custom-button move_down_button">انتقال به پایین</button><button type="button" class="custom-button red delete_menu_item_button">حذف</button><select name="menu_item_category[]"><option value="">انتخاب دسته بندی</option><?php foreach ($categories as $category) { echo '<option value="' . $category->term_id . '">' . $category->name . '</option>'; } ?></select></div>';
            document.getElementById('menu_items').appendChild(menuItem);
        });

        jQuery(document).on('click', '.upload_image_button', function(e) {
            e.preventDefault();
            var button = jQuery(this);
            var custom_uploader = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                console.log(attachment);
                button.parent().prev().attr('src', attachment.url);
                console.log(button.parent().prev())
                button.parent().prev().prev().val(attachment.id);

            }).open();
        });

        jQuery(document).on('click', '.delete_menu_item_button', function(e) {
            e.preventDefault();
            jQuery(this).parent().parent().remove();
        });

        jQuery(document).on('click', '.move_up_button', function(e) {
            e.preventDefault();
            var menuItem = jQuery(this).closest('.menu-item');
            menuItem.insertBefore(menuItem.prev());
        });

        jQuery(document).on('click', '.move_down_button', function(e) {
            e.preventDefault();
            var menuItem = jQuery(this).closest('.menu-item');
            menuItem.insertAfter(menuItem.next());
        });
    </script>
    <?php
}

function save_menu_items($post_id) {
    if (array_key_exists('menu_item_name', $_POST)) {
        $menu_items = array();
        $names = $_POST['menu_item_name'];
        $descriptions = $_POST['menu_item_description'];
        $prices = $_POST['menu_item_price'];
        $image_ids = $_POST['menu_item_image_id'];
        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $menu_items[] = array(
                'name' => $names[$i],
                'description' => $descriptions[$i],
                'price' => $prices[$i],
                'image_id' => $image_ids[$i],
            );
        }

        // save selected category(taxonomy) for each menu item
        $categories = $_POST['menu_item_category'];
        $count = count($categories);
        for ($i = 0; $i < $count; $i++) {
            if (isset($menu_items[$i])) { // Check if the menu item exists
                $menu_items[$i]['category'] = $categories[$i];
            }
        }

        // Validate the menu items
        $errors = array();
        foreach ($menu_items as $item) {
            if (empty($item['name'])) {
                $errors[] = 'لطفا نامی برای آیتم مورد نظر وارد کنید';
            }
            // if (empty($item['description'])) {
            //     $errors[] = '';
            // }
            // if (empty($item['price'])) {
            //     $errors[] = 'Price is required for all menu items.';
            // }
            // if (empty($item['image_id'])) {
            //     $errors[] = 'Image is required for all menu items.';
            // }
            // if (empty($item['category'])) {
            //     $errors[] = 'Category is required for all menu items.';
            // }
        }

        // If there are errors, display the error message and return
        if (!empty($errors)) {
            $error_message = implode('<br>', $errors);
            wp_die($error_message, 'Error');
        }

        // Update the post meta only once
        update_post_meta(
            $post_id,
            'menu_items',
            $menu_items
        );
    }else{        update_post_meta(
            $post_id,
            'menu_items',
            []
        );
    }
}

add_action('save_post', 'save_menu_items');


function add_restaurant_meta_boxes() {
    add_meta_box('restaurant_description', 'توضیحات', 'restaurant_description_callback', 'restaurant', 'normal', 'high');
    add_meta_box('restaurant_icon', 'لوگو', 'restaurant_icon_callback', 'restaurant', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_restaurant_meta_boxes');

function restaurant_description_callback($post) {
    wp_nonce_field(basename(__FILE__), 'restaurant_nonce');
    $stored_meta = get_post_meta($post->ID);
    ?>
    <textarea name="meta-description" style="width:100%;"><?php if (isset($stored_meta['meta-description'])) echo $stored_meta['meta-description'][0]; ?></textarea>
    <?php
}

function restaurant_icon_callback($post) {
    wp_nonce_field(basename(__FILE__), 'restaurant_nonce');
    $stored_meta = get_post_meta($post->ID);
    $icon_id = isset($stored_meta['meta-icon']) ? $stored_meta['meta-icon'][0] : '';
    $icon_url = wp_get_attachment_url($icon_id);
    ?>
    <input type="hidden" name="meta-icon" id="meta-icon" value="<?php echo esc_attr($icon_id); ?>">
    <div class="flex col-gap-8 items-center">
    <button class="custom-button" type="button" id="meta-icon-button">بارگذاری لوگو</button>
    <?php if ($icon_url) : ?>
        <img src="<?php echo esc_url($icon_url); ?>" style="max-width:100px;" id="meta-icon-preview">
    <?php endif; ?>
    </div>
    <script>
        document.getElementById('meta-icon-button').addEventListener('click', function() {
            var custom_uploader = wp.media({
                title: 'Select Icon',
                button: {
                    text: 'Use this icon'
                },
                multiple: false
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                document.getElementById('meta-icon').value = attachment.id;
                var iconPreview = document.getElementById('meta-icon-preview');
                if (iconPreview) {
                    iconPreview.src = attachment.url;
                } else {
                    var previewImage = document.createElement('img');
                    previewImage.id = 'meta-icon-preview';
                    previewImage.src = attachment.url;
                    previewImage.style.maxWidth = '100px';
                    document.getElementById('meta-icon-button').insertAdjacentElement('afterend', previewImage);
                }
                }).open();
        });
    </script>
    <?php
}
// save icon and description
function save_restaurant_meta($post_id) {
    if (!isset($_POST['restaurant_nonce']) || !wp_verify_nonce($_POST['restaurant_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if ($_POST['post_type'] == 'restaurant') {
        if (current_user_can('edit_post', $post_id)) {
            update_post_meta($post_id, 'meta-icon', sanitize_text_field($_POST['meta-icon']));
            update_post_meta($post_id, 'meta-description', sanitize_text_field($_POST['meta-description']));
        }
    }
}
add_action('save_post', 'save_restaurant_meta');


function remove_content_editor() {
    remove_post_type_support('restaurant', 'editor');
}
add_action('admin_init', 'remove_content_editor');

// inste

function add_menu_items_to_rest_api($object, $field_name, $request) {
    $menu_items = get_post_meta($object['id'], 'menu_items', true);
    if (is_array($menu_items)) {
        foreach ($menu_items as &$menu_item) {
            $menu_item['image_url'] = wp_get_attachment_url($menu_item['image_id']);
        }
    }
    return $menu_items;
}
add_action('rest_api_init', function () {
    register_rest_field('restaurant', 'menu_items', array(
        'get_callback' => 'add_menu_items_to_rest_api',
        'schema' => null,
    ));
});

// add description and icon to rest api
function add_restaurant_meta_to_rest_api($object, $field_name, $request) {
    return array(
        'description' => get_post_meta($object['id'], 'meta-description', true),
        'icon' => wp_get_attachment_url(get_post_meta($object['id'], 'meta-icon', true)),
    );
}
add_action('rest_api_init', function () {
    register_rest_field('restaurant', 'meta', array(
        'get_callback' => 'add_restaurant_meta_to_rest_api',
        'schema' => null,
    ));
});




function load_custom_admin_style() {
    wp_register_style('custom_admin_css', get_template_directory_uri() . '/admin-style.css', false, '1.0.0');
    wp_enqueue_style('custom_admin_css');
}
add_action('admin_enqueue_scripts', 'load_custom_admin_style');


function add_menu_category_to_restaurant() {
    register_rest_field('restaurant', 'menu_category', array(
        'get_callback' => function ($object, $field_name, $request) {
            $categories = wp_get_post_terms($object['id'], 'menu_category');
            // add image to each category
            foreach ($categories as &$category) {
                // use get_taxonomy_image plugin to get category image and add it to the category object
                $category->image = get_taxonomy_image($category->term_id);
            }
            return $categories;
        },
        'schema' => null,
    ));
}
add_action('rest_api_init', 'add_menu_category_to_restaurant');




//  add restaurant owner role which can only edit and delete their own restaurant


// remove resurant owener role
function remove_restaurant_owner_role() {
    remove_role('restaurant_owner');
}
add_action('init', 'remove_restaurant_owner_role');

function add_restaurant_owner_role() {
    add_role('restaurant_owner', 'Restaurant owneer', array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'edit_published_posts' => true,
        'delete_published_posts' => true,
        'upload_files' => true,
        'publish_posts' => false
    ));
}
add_action('init', 'add_restaurant_owner_role');

// remove seeing comments in admin panel for restaurant owner
function remove_comments_menu_for_restaurant_owner() {
    if (current_user_can('restaurant_owner')) {
        remove_menu_page('edit-comments.php');
        remove_menu_page('profile.php');
        remove_menu_page('upload.php');
        remove_menu_page('edit.php');
        remove_menu_page('post-new.php');
        remove_menu_page('index.php');
        remove_menu_page('tools.php');
        remove_menu_page('post-new.php?post_type=restaurant');
        
    }
}
add_action('admin_menu', 'remove_comments_menu_for_restaurant_owner');

// remove seeing other restaurant in admin panel for restaurant owner
function remove_other_restaurants_for_restaurant_owner($query) {
    if (is_admin() && current_user_can('restaurant_owner')) {
        global $user_ID;
        $query->set('author', $user_ID);
    }
}
add_action('pre_get_posts', 'remove_other_restaurants_for_restaurant_owner');

// remove the ability to see other restaurant in admin panel for restaurant owner
// function remove_other_restaurants_links_for_restaurant_owner($query) {
//     if (is_admin() && current_user_can('restaurant_owner')) {
//         global $submenu;
//         unset($submenu['edit.php?post_type=restaurant'][5]);
//     }
// }
// add_action('admin_menu', 'remove_other_restaurants_links_for_restaurant_owner');










//  change url of preview


// add spec style for restuarant_owner admin panel 
function load_custom_admin_style_for_restaurant_owner() {
    if (current_user_can('restaurant_owner')) {
        wp_register_style('custom_admin_css_res_owner', get_template_directory_uri() . '/restaurant_owner_style.css', false, '1.0.0');
        wp_enqueue_style('custom_admin_css_res_owner');
    }
}
add_action('admin_enqueue_scripts', 'load_custom_admin_style_for_restaurant_owner');



// wp-admin/post-new.php?post_type=restaurant


// remove this page from admin of res owner wp-admin/post-new.php?post_type=restaurant


// in admin panel every previewing website button is i want to point to that user website, not just post link , there is a home button in admin panel that i want to point to that user website, not just home page of website




// add a button to admin panel to go to user website


// add a button to admin panel to go to user website
// function add_go_to_website_button() {
//     if (current_user_can('restaurant_owner')) {
//         $user_url = wp_get_current_user()->user_url;
//         echo '<div class="visit-container"><a href="' . $user_url . '" class="visit">مشاهده منو</a></div>';
// }}
// add_action('admin_notices', 'add_go_to_website_button');


// change preview link of resrtaunt post type
function change_preview_link($link) {
    if (current_user_can('restaurant_owner')) {
        $user_url = wp_get_current_user()->user_url;
        return $user_url;
    }
    return $link;
}
add_filter('preview_post_link', 'change_preview_link');

// change text of preview button in admin panel
function change_preview_button_text($translation, $text, $domain) {
    if (current_user_can('restaurant_owner')) {
        if ($text == 'Preview Changes') {
            return 'مشاهده منو';
        }
        if ($text == 'Edit Post') {
            return 'ویرایش اطلاعات رستوران';
        }
        
    }
    return $translation;
}
add_filter('gettext', 'change_preview_button_text', 10, 3);




// redirect after login to edit restaurant page 
function redirect_after_login($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('restaurant_owner', $user->roles)) {
            return admin_url('edit.php?post_type=restaurant');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_after_login', 10, 3);



function redirect_to_backend() {
    if ( is_admin() ) {
        return;
    }

    global $wp;
    $current_url = home_url( add_query_arg( array(), $wp->request ) );

    // Exclude the REST API from the redirection
    if ( strpos( $current_url, '/wp-json/' ) !== false || strpos( $current_url, '/?rest_route=/' ) !== false ) {
        return;
    }
    // redirect to edit restaurant page
    if (current_user_can('restaurant_owner')) {
        wp_redirect(admin_url('edit.php?post_type=restaurant'));
        exit;
    }
    exit;
}
add_action( 'template_redirect', 'redirect_to_backend' );

// change the url of preview in the list of restaurant not in the page of that
function change_preview_link_in_list($actions, $post) {
    if (current_user_can('restaurant_owner')) {
        $user_url = wp_get_current_user()->user_url;
        $actions['view'] = '<a href="' . $user_url . '" target="_blank" aria-label="نمایش منو">نمایش منو</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'change_preview_link_in_list', 10, 2);