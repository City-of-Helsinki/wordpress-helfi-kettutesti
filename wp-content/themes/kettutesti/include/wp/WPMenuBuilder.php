<?php


/**
 * Builds HB renderable menu objects from WP menu class instance.
 *
 */
class WPMenuBuilder {

	private static function parse( array &$elements, $parentId = 0 ) {
		$branch = array();
		foreach ( $elements as &$element ) {
			if ( $element->menu_item_parent == $parentId ) {
				$children = self::parse( $elements, $element->ID );
				if ( $children ) {
					$element->children = $children;
				}
				$branch [$element->ID] = $element;
				unset( $element );
			}
		}

		return $branch;
	}

	public static function buildTree( $menu_id ) {
		$items = wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) );

		return $items ? self::parse( $items, 0 ) : null;
	}

	public static function buildGroups( $menu_id ) {
		$result = [
			'root' => [],
			'sub'  => [],
		];

		$items = wp_get_nav_menu_items( $menu_id );

		foreach ( $items as &$item ) {
			if ( $item->menu_item_parent == 0 ) {
				$keyPrimary = 'root';
				$keyId = $item->ID;
			} else {
				$keyPrimary = 'sub';
				$keyId = $item->menu_item_parent;
			}
			if ( !isset( $result [$keyPrimary] [$keyId] ) ) {
				$result [$keyPrimary] [$keyId] = [];
			}
			$result [$keyPrimary] [$keyId] [] = $item;
		}

		return $result;
	}

}
