<?php
l( 'Running '.__FILE__ );
get_header();
global $site;
$site->preparePageContent( 'layouts.hbs' );
get_footer();


