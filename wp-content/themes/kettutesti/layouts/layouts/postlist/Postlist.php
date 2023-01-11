<?php

class Postlist {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
        if ( $context['posts'] ) {
            foreach ( $context['posts'] as &$wp_post ) {
                $wp_post->permalink = get_permalink( $wp_post->ID );
                $post_thumbnail_id = get_post_thumbnail_id( $wp_post->ID );
                $wp_post->image = $sitemodel->getResponsiveImage( $post_thumbnail_id );
            }
        }
	}
}
