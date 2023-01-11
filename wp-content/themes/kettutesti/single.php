<?php
l( 'Running ' . __FILE__, LogTypes::$INFO );
get_header();

global $site;
$site->preparePageContent( 'single.hbs' );
get_footer();