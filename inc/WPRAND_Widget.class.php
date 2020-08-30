<?php
/**
 * @package WPRAND
 */
defined('ABSPATH') or die();

class WPRAND_Widget extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
                'classname'                   => 'widget_recent_entries',
                'description'                 => esc_attr__('Display categories and posts randomly.', 'wprand-widget'),
                'customize_selective_refresh' => true,
            );
        parent::__construct('wprand-widget', esc_attr__('WP Randomize', 'wprand-widget'), $widget_ops);
        $this->alt_option_name = 'wprand_widget';


        add_action('widgets_init', function () {
            register_widget('wprand_Widget');
        });
    }

    public function widget($args, $instance)
    {
        $before_widget = ! empty($instance['before_widget']) ? $instance['before_widget'] : $args['before_widget'];
        $after_widget  = ! empty($instance['after_widget']) ? $instance['after_widget'] : $args['after_widget'];
        $before_title  = ! empty($instance['before_title']) ? $instance['before_title'] : $args['before_title'];
        $after_title   = ! empty($instance['after_title']) ? $instance['after_title'] : $args['after_title'];
        $pre_title     = ! empty($instance['pre_title']) ? $instance['pre_title'] . '&nbsp;' : false;
        $max_post      = ! empty($instance['max_post']) ? $instance['max_post'] : false;

        if (! isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        $accept = array();
        foreach ($this->wprand_get_categories() as $item):
            if ($instance[$item['cat_id']] == 1):
                $accept[] = $item['cat_id'];
        endif;
        endforeach;

        $randomise = ! empty($accept) ? $accept[array_rand($accept, 1) ] : false;


        $arguments = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'perm' => 'readable',
            'orderby' => $instance['post_rand'] == 1 ? 'rand' : 'ID',
            'order'   => 'DESC',
            'cat' => $randomise,
            'cache_results' => true,
            'posts_per_page' => $instance['max_post'],

        );
        echo $before_widget;




        echo $before_title . apply_filters('widget_title', ! empty($randomise) ? ($pre_title ? $pre_title : '') . get_cat_name($randomise) : esc_attr__('Random Categories', 'wprand-widget')) . $after_title;

        $text_color = ! empty($instance['text_color']) ? 'style="color:'.$instance['text_color'] .';"' : '';
        $link_color = ! empty($instance['link_color']) ? 'style="color:'.$instance['link_color'] .';"' : '';

        $query = new WP_Query($arguments);
        echo '<div id="wprand-widget" '.$text_color.'>';

        if ($randomise && $query->have_posts()) {
            ?>
            <ul>
            <?php
            while ($query->have_posts()) {
                $query->the_post(); ?>
                 <li><a href="<?php echo get_permalink() ?>" <?php echo $link_color; ?>><?php echo get_the_title() ?></a></li>
                 <?php if ($instance['display_excerpt']): ?><p><?php echo wp_strip_all_tags(get_the_excerpt(), true) ?><p><?php endif; ?>
                 <?php
            } ?>
            </ul>
              <?php
        } else {
            ?>
              <p><?php echo esc_attr__('There are no posts matching criteria. Please configure widget&#8217;s settings.', 'wprand-widget') ?></p>
            <?php
        }

        wp_reset_postdata();
        echo '</div>';

        echo $after_widget;
    }

    public function wprand_get_categories()
    {
        $list = array();

        $categories = get_categories(array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'parent' => 0
        ));

        foreach ($categories as $key => $value) {
            $list[] = array(
                'cat_id' => $value->cat_ID,
                'cat_name' => $value->name
            );
        }
        return $list;
    }

    public function form($instance)
    {
        $link_color       = !empty($instance['link_color']) ? $instance['link_color'] : '';
        $text_color       = !empty($instance['text_color']) ? $instance['text_color'] : '';
        $display_excerpt  = isset($instance['display_excerpt']) ? (bool) $instance['display_excerpt'] : 0;
        $post_rand        = isset($instance['post_rand']) ? (bool) $instance['post_rand'] : 0;
        $max_post         = !empty($instance['max_post']) ? $instance['max_post'] : 5;
        $pre_title        = !empty($instance['pre_title']) ? $instance['pre_title'] : '';


        $before_widget    = !empty($instance['before_widget']) ? $instance['before_widget'] : '';
        $after_widget     = !empty($instance['after_widget']) ? $instance['after_widget'] : '';
        $before_title     = !empty($instance['before_title']) ? $instance['before_title'] : '';
        $after_title      = !empty($instance['after_title']) ? $instance['after_title'] : ''; ?>

        <p><?php echo wp_kses_post(__('<strong>Select categories</strong> wich you want to display randomly:', 'wprand-widget')) ?></p>
        <p>
          <?php
        $catlist = array();
        foreach ($this->wprand_get_categories() as $item):
            $catlist[$item['cat_id']] = isset($instance[$item['cat_id']]) ? (bool)$instance[$item['cat_id']] : 1; ?>
     			   <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id($item['cat_id']) ?>" name="<?php echo $this->get_field_name($item['cat_id']) ?>"<?php checked($catlist[$item['cat_id']]) ?> />
       			 <label for="<?php echo $this->get_field_id($item['cat_id']) ?>"><?php echo $item['cat_name'] ?></label>
       			 <br />

   		<?php
        endforeach; ?>
        <p>
          <label for="<?php echo $this->get_field_id('max_post') ?>"><?php echo esc_attr__('Maximum Post Number: ', 'wprand-widget') ?></label>
          <input type="number" class="tiny-text" step="1" min="1" size="3" id="<?php echo $this->get_field_id('max_post') ?>" name="<?php echo $this->get_field_name('max_post') ?>" class="max_post" value="<?php echo esc_attr($max_post) ?>" />
        </p>
        <p>
          <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('post_rand') ?>" name="<?php echo $this->get_field_name('post_rand') ?>"<?php checked($post_rand) ?> />
          <label for="<?php echo $this->get_field_id('post_rand') ?>"><?php echo esc_attr__('Display Posts Randomly', 'wprand-widget') ?></label>
        </p>
        <p><?php echo esc_attr__('If not checked, Recent posts of each category will be displayed.', 'wprand-widget') ?></p>
      </p>

        <br />
        <h4><?php echo esc_attr__('Display', 'wprand-widget') ?></h4>
        <hr />
        <p><?php echo esc_attr__('You can change widget&#8217;s Display settings here.', 'wprand-widget') ?></p>
        <p>
          <label for="<?php echo $this->get_field_id('link_color') ?>"><?php echo esc_attr__('Link Color: ', 'wprand-widget') ?></label>
          <input type="text" id="<?php echo $this->get_field_id('link_color') ?>" name="<?php echo $this->get_field_name('link_color') ?>" class="color-picker" value="<?php echo esc_attr($link_color) ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('text_color') ?>"><?php echo esc_attr__('Text Color: ', 'wprand-widget') ?></label>
          <input type="text" id="<?php echo $this->get_field_id('text_color') ?>" name="<?php echo $this->get_field_name('text_color') ?>" class="color-picker" value="<?php echo esc_attr($text_color) ?>" />
        </p>
        <p>
          <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('display_excerpt') ?>" name="<?php echo $this->get_field_name('display_excerpt') ?>"<?php checked($display_excerpt) ?> />
          <label for="<?php echo $this->get_field_id('display_excerpt') ?>"><?php echo esc_attr__('Display Posts Excerpt', 'wprand-widget') ?></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_name('pre_title') ?>"><?php echo esc_attr__('Text before widget title:', 'wprand-widget') ?></label>
          <input class="widefat" placeholder="<?php echo esc_attr__('e.g. Random Category:', 'wprand-widget') ?>" id="<?php echo $this->get_field_id('pre_title') ?>" name="<?php echo $this->get_field_name('pre_title') ?>" type="text" value="<?php echo esc_attr($pre_title) ?>" />
        </p>

        <br />
        <h4><?php echo esc_attr__('Advanced Settings', 'wprand-widget') ?></h4>
        <hr />
        <p><?php echo wp_kses_post(__('<strong>Notice:</strong> These settings are used to change widget&#8217;s template. If you are not sure how to use them, Please left them empty and do not change anything.', 'wprand-widget')) ?></p>
        <p>
        <label for="<?php echo $this->get_field_id('before_widget') ?>"><?php echo esc_attr__('Before Widget HTML opening Tags:', 'wprand-widget') ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('before_widget') ?>" name="<?php echo $this->get_field_name('before_widget') ?>" type="text" value="<?php echo esc_html($before_widget) ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('after_widget') ?>"><?php echo esc_attr__('After Widget HTML closing Tags:', 'wprand-widget') ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('after_widget') ?>" name="<?php echo $this->get_field_name('after_widget') ?>" type="text" value="<?php echo esc_html($after_widget) ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('before_title') ?>"><?php echo esc_attr__('Before Title HTML opening Tags:', 'wprand-widget') ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('before_title') ?>" name="<?php echo $this->get_field_name('before_title') ?>" type="text" value="<?php echo esc_html($before_title) ?>">
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('after_title') ?>"><?php echo esc_attr__('After Title HTML closing Tags:', 'wprand-widget') ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('after_title') ?>" name="<?php echo $this->get_field_name('after_title') ?>" type="text" value="<?php echo esc_html($after_title) ?>">
        </p>
         <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();

        $instance['link_color']     = (!empty($new_instance['link_color'])) ? sanitize_hex_color($new_instance['link_color']) : '';
        $instance['text_color']     = (!empty($new_instance['text_color'])) ? sanitize_hex_color($new_instance['text_color']) : '';
        $instance['max_post']       = (!empty($new_instance['max_post'])  && is_numeric($new_instance['max_post']) && $new_instance['max_post'] >= 1) ? strip_tags($new_instance['max_post']) : 5;
        $instance['post_rand']      = !empty($new_instance['post_rand']) ? 1 : 0;
        $instance['pre_title']      = (!empty($new_instance['pre_title'])) ? sanitize_text_field($new_instance['pre_title']) : '';

        $instance['before_widget']  = (!empty($new_instance['before_widget'])) ? wp_kses_post($new_instance['before_widget']) : false;
        $instance['after_widget']   = (!empty($new_instance['after_widget'])) ?  wp_kses_post($new_instance['after_widget']) : false;
        $instance['before_title']   = (!empty($new_instance['before_title'])) ? wp_kses_post($new_instance['before_title']) : false;
        $instance['after_title']    = (!empty($new_instance['after_title'])) ? wp_kses_post($new_instance['after_title']) : false;

        foreach ($this->wprand_get_categories() as $item):

            $instance[$item['cat_id']] = !empty($new_instance[$item['cat_id']]) ? 1 : 0;

        endforeach;

        $instance['display_excerpt'] = !empty($new_instance['display_excerpt']) ? 1 : 0;


        return $instance;
    }
}
