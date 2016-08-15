<?php
class WordPress {
	public static function ttl() {
		return 60 * 60;
		// return 1;
	}

	public static function get_url() {
		return PROTOCOL . HOST;
	}

	public static function menu_item($obj) {
		$item = new \stdClass;
		$item->ID = intval($obj->db_id);
		$item->title = $obj->title;
		$item->attr_title = $obj->attr_title;
		// make internal links root relative
		$item->url = str_ireplace(self::get_url(), '/', $obj->url);
		// make external links protocol-less
		$item->url = str_ireplace(array('https://', 'http://'), array('//', '//'), $item->url);
		$item->target = $obj->target;
		$item->post_ID = intval($obj->object_id);
		$item->active = false;
		$item->children = [];

		if ($obj->menu_item_parent !== '0') {
			$item->parent = intval($obj->menu_item_parent);
		}
		return $item;
	}

	public static function menu_parent($menu, $id, $active) {
		foreach ($menu->children as $child) {
			if (isset($menu->children[$id])) {
				if ($active == true) {
					$menu->active = true;
				}
				return $menu->children[$id];
			} else {
				return self::menu_parent($child, $id, $active);
			}
		}
	}

	public static function menu($str, $post_id) {
		$items = wp_get_nav_menu_items($str);

		$menu = new \stdClass;
		$menu->children = [];

		foreach ($items as $item) {
			$t = self::menu_item($item);
			if ($t->post_ID == $post_id) {
				$t->active = true;
			}
			if (isset($t->parent)) {
				$parent = self::menu_parent($menu, $t->parent, $t->active);
				$parent->children[$t->ID] = $t;
				if ($t->active == true) {
					$parent->active = true;
				}
			} else {
				$menu->children[$t->ID] = $t;
			}
		}

		return $menu->children;
	}

	public static function check_route($url, $post_types = NULL) {
		if (!apc_exists('check_route_' . $url)) {
			$post = new \stdClass;
			// parse url variable
			$url = explode('/', $url);
			$url = $url[count($url) - 1];

			if (!isset($post_types)) {
				// get all post types
				$post_types = get_post_types();

				// remove unnecessary post types
				unset($post_types['nav_menu_item']);
				unset($post_types['revision']);
				unset($post_types['attachment']);
			}

			// get single post
			$query = new \WP_Query(array(
				'post_type' => $post_types,
				'posts_per_page' => 1,
				'name' => $url,
				'post_status' => 'publish'
			));

			if (count($query->posts) > 0) {
				$post->ID = $query->posts[0]->ID;

				if ('page' == $query->posts[0]->post_type) {
					$post->type = 'page';
					// send to page controller
				} else {
					$post->type = 'post';
					// send to post controller
				}
			}
			apc_store('check_route_' . $url, $post, self::ttl());
		} else {
			$post = apc_fetch('check_route_' . $url);
		}
		return $post;
	}

	public static function post($id) {

		if (!apc_exists('post_' . $id)) {
			global $post;
			$post = get_post($id);

			// setup_postdata( $post ); 

			if ($post->post_type !== 'page') {
				$prev = get_previous_post();

				if (!isset($prev->ID)) {
					$args = array(
						'post_type' => $post->post_type,
						'posts_per_page' => 1,
						'order' => 'DESC',
						'post_status' => 'publish'
					);
					$prev = new \WP_Query($args);
					$prev = $prev->posts[0];
				}
				$post->post_previous_url = str_ireplace(self::get_url(), '/', get_permalink($prev->ID));
				$post->post_previous_title = $prev->post_title;


				$next = get_next_post();
				if (!isset($next->ID)) {
					$args = array(
						'post_type' => $post->post_type,
						'posts_per_page' => 1,
						'order' => 'ASC',
						'post_status' => 'publish'
					);
					$next = new \WP_Query($args);
					$next = $next->posts[0];
				}
				$post->post_next_url = str_ireplace(self::get_url(), '/', get_permalink($next->ID));
				$post->post_next_title = $next->post_title;

			}

			$post->post_custom_fields = get_post_custom($id);
			$post->featured_image = [];
			$post->URL = str_ireplace(self::get_url(), '/', get_permalink($id));

			$sizes = get_intermediate_image_sizes();
			$sizes[] = 'full';
			$images = get_children('post_type=attachment&post_mime_type=image&post_parent=' . $post->ID);

			if (count($images) > 0) {
				foreach ($images as $imageID => $imagePost) {
					foreach ($sizes as $size) {
						$s = $size;
						$img = wp_get_attachment_image_src($imageID, $s, false);
						$img = $img[0];
						$post->featured_image[$s] = $img;
					}
				}
			}

			$post->post_meta = get_post_meta($post->ID);

			foreach ($post->post_custom_fields as $k => $v) {
				if (get_field($k, $post->ID)) {
					$post->post_custom_fields[$k] = get_field($k, $post->ID);

					if (is_string($post->post_custom_fields[$k])) {
						$post->post_custom_fields[$k] = str_ireplace(self::get_url(), '/', $post->post_custom_fields[$k]);
					}
				}
			}

			apc_store('post_' . $id, $post, self::ttl());
			// wp_reset_postdata();

		} else {
			$post = apc_fetch('post_' . $id);
		}
		return $post;
	}

	public static function query($params) {
		$results = [];
		$query = new \WP_Query($params);

		foreach ($query->posts as $p) {
			$results[] = self::post($p->ID);
		}

		return $results;
	}

}
$container['wordpress'] = new Wordpress();