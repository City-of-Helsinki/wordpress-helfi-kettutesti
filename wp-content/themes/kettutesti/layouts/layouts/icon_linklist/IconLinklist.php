<?php

class IconLinklist {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
        if ( is_array( $context['listing'] ) ) {
            for ( $i = 0; $i < count( $context['listing'] ); $i++ ) {
            if(isset($context['listing'][$i]['image']) && $context['listing'][$i]['image']['ID']) {
            $context['listing'][$i]['image'] = $sitemodel->getResponsiveImage( $context['listing'][$i]['image']['ID'] );
        }

            }
        }
	}
}
