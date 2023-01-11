<?php
l( 'Running ' . __FILE__ );
get_header();
global $site;
$site->preparePageContent( 'archive.hbs' );
get_footer();