<?php
/**
 * AdminAseets class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_AdminAssets {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		add_image_size( 'soliloquy-thumb', 115, 115, true );

		/** Load dev scripts and styles if in Soliloquy dev mode */
		$dev = defined( 'SOLILOQUY_DEV' ) && SOLILOQUY_DEV ? '-dev' : '';

		/** Register scripts and styles */
		wp_register_script( 'soliloquy-admin', plugins_url( 'js/admin' . $dev . '.js', dirname( __FILE__ ) ), array( 'jquery', 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror', plugins_url( '/js/codemirror.js', dirname( __FILE__ ) ), array( 'jquery' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-php', plugins_url( '/js/codemirror-php.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-html', plugins_url( '/js/codemirror-html.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-css', plugins_url( '/js/codemirror-css.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-js', plugins_url( '/js/codemirror-js.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-xml', plugins_url( '/js/codemirror-xml.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-codemirror-clike', plugins_url( '/js/codemirror-clike.js', dirname( __FILE__ ) ), array( 'soliloquy-codemirror' ), Tgmsp::get_instance()->version, true );
		wp_register_style( 'soliloquy-admin', plugins_url( 'css/admin' . $dev . '.css', dirname( __FILE__ ) ), array(), Tgmsp::get_instance()->version );
		wp_register_style( 'soliloquy-codemirror', plugins_url( '/css/codemirror.css', dirname( __FILE__ ) ), array(), Tgmsp::get_instance()->version );
		wp_register_style( 'soliloquy-codemirror-elegant', plugins_url( '/css/codemirror-eclipse.css', dirname( __FILE__ ) ), array(), Tgmsp::get_instance()->version );

		/** Load assets */
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

	}

	/**
	 * Enqueue custom scripts and styles for the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id The current post ID
	 * @global object $post The current post object
	 */
	public function load_assets() {

		global $id, $post;

		/** Load for any Soliloquy screen */
		if ( Tgmsp::is_soliloquy_screen() )
			wp_enqueue_style( 'soliloquy-admin' );

		/** Only load for the Soliloquy post type add and edit screens */
		if ( Tgmsp::is_soliloquy_add_edit_screen() ) {
			/** Send the post ID along with our script */
			$post_id = ( null === $id ) ? $post->ID : $id;

			/** Store script arguments in an array */
			$args = apply_filters( 'tgmsp_slider_object_args', array(
				'alt'			=> Tgmsp_Strings::get_instance()->strings['image_alt'],
				'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
				'caption'		=> Tgmsp_Strings::get_instance()->strings['image_caption'],
				'delete_nag'	=> Tgmsp_Strings::get_instance()->strings['confirm_delete'],
				'duration'		=> 600,
				'existing'		=> Tgmsp_Strings::get_instance()->strings['link_existing'],
				'html'			=> Tgmsp_Strings::get_instance()->strings['html'],
				'htmlplace'		=> Tgmsp_Strings::get_instance()->strings['html_slide_place'],
				'htmlremove'	=> Tgmsp_Strings::get_instance()->strings['html_remove'],
				'htmlstart'		=> Tgmsp_Strings::get_instance()->strings['html_start'],
				'htmlslide'		=> Tgmsp_Strings::get_instance()->strings['html_slide'],
				'id'			=> $post_id,
				'image'			=> Tgmsp_Strings::get_instance()->strings['image'],
				'insertnonce'	=> wp_create_nonce( 'soliloquy_insert_slides' ),
				'inserting'		=> Tgmsp_Strings::get_instance()->strings['inserting'],
				'inserted'		=> Tgmsp_Strings::get_instance()->strings['inserted'],
				'height'		=> 300,
				'librarysearch' => wp_create_nonce( 'soliloquy_library_search' ),
				'link'			=> Tgmsp_Strings::get_instance()->strings['image_link'],
				'linkclose'		=> Tgmsp_Strings::get_instance()->strings['link_existing_close'],
				'linknonce'		=> wp_create_nonce( 'soliloquy_linking' ),
				'linknormal'	=> Tgmsp_Strings::get_instance()->strings['image_link_normal'],
				'linkopen'		=> Tgmsp_Strings::get_instance()->strings['link_existing'],
				'linktitle'		=> Tgmsp_Strings::get_instance()->strings['image_url_title'],
				'linktype'		=> Tgmsp_Strings::get_instance()->strings['image_link_type'],
				'linkvideo'		=> Tgmsp_Strings::get_instance()->strings['image_link_video'],
				'loading'		=> Tgmsp_Strings::get_instance()->strings['loading'],
				'loadnonce'		=> wp_create_nonce( 'soliloquy_load_library' ),
				'menu_explain'	=> Tgmsp_Strings::get_instance()->strings['menu_explain'],
				'metadesc'		=> Tgmsp_Strings::get_instance()->strings['image_meta'],
				'metanonce'		=> wp_create_nonce( 'soliloquy_meta' ),
				'metatitle'		=> Tgmsp_Strings::get_instance()->strings['update_meta'],
				'modify'		=> Tgmsp_Strings::get_instance()->strings['modify_image'],
				'modifytb'		=> Tgmsp_Strings::get_instance()->strings['modify_image_tb'],
				'nonce'			=> wp_create_nonce( 'soliloquy_uploader' ),
				'noresults'		=> Tgmsp_Strings::get_instance()->strings['no_results'],
				'remove'		=> Tgmsp_Strings::get_instance()->strings['remove_image'],
				'removenonce'	=> wp_create_nonce( 'soliloquy_remove' ),
				'removing'		=> Tgmsp_Strings::get_instance()->strings['removing'],
				'saved'			=> Tgmsp_Strings::get_instance()->strings['saved'],
				'saving'		=> Tgmsp_Strings::get_instance()->strings['saving'],
				'screen'		=> true,
				'search'		=> Tgmsp_Strings::get_instance()->strings['search'],
				'searching'		=> Tgmsp_Strings::get_instance()->strings['searching'],
				'sortnonce'		=> wp_create_nonce( 'soliloquy_sortable' ),
				'speed'			=> 7000,
				'spinner'		=> plugins_url( 'css/images/loading.gif', dirname( __FILE__ ) ),
				'savemeta'		=> Tgmsp_Strings::get_instance()->strings['save_meta'],
				'upload'		=> Tgmsp_Strings::get_instance()->strings['upload_images_tb'],
				'tab'			=> Tgmsp_Strings::get_instance()->strings['new_tab'],
				'title'			=> Tgmsp_Strings::get_instance()->strings['image_title'],
				'url'			=> Tgmsp_Strings::get_instance()->strings['image_url'],
				'video'			=> Tgmsp_Strings::get_instance()->strings['video'],
				'videocaption'	=> Tgmsp_Strings::get_instance()->strings['video_caption'],
				'videoslide'	=> Tgmsp_Strings::get_instance()->strings['video_slide'],
				'videoplace'	=> Tgmsp_Strings::get_instance()->strings['video_slide_place'],
				'videotitle'	=> Tgmsp_Strings::get_instance()->strings['video_title'],
				'videooutput'	=> Tgmsp_Strings::get_instance()->strings['video_output'],
				'width'			=> 600
			) );

			wp_enqueue_script( 'soliloquy-admin' );
			wp_localize_script( 'soliloquy-admin', 'soliloquy', $args );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'soliloquy-codemirror' );
			wp_enqueue_script( 'soliloquy-codemirror-php' );
			wp_enqueue_script( 'soliloquy-codemirror-html' );
			wp_enqueue_script( 'soliloquy-codemirror-css' );
			wp_enqueue_script( 'soliloquy-codemirror-js' );
			wp_enqueue_script( 'soliloquy-codemirror-xml' );
			wp_enqueue_script( 'soliloquy-codemirror-clike' );
			wp_enqueue_script( 'plupload-handlers' );
			wp_enqueue_style( 'soliloquy-codemirror' );
			wp_enqueue_style( 'soliloquy-codemirror-elegant' );
			wp_enqueue_media( array( 'post' => $post_id ) );
		}

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}