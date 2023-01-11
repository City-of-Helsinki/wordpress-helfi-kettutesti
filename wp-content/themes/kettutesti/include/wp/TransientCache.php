<?php


class TransientCache {
	public const TRANSIENT_MENUS = 'TRANS_MENUS';
	public const TRANSIENT_ATTACHMENT_SUFFIX = 'trans_img_';
	public const TRANSIENT_PAGE_SUFFIX = 'trans_page_';

	function __construct() {
		$this->addActionsForTransientDeletions();
	}

	protected function getKey(){
		if( defined('ICL_LANGUAGE_CODE') ){
			return ICL_LANGUAGE_CODE;
		}else{
			return '';
		}
	}

	private function addActionsForTransientDeletions() {
		//Delete navigation cache when navigation saved
		add_action( 'wp_update_nav_menu', function() {
			delete_transient( TransientCache::TRANSIENT_MENUS . $this->getKey() );
		}, 10 );

		//Delete media cache when attachment fields are updated
		add_filter( 'wp_insert_attachment_data', function( $fields, $post ) {
			if ( $post && isset( $post['ID'] ) ) {
				delete_transient( TransientCache::TRANSIENT_ATTACHMENT_SUFFIX . $post['ID'] );
			}

			return $fields;
		}, 10, 2 );
	}

	final public function getCached( $transientName ) {
		return get_transient( $transientName . $this->getKey() );
	}

	final public function setCached( $transientName, $data ) {
		set_transient( $transientName . $this->getKey(), $data, MONTH_IN_SECONDS );
	}
}