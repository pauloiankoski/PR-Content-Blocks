<?php
/*
Plugin Name: PR Content Blocks
Plugin URI:  http://www.grau.com.br
Description: Create extra content blocks with shortcodes.
Version:     0.1
Author:      Paulo Iankoski
Author URI:  http://www.paulor.com.br
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: pr-content-blocks

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Start up the engine
class PR_Content_Blocks
{

	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		// backend
		add_action ( 'plugins_loaded', array( $this, 'textdomain' ) );
		add_action( 'init', array( $this, 'create_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_shortcode_meta_box' ) );
		add_filter( 'manage_content_block_posts_columns', array( $this, 'set_custom_edit_content_block_columns' ) );
		add_action( 'manage_content_block_posts_custom_column' , array( $this, 'custom_content_block_column' ), 10, 2 );
		add_shortcode( 'pr_content_block', array( $this, 'create_shortcode' ) );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return PR_Content_Blocks
	 */

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * load textdomain
	 *
	 * @return void
	 */

	public function textdomain() {
		load_plugin_textdomain( 'pr-content-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	public function create_post_type() {
		$labels = array(
			'name'               => _x( 'Content Blocks', 'post type general name', 'pr-content-blocks' ),
			'singular_name'      => _x( 'Content Block', 'post type singular name', 'pr-content-blocks' ),
			'menu_name'          => _x( 'Content Blocks', 'admin menu', 'pr-content-blocks' ),
			'name_admin_bar'     => _x( 'Content Block', 'add new on admin bar', 'pr-content-blocks' ),
			'add_new'            => _x( 'Add New', 'content_block', 'pr-content-blocks' ),
			'add_new_item'       => __( 'Add New Content Block', 'pr-content-blocks' ),
			'new_item'           => __( 'New Content Block', 'pr-content-blocks' ),
			'edit_item'          => __( 'Edit Content Block', 'pr-content-blocks' ),
			'view_item'          => __( 'View Content Block', 'pr-content-blocks' ),
			'all_items'          => __( 'All Content Blocks', 'pr-content-blocks' ),
			'search_items'       => __( 'Search Content Blocks', 'pr-content-blocks' ),
			'parent_item_colon'  => __( 'Parent Content Blocks:', 'pr-content-blocks' ),
			'not_found'          => __( 'No content blocks found.', 'pr-content-blocks' ),
			'not_found_in_trash' => __( 'No content blocks found in Trash.', 'pr-content-blocks' )
		);

		$args = array(
			'description'   => __( 'Description.', 'pr-content-blocks' ),
			'labels'        => $labels,
			'menu_position' => 20,
			'public'        => false,
			'show_ui'       => true,
			'supports'      => array( 'title', 'editor' ),
		);

		register_post_type( 'content_block', $args );
	}

	public function add_shortcode_meta_box() {
		add_meta_box(
			'content_block_shortcode',
			__( 'Content Block Shortcode', 'pr-content-blocks' ),
			array( $this, 'add_shortcode_meta_box_callback' ),
			'content_block',
			'side'
		);
	}

	function add_shortcode_meta_box_callback( $post ) {
		echo '<input type="text" value="' . htmlspecialchars( sprintf( '[pr_content_block id="%d" title="%s"]', $post->ID, get_the_title( $post->ID ) ) ) . '" readonly style="width: 100%" />';
	}

	public function set_custom_edit_content_block_columns( $columns ) {
		unset( $columns['date'] );
		$columns['shortcode'] = __( 'Shortcode', 'pr-content-blocks' );

		return $columns;
	}

	public function custom_content_block_column( $column, $post_id ) {
		switch ( $column ) {

			case 'shortcode' :
				printf( '[pr_content_block id="%d" title="%s"]', $post_id, get_the_title( $post_id ) );
			break;
		}
	}

	public function create_shortcode( $atts ) {
		$a = shortcode_atts( array(
			'id' => '',
			'title' => '',
		), $atts );

		$content_block = get_post( $a['id'] );

		return sprintf( '<div class="pr-content-blocks content-block block-id-%d">%s</div>', $content_block->ID, do_shortcode( $content_block->post_content ) );
	}

}

// Instantiate our class
$PR_Content_Blocks = PR_Content_Blocks::getInstance();
