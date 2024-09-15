<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @package Astra Child
 */

/**
 * Enqueue the parent theme styles and the child theme styles
 */
function astra_child_enqueue_styles() {
    // Enqueue parent theme style
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme style
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');
function custom_post_filter_widget() {
    register_widget('WP_Widget_Post_Filter');
}
add_action('widgets_init', 'custom_post_filter_widget');

class WP_Widget_Post_Filter extends WP_Widget {
    function __construct() {
        parent::__construct(
            'post_filter_widget',
            __('Post Filter by Year and Month', 'text_domain'),
            array('description' => __('A widget to filter posts by year and month', 'text_domain'))
        );
    }

    // Output the widget form on the front-end
    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo $args['before_title'] . apply_filters('widget_title', 'Filter Posts') . $args['after_title'];

        $this->display_filter_form();

        echo $args['after_widget'];
    }

    // Display the filter form for selecting year and month
    public function display_filter_form() {
        ?>
        <form method="GET" action="<?php echo esc_url(home_url('/')); ?>">
            <select name="year">
                <option value=""><?php _e('Select Year', 'text_domain'); ?></option>
                <?php
                $years = $this->get_years_with_posts();
                foreach ($years as $year) {
                    echo '<option value="' . esc_attr($year) . '">' . esc_html($year) . '</option>';
                }
                ?>
            </select>

            <select name="month">
                <option value=""><?php _e('Select Month', 'text_domain'); ?></option>
                <?php
                $months = range(1, 12);
                foreach ($months as $month) {
                    echo '<option value="' . esc_attr($month) . '">' . esc_html(date('F', mktime(0, 0, 0, $month, 1))) . '</option>';
                }
                ?>
            </select>

            <button type="submit"><?php _e('Filter', 'text_domain'); ?></button>
        </form>
        <?php
    }

    // Get a list of years with posts
    public function get_years_with_posts() {
        global $wpdb;
        $years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
        return $years;
    }
}
function filter_posts_by_year_month($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Check if year and month are set in the GET request
    if (!empty($_GET['year'])) {
        $query->set('year', intval($_GET['year']));
    }

    if (!empty($_GET['month'])) {
        $query->set('monthnum', intval($_GET['month']));
    }
}
add_action('pre_get_posts', 'filter_posts_by_year_month');
