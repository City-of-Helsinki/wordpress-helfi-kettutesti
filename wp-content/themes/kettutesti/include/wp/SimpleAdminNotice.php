<?php

class SimpleAdminNotice{

	private const NOTICE_OPTION_KEY = "simple_admin_notice";
	private static $notices = null;

	public static function init(){
		if( self::$notices !== null ){
			return;
		}
		self::$notices = get_option( self::NOTICE_OPTION_KEY );
		if( self::$notices === false ){
			self::$notices = [];
		}

		add_action( 'admin_notices', 'SimpleAdminNotice::onAdminNotices', 99 );
	}
	
	public static function add( $message ){
		self::$notices[] = $message;
		update_option( self::NOTICE_OPTION_KEY, self::$notices );
	}
	

	public static function onAdminNotices(){
		foreach( self::$notices as $notice ){
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', $notice );
		}

		if( !empty( self::$notices ) ){
			if( !delete_option( self::NOTICE_OPTION_KEY )){
				ThemeLogger::log("Cannot delete notice; force wp cache clear " . self::NOTICE_OPTION_KEY, LogTypes::$WARNING);
				global $wp_object_cache;
				return $wp_object_cache->flush();
			}
		}
	}

}


SimpleAdminNotice::init();
