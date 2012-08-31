<?php

$string['etherpadlite'] = 'Etherpad Lite';

$string['modulename'] = 'Etherpad Lite';
$string['modulenameplural'] = 'Etherpad Lites';

$string['pluginadministration'] = 'etherpad lite administration';
$string['pluginname'] = 'Etherpad Lite';

$string['etherpadlitefieldset'] = 'Custom example fieldset';
$string['etherpadliteintro'] = 'Etherpadlite Intro';
$string['etherpadlitename'] = 'Etherpadlite Name';


// Admin Settings
$string['url'] = 'Server URL';
$string['urldesc'] = 'This is the URL to your Etherpadlite server in the form: http[s]://host[:port]/[subDir/]';

$string['padname'] = 'Padname for all instances';
$string['padnamedesc'] = 'A general padname can be helpful, if you want to find all instances from this installation on your etherpadlite server. Pad groups are generated autmatically';

$string['apikey'] = 'ApiKey';
$string['apikeydesc'] = 'This is the apiKey this module needs, to communicate with your server. \n This key is stored on your webserver';

$string['cookiedomain'] = 'Cookie Domain';
$string['cookiedomaindesc'] = 'Here you can enter the domain, which should be stored in the session cookie,
									  so that the etherpadlite server recognize it. When moodle runs on the domain moodle.example.com and your etherpadlite server on etherpadlite.example.com then your cookie domain should be .example.com, because the module will then delete old sessions.';

$string['cookietime'] = 'Session elapse time';
$string['cookietimedesc'] = 'Here you have to enter the time in seconds until the session should be valid';

$string['ssl'] = 'Https Support';
$string['ssldesc'] = 'With this set, your site will redirect itself to https, if an etherpad is opened';

$string['adminguests'] = 'Guests allowed to write?';
$string['adminguestsdesc'] = 'With this set, someone who is allowed to, can allow guests to write in his specific etherpadlite module';

// Pad Settings
$string['guestsallowed'] = 'Are Guests allowed to write?';
$string['guestsallowed_help'] = 'This determines, if a guests is allowed to write. If not, then he will only see the content';

// view
$string['summaryguest'] = 'You are logged in as guest. That\'s why you can only see the readonly version of this Pad. Reload the page to get new changes.';
?>
