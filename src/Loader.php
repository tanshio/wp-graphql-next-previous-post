<?php

namespace WPGraphQL\Extensions\NextPreviousPost;

use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;

/**
 * Class Loader
 *
 * This class allows you to see the next and previous posts in the 'post' type.
 *
 * @package WNextPreviousPost
 * @since   0.1.0
 */
class Loader
{
	public static function init()
	{
		define('WP_GRAPHQL_NEXT_PREVIOUS_POST', 'initialized');
		(new Loader())->bind_hooks();
	}

	public function bind_hooks()
	{
		add_action(
			'graphql_register_types',
			[$this, 'npp_action_register_types'],
			9,
			0
		);

	}

	public function npp_action_register_types()
	{
		$args = [
			'show_in_nav_menus' => true,
		];
		$post_types = \WPGraphQL::get_allowed_post_types($args);
		if (!empty($post_types) && is_array($post_types)) {

			foreach ($post_types as $post_type) {
				$post_type_object = get_post_type_object($post_type);
				if (isset($post_type_object->graphql_single_name)) {
					$name = $post_type_object->graphql_single_name;
					register_graphql_field($name, 'next', [
						'type' => 'Post',
						'description' => __(
							'Next post'
						),
						'resolve' => function (Post $_post, array $args, AppContext $context) {
							global $post;

							// get post
							$post = get_post($_post->ID, OBJECT);

							// setup global $post variable
							setup_postdata($post);

							$next = get_next_post();
							$post_id = $post->ID;

							wp_reset_postdata();

							if (!$next) {
								return null;
							}

							return DataSource::resolve_post_object($next->ID, $context);
						},
					]);

					register_graphql_field($name, 'previous', [
						'type' => 'Post',
						'description' => __(
							'Previous post'
						),

						'resolve' => function (Post $_post, array $args, AppContext $context) {
							global $post;

							// get post
							$post = get_post($_post->ID, OBJECT);

							// setup global $post variable
							setup_postdata($post);

							$prev = get_previous_post();

							wp_reset_postdata();

							if (!$prev) {
								return null;
							}

							return DataSource::resolve_post_object($prev->ID, $context);
						},
					]);
				}
			}
		}
	}
}
