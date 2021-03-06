<?php

/**
 * Template HTML Output.
 *
 * @package     Connections
 * @subpackage  Template HTML Output
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="cn-entry" style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; color: #000000; margin:8px 0px; padding:6px; position: relative;">
	<div>
		<span style="float: left; margin-right: 10px;"><?php $entry->getImage( array( 'preset' => 'profile' ) ); ?></span>

		<div style="margin-left: 10px;">
			<span style="font-size:larger;font-variant: small-caps"><strong><?php $entry->getNameBlock(); ?></strong></span>
			<div style="margin-bottom: 20px;">
				<?php $entry->getTitleBlock() ?>
				<?php $entry->getOrgUnitBlock(); ?>
			</div>
			<?php echo $entry->getBioBlock(); ?>
		</div>

	</div>
	<div style="clear:both"></div>
</div>