<?php

/**
 * Dashboard: Recently Modified Widget Template.
 *
 * @package     Connections
 * @subpackage  Template : Dashboard: Recently Modified Widget
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Dashboard_Recently_Modified_Template' ) ) {

	class CN_Dashboard_Recently_Modified_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Dashboard_Recently_Modified_Template',
				'name'        => 'Dashboard: Recently Modified Widget',
				'slug'        => 'dashboard-recent-modified',
				'type'        => 'dashboard',
				'version'     => '2.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Dashboard Widget that displays the recently modified entries.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => '',
				'parts'       => array( 'css' => 'styles.css' ),
				);

			cnTemplateFactory::register( $atts );
		}

		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( $this, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		public function card( $entry, $content, $template, $atts, $connections, $vCard ) {

			if ( is_admin() ) {

				if ( ! isset( $form ) ) $form = new cnFormObjects();

				$editTokenURL = $form->tokenURL( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(), 'entry_edit_' . $entry->getId() );

				if ( current_user_can( 'connections_edit_entry' ) ) {

					echo '<span class="cn-entry-name"><a class="row-title" title="Edit ' , $entry->getName() , '" href="' , $editTokenURL , '"> ' , $entry->getName() . '</a></span> <span class="cn-list-date">' , $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) , '</span>';

				} else {

					echo '<span class="cn-entry-name">' , $entry->getName() , '</span> <span class="cn-list-date">' , $entry->getFormattedTimeStamp( 'm/d/Y g:ia' ) , '</span>';
				}

			}
		}

	}

	add_action( 'cn_register_template', array( 'CN_Dashboard_Recently_Modified_Template', 'register' ) );
}