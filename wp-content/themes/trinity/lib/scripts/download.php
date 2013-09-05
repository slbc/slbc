<?php
$file = ( $_REQUEST['file'] ) ? htmlspecialchars( $_REQUEST['file'] ) : null;

if ( empty( $file ) ) {
	die ( '<h1>Script Error</h1><p>You must specify a file path to download.</p>' );
}

// Required for IE, otherwise Content-Disposition is ignored
if ( ini_get( 'zlib.output_compression' ) ) {
	ini_set( 'zlib.output_compression', 'Off' );
}

// Get the file extension to serve the correct Content-Type
$ext = pathinfo( $file, PATHINFO_EXTENSION );
switch ( $ext ) {
	case "pdf"  : $type = 'application/pdf'; break;
	case "exe"  : $type = 'application/octet-stream'; break;
	case "dmg"  : $type = 'application/octet-stream'; break;
	case "zip"  : $type = 'application/zip'; break;
	case "doc"  : $type = 'application/msword'; break;
	case "xls"  : $type = 'application/vnd.ms-excel'; break;
	case "ppt"  : $type = 'application/vnd.ms-powerpoint'; break;
	case "ogg"  : $type = 'application/ogg'; break;
	case "swf"  : $type = 'application/x-shockwave-flash'; break;
	case "xml"  : $type = 'application/xml'; break;
	case "xhtml": $type = 'application/xhtml+xml'; break;
	case "txt"  : $type = 'text/plain'; break;
	case "rtf"  : $type = 'text/rtf'; break;
	case "htm"  : $type = 'text/html'; break;
	case "html" : $type = 'text/html'; break;
	case "bmp"  : $type = 'image/bmp'; break;
	case "gif"  : $type = 'image/gif'; break;
	case "png"  : $type = 'image/png'; break;
	case "jpeg" : $type = 'image/jpg'; break;
	case "jpg"  : $type = 'image/jpg'; break;
	case "tif"  : $type = 'image/tiff'; break;
	case "tiff" : $type = 'image/tiff'; break;
	case 'aif'  : $type = 'audio/x-aiff'; break;
	case 'mp3'  : $type = 'audio/mpeg'; break;
	case 'm4a'  : $type = 'audio/mp4a-latm'; break;
	case 'midi' : $type = 'audio/midi'; break;
	case 'wav'  : $type = 'audio/x-wav'; break;
	case 'm4v'  : $type = 'video/x-m4v'; break;
	case 'mp4'  : $type = 'video/mp4'; break;
	case 'mpeg' : $type = 'video/mpeg'; break;
	case 'mpg'  : $type = 'video/mpeg'; break;
	case 'mov'  : $type = 'video/quicktime'; break;
	case 'avi'  : $type = 'video/x-msvideo'; break;
	default     : $type = 'application/force-download';
}
header( 'Content-Type: ' . $type );

// Set the remaining required headers
header( 'Content-Description: File Transfer' );
header( 'Content-Disposition: attachment; filename=' . basename( $file ) . ';' );
header( 'Content-Transfer-Encoding: binary' );
header( 'Expires: 0' );
header( 'Pragma: public' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Cache-Control: private', false ); // Required for certain browsers
header( 'Robots: none' );

// Attempt to fetch the filesize
if ( $filesize = filesize( $file ) ) {
	header( 'Content-Length: ' . $filesize );
}

// Fetch the file for download
@readfile( $file );
exit;
