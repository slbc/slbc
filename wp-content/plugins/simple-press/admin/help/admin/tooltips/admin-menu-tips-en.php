<?php
global $sfatooltips;

$sfatooltips=array();
$sfatooltips['integration'] = "Setup the Forum Page, Forum Permalink and Storage Locations";
$sfatooltips['forums'] = "Create and Edit Forum Groups and Forums";
$sfatooltips['options']= "Set Forum Display, Email and Style Options";
$sfatooltips['components'] = "Setup Controls for Various Forum Components";
$sfatooltips['usergroups'] = "Create and Edit User Groups to Control Forum Access";
$sfatooltips['permissions'] = "Grant and Deny User Access to Various Forum Attributes";
$sfatooltips['users'] = "Review Your Users and their Forum use";
$sfatooltips['profiles'] = "Set Up Your Users Profile Options and Display";
$sfatooltips['admins'] = "Create, Change and Remove Forum Administrators";
$sfatooltips['toolbox'] = "Delete Old Topics, Check for Updates and other Forum Information";
$sfatooltips['plugins'] = "Manage Plugins for Simple:Press";
$sfatooltips['themes'] = "Manage Themes for Simple:Press";

$sfatooltips = apply_filters('sph_menu_tooltips', $sfatooltips);
?>