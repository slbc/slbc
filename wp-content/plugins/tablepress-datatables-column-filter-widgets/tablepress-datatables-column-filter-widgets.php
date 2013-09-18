<?php
/*
Plugin Name: TablePress Extension: DataTables ColumnFilterWidgets
Plugin URI: http://tablepress.org/extensions/datatables-columnfilterwidgets/
Description: Custom Extension for TablePress to add the DataTables ColumnFilterWidgets functionality
Version: 1.4
Author: Tobias Bäthge
Author URI: http://tobias.baethge.com/
*/

// Shortcode: [table id=123 datatables_columnfilterwidgets=true datatables_columnfilterwidgets_exclude_columns=2,3,4 datatables_columnfilterwidgets_separator="," /]

/**
 * Register necessary Plugin Filters
 */
add_filter( 'tablepress_shortcode_table_default_shortcode_atts', 'tablepress_add_shortcode_parameters_columnfilterwidgets' );
add_filter( 'tablepress_table_js_options', 'tablepress_add_columnfilterwidgets_js_options', 10, 3 );
add_filter( 'tablepress_datatables_parameters', 'tablepress_add_columnfilterwidgets_parameters', 10, 4 );
if ( ! is_admin() )
	add_action( 'wp_enqueue_scripts', 'tablepress_enqueue_columnfilterwidgets_css' );

/**
 * Add "datatables_columnfilterwidgets" as a valid parameter to the [table /] Shortcode
 */
function tablepress_add_shortcode_parameters_columnfilterwidgets( $default_atts ) {
	$default_atts['datatables_columnfilterwidgets'] = false;
	$default_atts['datatables_columnfilterwidgets_exclude_columns'] = '';
	$default_atts['datatables_columnfilterwidgets_separator'] = '';
	return $default_atts;
}

/**
 * Pass "datatables_columnfilterwidgets" from Shortcode parameters to JavaScript arguments
 */
function tablepress_add_columnfilterwidgets_js_options( $js_options, $table_id, $render_options ) {
	$js_options['datatables_columnfilterwidgets'] = $render_options['datatables_columnfilterwidgets'];
	$js_options['datatables_columnfilterwidgets_exclude_columns'] = $render_options['datatables_columnfilterwidgets_exclude_columns'];
	$js_options['datatables_columnfilterwidgets_separator'] = $render_options['datatables_columnfilterwidgets_separator'];

	// register the JS
	if ( '' != $js_options['datatables_columnfilterwidgets'] ) {
		$js_columnfilterwidgets_url = plugins_url( 'js/ColumnFilterWidgets.js', __FILE__ );
		wp_enqueue_script( 'tablepress-columnfilterwidgets', $js_columnfilterwidgets_url, array( 'tablepress-datatables' ), '1.3', true );
	}

	return $js_options;
}

/**
 * Add "sDom" parameter, if ColumnFilterWidgets is enabled for the table
 */
function tablepress_add_columnfilterwidgets_parameters( $parameters, $table_id, $html_id, $js_options ) {

	if ( ! $js_options['datatables_columnfilterwidgets'] )
		return $parameters;

	$parameters['sDom'] = '"sDom": "Wlfrtip"';

	$column_filter_widget_parameters = array();

	if ( '' != $js_options['datatables_columnfilterwidgets_exclude_columns'] ) {
		$columns = explode( ',', $js_options['datatables_columnfilterwidgets_exclude_columns'] );
		foreach ( $columns as $idx => $column ) {
			$columns[$idx] = (int)( trim($column) ) - 1;
		}
		$columns = implode( ',', $columns );
		if ( '' != $columns )
			$column_filter_widget_parameters['oColumnFilterWidgets'] = '"aiExclude": [ ' . $columns . ' ]';
	}

	if ( '' != $js_options['datatables_columnfilterwidgets_separator'] ) {
		$separator = addslashes( $js_options['datatables_columnfilterwidgets_separator'] );
		$separator = str_replace( '"', '\"', $separator ); // some simple escaping
		$column_filter_widget_parameters['sSeparator'] = '"sSeparator": "' . $separator . '"';
	}

	if ( ! empty( $column_filter_widget_parameters ) ) {
		$column_filter_widget_parameters = implode( ',', $column_filter_widget_parameters );
		$parameters['oColumnFilterWidgets'] = '"oColumnFilterWidgets": { ' . $column_filter_widget_parameters . ' }';
	}

	return $parameters;
}

/**
 * Enqueue ColumnFilterWidgets CSS
 */
function tablepress_enqueue_columnfilterwidgets_css() {
	$columnfilterwidgets_css_url = plugins_url( "css/ColumnFilterWidgets.css", __FILE__ );
	wp_enqueue_style( 'tablepress-columnfilterwidgets-css', $columnfilterwidgets_css_url, array(), '1.2' );
}
