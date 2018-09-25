<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
// Copyright (C) 04/2007 Mathias Zuckermann &
//			 Bernhard Heibler
//
// See COPYING for license info
//
////////////////////////////////////////////////////
//
// This is a library, that by including it,
// automatically determines the proper language
// to use and includes the language file.

// First define an array of all possible
// languages:
$languages = array('en' => 'English', 'de' => 'German', 'fr' => 'French');

// Make sure that the language string we have is
// a valid one:
if (!(in_array($lang, array_keys($languages)))) {
  die("ERROR: Bad Language String Provided!");
}

// Now include the appropriate language file:
require_once "{$lang}.php";

// As one last step, create a function
// that can be used to output language
// options to the user:
function switch_language_options() {
  // Include a few globals that we will need:
  global $text, $languages, $lang;

  // Start our string with a language specific
  // 'switch' statement:
  $retval = $text['switch'];

  // Loop through all possible languages to
  // create our options.
  $get = $_GET;
  foreach ($languages as $abbrv => $name) {
    // Create the link, ignoring the current one.
    if ($abbrv !== $lang) {
      // Recreate the GET string with
      // this language.
      $get['lang'] = $abbrv;
      $url = $_SERVER['PHP_SELF'] . '?' .
        http_build_query($get);
      $retval .= " <a href=\"{$url}\">
        {$name}</a>";
    }
  }

// Now return this string.
  return $retval;
}
?>
