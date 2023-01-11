<?php /* Template Name: Result-Page */ ?>

<?php
l('Running ' . __FILE__);
get_header();
global $site;
$site->preparePageContent('tmpl-resultpage.hbs');

get_footer();
