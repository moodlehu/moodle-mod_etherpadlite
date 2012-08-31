<?php  //$Id: settings.php,v 1.2 2008/03/25 20:19:49 poltawski Exp $


$settings->add(new admin_setting_configtext('etherpadlite_url', get_string('url', 'etherpadlite'), 
				   get_string('urldesc', 'etherpadlite'), null, PARAM_RAW,40));
				   
$settings->add(new admin_setting_configtext('etherpadlite_apikey', get_string('apikey', 'etherpadlite'),
		           get_string('apikeydesc', 'etherpadlite'), null, PARAM_RAW,40));
		           
$settings->add(new admin_setting_configtext('etherpadlite_padname', get_string('padname', 'etherpadlite'), 
				   get_string('padnamedesc', 'etherpadlite'), null));
		           
$settings->add(new admin_setting_configtext('etherpadlite_cookiedomain', get_string('cookiedomain', 'etherpadlite'),
				   get_string('cookiedomaindesc', 'etherpadlite'), null));
$settings->add(new admin_setting_configtext('etherpadlite_cookietime', get_string('cookietime', 'etherpadlite'), 
				   get_string('cookietimedesc', 'etherpadlite'), null));
				   
$settings->add(new admin_setting_configcheckbox('etherpadlite_ssl', get_string('ssl', 'etherpadlite'),
				   get_string('ssldesc', 'etherpadlite'), '0'));

$settings->add(new admin_setting_configcheckbox('etherpadlite_adminguests', get_string('adminguests', 'etherpadlite'),
				   get_string('adminguestsdesc', 'etherpadlite'), '0'));

?>