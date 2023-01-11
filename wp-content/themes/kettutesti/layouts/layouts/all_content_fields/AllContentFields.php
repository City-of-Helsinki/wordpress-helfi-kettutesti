<?php

class AllContentFields {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
        if(isset($context['image']) && $context['image']['ID']) {
            $context['image'] = $sitemodel->getResponsiveImage( $context['image']['ID'] );
        }

        if ( is_array( $context['gallery'] ) ) {
            for ( $i = 0; $i < count( $context['gallery'] ); $i++ ) {
                $context['gallery'][$i] = $sitemodel->getResponsiveImage( $context['gallery'][$i]['ID'] );
            }
        }
        if(isset($context['item-group']['kuva']) && $context['item-group']['kuva']['ID']) {
            $context['item-group']['kuva'] = $sitemodel->getResponsiveImage( $context['item-group']['kuva']['ID'] );
        }

        if ( $context['posts'] ) {
            foreach ( $context['posts'] as &$wp_post ) {
                $wp_post->permalink = get_permalink( $wp_post->ID );
                $post_thumbnail_id = get_post_thumbnail_id( $wp_post->ID );
                $wp_post->image = $sitemodel->getResponsiveImage( $post_thumbnail_id );
            }
        }
	}
}
