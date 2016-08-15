<?php get_template_part('templates/page', 'header'); ?>

<?php if (have_posts()) : ?>
	
	<?php while (have_posts()) : the_post();
		$post_format = get_post_type();
		get_template_part('templates/content', $post_format);
	endwhile; ?>
	
<?php else : ?>

	<div class="alert alert-warning">
		<?php _e('Sorry, no results were found.', 'SM'); ?>
	</div>
	<?php get_search_form(); ?>
	
<?php endif; ?>

<?php if ($wp_query->max_num_pages > 1) : ?>
	<nav class="pagination">
		<div class="previous">
		<?php next_posts_link(__('Older posts', 'SM')); ?>
		</div>
		<div class="next">
		<?php previous_posts_link(__('Newer posts', 'SM')); ?>
		</div>
	</nav>
<?php endif; ?>