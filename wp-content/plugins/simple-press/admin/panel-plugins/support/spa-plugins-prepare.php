<?php
/*
Simple:Press
Admin plugins prepare Support Functions
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
* Get the list of plugins
*/
function spa_get_plugins_list_data() {
    $plugins = sp_get_plugins();
    return $plugins;
}

?>