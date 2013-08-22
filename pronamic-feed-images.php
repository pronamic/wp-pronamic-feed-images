<?php
/*
Plugin Name: Pronamic Feed Images
Plugin URI: http://pronamic.eu/wp-plugins/feed-images/
Description: This plugin automatically adds the post thubmanil to the WordPress feeds, the image size can be easily configured.
 
Version: 1.0.0
Requires at least: 3.0

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: pronamic_feed_images
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-pronamic-feed-images
*/

class Pronamic_Feed_Images_Plugin {
	/**
	 * The plugin file
	 * 
	 * @var string
	 */
	public static $file;

	/**
	 * The plugin directory name
	 * 
	 * @var string
	 */
	public static $dirname;

	//////////////////////////////////////////////////

	/**
	 * The feed image size
	 * 
	 * @var string
	 */
	public static $feed_image_size;

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap( $file ) {
		self::$file = $file;
		self::$dirname = dirname( $file );

		add_action( 'init',       array( __CLASS__, 'init' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public static function init() {
		// Text domain
		$rel_path = dirname( plugin_basename( self::$file ) ) . '/languages/';
	
		load_plugin_textdomain( 'pronamic_feed_images', false, $rel_path );

		// Feed images
		self::$feed_image_size = get_option( 'pronamic_feed_images_size' );

		if ( !empty( self::$feed_image_size ) ) {
			// add_filter( 'the_excerpt_rss',  array( __CLASS__, 'add_feed_image' ) );
			// add_filter( 'the_content_feed', array( __CLASS__, 'add_feed_image' ) );

			add_action( 'rss_item',         array( __CLASS__, 'feed_item' ) );
			add_action( 'rss2_item',        array( __CLASS__, 'feed_item' ) );
			add_action( 'rdf_item',         array( __CLASS__, 'feed_item' ) );
			add_action( 'atom_item',        array( __CLASS__, 'feed_item' ) );
		}
	}

	/**
	 * Admin initialize
	 */
	public static function admin_init() {
		add_settings_section(
			'pronamic_feed_images', // id
			__( 'Feed', 'pronamic_feed_images' ), // title
			array( __CLASS__, 'settings_section' ), // callback
			'media' // page
		);

		add_settings_field( 
			'pronamic_feed_images_size', // id
			__( 'Feed Images Size', 'pronamic_feed_images' ), // title
			array( __CLASS__, 'input_image_sizes' ),  // callback
			'media', // page
			'pronamic_feed_images', // section 
			array(  // args 
				'class'     => 'regular-text',
				'label_for' => 'pronamic_feed_images_size' 
			) 
		);

		// Register settings
		register_setting( 'media', 'pronamic_feed_images_size' );
	}

	/**
	 * Settings section
	 */
	public static function settings_section() {
		
	}

	/**
	 * Input page
	 * 
	 * @param array $args
	 */
	public static function input_image_sizes( $args ) {
		global $_wp_additional_image_sizes;

		$name = $args['label_for'];

		$image_size = get_option( $name );

		$image_sizes = get_intermediate_image_sizes(); 

		?><select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
    		<option value="" <?php selected( $image_size, '' ); ?>></option>

			<?php foreach ( $image_sizes as $size_name ): ?>
    			<option value="<?php echo esc_attr( $size_name ); ?>" <?php selected( $image_size, $size_name ); ?>><?php 
    			
    			echo $size_name;
    			
    			if ( isset( $_wp_additional_image_sizes[$size_name] ) ) {
    				$size = $_wp_additional_image_sizes[$size_name];
    				
    				if ( isset( $size['width'], $size['height'] ) ) {
    					echo ' (';
    					echo $size['width'], ' &times; ', $size['height'];  					
    					echo ')';
    				}
    			}
    			
    			?></option>
    		<?php endforeach; ?>
    	</select><?php
	}
	
	/**
	 * Add feed image to output
	 * 
	 * @param string $output
	 */
	public static function add_feed_image( $output ) {
		if ( has_post_thumbnail() ) {
			$output .= get_the_post_thumbnail( null, self::$feed_image_size );
		}

		return $output;
	}

	/**
	 * Feed item
	 */
	public static function feed_item() {
		if ( has_post_thumbnail() ) {
			$thumbnail_id = get_post_thumbnail_id();

			$src  = wp_get_attachment_image_src( $thumbnail_id, self::$feed_image_size );
			
			if ( $src ) {
				$url  = $src[0];
				$type = get_post_mime_type( $thumbnail_id );
			
				printf( '<enclosure url="%s" type="%s" />', $url, $type );
			}
		}
	}
}

Pronamic_Feed_Images_Plugin::bootstrap( __FILE__ );
