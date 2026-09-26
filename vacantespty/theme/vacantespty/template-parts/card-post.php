<article <?php post_class( 'post-card' ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'vpty-card', array( 'loading' => 'lazy' ) );
		} else {
			echo '<span class="post-card__placeholder">vacantes<b>pty</b></span>';
		}
		?>
	</a>
	<div class="post-card__body">
		<?php
		$cat = get_the_category();
		if ( $cat ) {
			echo '<span class="vpty-badge">' . esc_html( $cat[0]->name ) . '</span>';
		}
		?>
		<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
	</div>
</article>
