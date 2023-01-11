<?php

class Map {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
        if(isset($context['image']) && $context['image']['ID']) {
            $context['image'] = $sitemodel->getResponsiveImage( $context['image']['ID'] );
        }

	}
}
