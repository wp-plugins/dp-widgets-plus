<?php
/**
 * Plugin Name: DP Widgets Plus
 * Plugin URI: http://dedepress.com/plugins/dp-widgets-plus/
 * Description: Add extra control options to each widget. You can specify the link of widget title, custom CSS classs name, subtitle, icon and more advanced settings to gives you total control over the output of your widgets. 
 * Version: 1.0
 * Author: Cloud Stone
 * Author URI: http://dedepress.com/
 * License: GPL V2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/*
 * This Plugin is inspired by Aizat's Widget Classes plugins, Big Thanks!
 */
class DP_Widgets_Plus {
	/**
	 * PHP4 constructor method.
	 */
	function DP_Widgets_Plus() {
		$this->__construct();
	}
	
	/**
	 * Constructor method for adds other methods of the class to specific hooks within WordPress.
	 */
	function __construct( ) {
		add_action('in_widget_form', array(&$this, 'in_widget_form'), 10, 3); 
		add_filter('widget_update_callback', array(&$this, 'widget_update_callback'), 10, 4);
		add_filter('dynamic_sidebar_params', array(&$this, 'dynamic_sidebar_params'));
		add_filter('sidebar_admin_setup', array(&$this, 'sidebar_admin_setup'));
		// add_action('wp_print_styles', array(&$this, 'styles')); 
		add_action('admin_print_styles', array(&$this, 'admin_styles')); 
		// TODO: Add a menu page for general settings and user still can override in each widget.
		// add_action('admin_menu', array(&$this, 'add_menu_pages'));
		// Filtering a setting link to plugin_action_links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(&$this, 'settings_link') );
	}
	
	/**
	 * Add a settings page for this plugin.
	 */
	function add_menu_pages() {
		add_theme_page( __('Widget Plus', 'dp'), __('Widget Plus', 'dp'), 'edit_plugins', 'dp-widget-plus',array(&$this, 'menu_page'), '' );
	}
	
	/**
	 * Add a settings link to plugin actino links in plugin manager page.
	 */
	function menu_page() {
		echo 'TODO';
	}
	
	function settings_link( $links ) {
		$links[] = '<a href="'.admin_url('widgets.php').'">' . __('Settings') .'</a>';
		return $links;
	}
	
	/**
     * Ideally all classes are children of the WP_Widget class.
     * 
     * Sometimes they are not, for example 'twitter-for-wordpress' and 'akismet'.
     * 
     * For these widges which avoid using WP_Widget class, they circumvent a hook which
     * allows me to append the CSS Class form to the bottom of the widget.
     * 
     * Instead we intercept the hook, and have it call our own function, which
     * will allow us to inject our the form into the widget.
	 */
	function sidebar_admin_setup() {
		global $wp_registered_widget_controls;
	
		foreach ($wp_registered_widget_controls as $widget_id => $options) {
			if (is_array($options['callback'])) {
				continue;
			}

			$wp_registered_widget_controls[$widget_id]['_params']   = $wp_registered_widget_controls[$widget_id]['params'];
			$wp_registered_widget_controls[$widget_id]['_callback'] = $wp_registered_widget_controls[$widget_id]['callback'];
			$wp_registered_widget_controls[$widget_id]['params']    = $widget_id;
			$wp_registered_widget_controls[$widget_id]['callback']  = array(__CLASS__, 'intercept');
		}
	}

	/**
     * Injects the CSS class into the 'before_widget' value
     */
	function dynamic_sidebar_params($params) {
		global $wp_registered_sidebars, $wp_registered_widgets;

		$widget_id = $params[0]['widget_id'];
		$widget = $wp_registered_widgets[$widget_id];
		
		if (! is_array($widget['callback'])) {
			$defaults = $this->get_defaults();
			$options = get_option('dp_widget_params', $defaults);

			if (! array_key_exists($widget_id, $options))
				return $params;
				
			$instance = $options[$widget_id];
		} 
		
		else { 
			$instance = $widget['callback'][0]->get_settings();

			if (! array_key_exists($params[1]['number'], $instance))
				return $params;

			$instance = $instance[$params[1]['number']];
		}
		
		$before_widget = $wp_registered_sidebars[$params[0]['id']]['before_widget'];
		$after_widget = $wp_registered_sidebars[$params[0]['id']]['after_widget'];
		$before_title = $wp_registered_sidebars[$params[0]['id']]['before_title'];
		$after_title = $wp_registered_sidebars[$params[0]['id']]['after_title'];
		
		$before_bw = $after_bw = $before_aw = $after_aw = $before_bt = $after_bt = $before_at = $after_at = '';
		
		// Substitute HTML id and class attributes into before_widget
		$classname_ = '';
		foreach ( (array) $widget['classname'] as $cn ) {
			if ( is_string($cn) )
				$classname_ .= '_' . $cn;
			elseif ( is_object($cn) )
				$classname_ .= '_' . get_class($cn);
		}
		$classname_ = ltrim($classname_, '_');
		if(!empty($instance['dp_class'])) {$classname_ .= ' ' . $instance['dp_class'];}
			
		$before_widget = sprintf($before_widget, $widget['id'], $classname_);
		
		/* subtitle */
		if(!empty($instance['dp_subtitle'])) {
			$subtitle = '<span class="widget-sub-title">'. $instance['dp_subtitle']. '</span> ';
			
			if($instance['dp_subtitle_pos'] == 'after_title_tag')
				$after_at .= $subtitle;
			else
				$before_at .= $subtitle;
		}
			
		/* icon */
		if(!empty($instance['dp_icon'])) {
			$icon = '<img src="'. $instance['dp_icon']. '" alt="" />';
			
			$icon_style = '';
			
			if($instance['dp_icon_float'] == 'left') {
				$icon_style = ' style="margin:0 10px 0 0;float:left;"';
			} elseif($instance['dp_icon_float'] == 'right') {
				$icon_style = ' style="margin:0 0 0 10px;float:right;"';
			}
			
			if(!empty($instance['dp_icon_link']))
				$icon = '<a'.$icon_style.' class="widget-icon" href="'.$instance['dp_icon_link'].'">'.$icon .'</a> ';
			else
				$icon = '<span'.$icon_style.' class="widget-icon">'.$icon.'</span> ';
				
			if($instance['dp_icon_pos'] == 'before_title_tag')
				$before_bt .= $icon;
			elseif($instance['dp_icon_pos'] == 'before_title_text')
				$after_bt .= $icon;
			elseif($instance['dp_icon_pos'] == 'after_title_text')
				$before_at .= $icon;
			elseif($instance['dp_icon_pos'] == 'after_title_tag')
				$after_at .= $icon;
		}
		
		/* main title */
		if(!empty($instance['dp_title_link'])) {
			$after_bt = $after_bt. '<a class="widget-main-title" href="'.$instance['dp_title_link'].'">';
			$before_at = '</a> '.$before_at;
		} else { 
			$after_bt = $after_bt. '<span class="widget-main-title">';
			$before_at = '</span> '.$before_at;
		}
		
		if(!empty($instance['dp_icon']))
			$after_at = '<br style="clear:both" />' . $after_at;
		
		if(!empty($instance['dp_more']))
			$before_aw = $before_aw . '<div class="widget-footer">' . stripslashes($instance['dp_more']) . '</div>';
		
		// $before_at = '<div class="widget-header">'. $before_at;
		// $after_at = $after_at . '</div><!-- .widget-header -->';
		
		$params[0]['before_widget'] = $before_bw . $before_widget . $after_bw;
		$params[0]['after_widget'] = $before_aw . $after_widget . $after_aw;
		$params[0]['before_title'] = $before_bt . $before_title . $after_bt;
		$params[0]['after_title'] = $before_at . $after_title . $after_at;

		return $params;
	}

	/**
     * Our hook which allows us to intercept and inject into widgets which
     * do not use WP_Widget
     */
	function intercept($widget_id) {
		global $wp_registered_widget_controls;
		$callback = $wp_registered_widget_controls[$widget_id]['_callback'];
		$params   = $wp_registered_widget_controls[$widget_id]['_params'];

		$return   = call_user_func_array($callback, $params);
		
		$options = get_option('dp_widget_params', array());

		if (!array_key_exists($widget_id, $options))
			$options[$widget_id] = self::get_defaults();

		$old_instance = $new_instance = $options;

		if (!empty($_POST[$widget_id]['dp_widget_params']))
			$new_instance[$widget_id] = $options[$widget_id] = $_POST[$widget_id]['dp_widget_params'];
			
		$icon_float = array('none' => __('None', 'dp'), 'left' => __('Left', 'dp'), 'right' => __('Right', 'dp'));
		$icon_pos = array(
			'before_title_text' => __('Before title text', 'dp'), 
			'after_title_text' => __('After title text', 'dp'),
			'before_title_tag' => __('Before title tag', 'dp'), 
			'after_title_tag' => __('After title tag', 'dp') 
		);
		$subtitle_pos = array(
			'after_title_text' => __('After title text', 'dp'),
			'after_title_tag' => __('After title tag', 'dp') 
		);
		
		?>
		
		<div class="widget dp-widget-panel">
		<div class="widget-top">
			<div class="widget-title-action">
				<a href="#available-widgets" class="widget-action hide-if-no-js"></a>
			</div>
			<div class="widget-title"><h4><?php _e('DP Widget Plus', 'dp'); ?></h4></div>
		</div>
		
		<div class="widget-inside" style="display: block;">
		<p>
			<label><?php _e('Classes:', 'dp'); ?><input class="widefat" id="<?php echo $widget_id.'_dp_class'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_class]'; ?>" type="text" value="<?php echo esc_attr(strip_tags($options[$widget_id]['dp_class'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Subtitle:', 'dp'); ?></label><textarea class="widefat" cols="20" rows="2" id="<?php echo $widget_id.'_dp_subtitle'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_subtitle]'; ?>"><?php echo esc_attr(strip_tags($options[$widget_id]['dp_subtitle'])); ?></textarea>
		</p>
		<p>
			<label><?php _e('Subtitle position:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget_id.'_dp_subtitle_pos'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_subtitle_pos]'; ?>">
				<?php foreach($subtitle_pos as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $options[$widget_id]['dp_subtitle_pos']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon:', 'dp'); ?><input class="widefat" id="<?php echo $widget_id.'_dp_icon'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_icon]'; ?>" type="text" value="<?php echo esc_attr(strip_tags($options[$widget_id]['dp_icon'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Icon position:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget_id.'_dp_icon_pos'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_icon_pos]'; ?>">
				<?php foreach($icon_pos as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $options[$widget_id]['dp_icon_pos']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon float:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget_id.'_dp_icon_float'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_icon_float]'; ?>">
				<?php foreach($icon_float as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $options[$widget_id]['dp_icon_float']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon link:', 'dp'); ?><input class="widefat" id="<?php echo $widget_id.'_dp_icon_link'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_icon_link]'; ?>" type="text" value="<?php echo esc_attr(strip_tags($options[$widget_id]['dp_icon_link'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Title link:', 'dp'); ?><input class="widefat" id="<?php echo $widget_id.'_dp_title_link'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_title_link]'; ?>" type="text" value="<?php echo esc_attr(strip_tags($options[$widget_id]['dp_title_link'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('More:', 'dp'); ?></label><textarea class="widefat" cols="20" rows="2" id="<?php echo $widget_id.'_dp_more'; ?>" name="<?php echo $widget_id.'[dp_widget_params][dp_more]'; ?>"><?php echo esc_html(stripslashes($options[$widget_id]['dp_more'])); ?></textarea>
		</p>
		</div><!-- .widget-inside -->
		</div><!-- .widget -->
		
		
		<?php
		if ($old_instance[$widget_id] != $new_instance[$widget_id])
			update_option('dp_widget_params', $options);
		
		return $return;
	}

	/**
     * Hook used by WP_Widget and its children
     */
	function in_widget_form($widget, $return, $instance) {
		$defaults = $this->get_defaults();
		$instance = wp_parse_args( (array) $instance, $defaults );
		$return = null;
		
		$icon_float = array('none' => __('None', 'dp'), 'left' => __('Left', 'dp'), 'right' => __('Right', 'dp'));
		$icon_pos = array(
			'before_title_text' => __('Before title text', 'dp'), 
			'after_title_text' => __('After title text', 'dp'),
			'before_title_tag' => __('Before title tag', 'dp'), 
			'after_title_tag' => __('After title tag', 'dp') 
		);
		$subtitle_pos = array(
			'after_title_text' => __('After title text', 'dp'),
			'after_title_tag' => __('After title tag', 'dp') 
		);
		
		?>
		
		<div class="widget dp-widget-panel">
		<div class="widget-top">
			<div class="widget-title-action">
				<a href="#available-widgets" class="widget-action hide-if-no-js"></a>
			</div>
			<div class="widget-title"><h4><?php _e('DP Widget Plus', 'dp'); ?></h4></div>
		</div>
		
		<div class="widget-inside" style="display: block;">
		<p>
			<label><?php _e('Classes:', 'dp'); ?><input class="widefat" id="<?php echo $widget->get_field_id('dp_class') ?>" name="<?php echo $widget->get_field_name('dp_class'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['dp_class'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Subtitle:', 'dp'); ?></label><textarea class="widefat" cols="20" rows="2" id="<?php echo $widget->get_field_id('dp_subtitle') ?>" name="<?php echo $widget->get_field_name('dp_subtitle'); ?>"><?php echo esc_attr(strip_tags($instance['dp_subtitle'])); ?></textarea>
		</p>
		<p>
			<label><?php _e('Subtitle position:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget->get_field_id('dp_subtitle_pos') ?>" name="<?php echo $widget->get_field_name('dp_subtitle_pos'); ?>">
				<?php foreach($subtitle_pos as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $instance['dp_subtitle_pos']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon:', 'dp'); ?><input class="widefat" id="<?php echo $widget->get_field_id('dp_icon') ?>" name="<?php echo $widget->get_field_name('dp_icon'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['dp_icon'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Icon position:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget->get_field_id('dp_icon_pos') ?>" name="<?php echo $widget->get_field_name('dp_icon_pos'); ?>">
				<?php foreach($icon_pos as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $instance['dp_icon_pos']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon float:', 'dp'); ?></label>
			<select class="widefat" id="<?php echo $widget->get_field_id('dp_icon_float') ?>" name="<?php echo $widget->get_field_name('dp_icon_float'); ?>">
				<?php foreach($icon_float as $option => $label) { 
					echo '<option value="'.$option.'"'.selected($option, $instance['dp_icon_float']).'>'.$label.'</option>';
				} ?>
			</select>
		</p>
		<p>
			<label><?php _e('Icon link:', 'dp'); ?><input class="widefat" id="<?php echo $widget->get_field_id('dp_icon_link') ?>" name="<?php echo $widget->get_field_name('dp_icon_link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['dp_icon_link'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('Title link:', 'dp'); ?><input class="widefat" id="<?php echo $widget->get_field_id('dp_title_link') ?>" name="<?php echo $widget->get_field_name('dp_title_link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['dp_title_link'])); ?>"></label>
		</p>
		<p>
			<label><?php _e('More:', 'dp'); ?></label><textarea class="widefat" cols="20" rows="2" id="<?php echo $widget->get_field_id('dp_more') ?>" name="<?php echo $widget->get_field_name('dp_more'); ?>"><?php echo esc_html(stripslashes($instance['dp_more'])); ?></textarea>
		</p>
		</div><!-- .widget-inside -->
		</div><!-- .widget -->
	<?php }

	/**
     * Default fields value.
     */
	function get_defaults() { 
		$defaults = array( 
			'dp_class' => '', 
			'dp_icon' => '', 
			'dp_icon_pos' => '',
			'dp_icon_float' => '',
			'dp_subtitle' => '',
			'dp_subtitle_pos' => '',
			'dp_title_link' => '',
			'dp_icon_link' => '',
			'dp_more' => ''
		);
		return $defaults;
	}

	/**
     * Hook used by WP_Widget and its children.
     */
	function widget_update_callback($instance, $new_instance, $old_instance, $widget) {
		$instance['dp_class'] = str_replace(', ', ' ', strip_tags($new_instance['dp_class']));
		$instance['dp_class'] = str_replace(',', ' ', strip_tags($new_instance['dp_class']));
		$instance['dp_icon'] = strip_tags($new_instance['dp_icon']);
		$instance['dp_icon_pos'] = strip_tags($new_instance['dp_icon_pos']);
		$instance['dp_icon_float'] = strip_tags($new_instance['dp_icon_float']);
		$instance['dp_subtitle'] = $new_instance['dp_subtitle'];
		$instance['dp_subtitle_pos'] = $new_instance['dp_subtitle_pos'];
		$instance['dp_title_link'] = $new_instance['dp_title_link'];
		$instance['dp_icon_link'] = $new_instance['dp_icon_link'];
		$instance['dp_more'] = $new_instance['dp_more'];

		return $instance;
	}
	
	/**
     * Output embedded styles in head of back end.
     */
	function admin_styles() { ?>
		<style type="text/css">
			.dp-widget-panel{width:100% !important;}
		</style>
	<?php }
	
	/**
     * Output embedded styles in head of front end.
     */
	function styles() { ?>
		<style type="text/css">
			.widget-icon{}
			.widget-icon img{}
			.widget-title{}
			.widget-main-title{}
			.widget-sub-title{}
			.widget-footer{}
			.widget-footer a{}
		</style>
	<?php }
}

$widgets_plus = & new DP_Widgets_Plus();