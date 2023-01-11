<?php
ThemeLogger::log( 'Running ' . __FILE__, LogTypes::$INFO );
global $site;

get_header();
$site->preparePageContent( 'search.hbs' );
get_footer();