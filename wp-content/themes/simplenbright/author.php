<?php get_header(); ?>
			
			<div id="content" class="clearfix">
			
				<h1><?php _e("Posts By", "site5framework"); ?> 
				 / <span><!-- google+ rel=me function -->
				<?php $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
				$google_profile = get_the_author_meta( 'google_profile', $curauth->ID );
				if ( $google_profile ) {
					echo '<a href="' . esc_url( $google_profile ) . '" rel="me">' . $curauth->display_name . '</a>'; ?></a>
				<?php } else { ?>
				<?php echo get_author_name(get_query_var('author')); ?>
				<?php } ?></span></h1>
				
				<div class="hrThickFull"></div>
			
				<div id="main" role="main">
					
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					
					<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
						
						<header>
							
							<time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate><span class="date"><?php the_time('d'); ?></span> <span class="month"><?php the_time('M'); ?></span></time>
							
							<h2 class="blogpost-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'site5framework' ); ?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
							
							<p class="meta"><?php _e("BY", "site5framework"); ?> <?php the_author_posts_link(); ?> <span class="amp"> </span> <?php _e("IN", "site5framework"); ?> <?php the_category(', '); ?> <span class="commentscount"><?php comments_popup_link(__('NO COMMENTS YET', 'site5framework'), __('1 COMMENT', 'site5framework'), __('% COMMENTS', 'site5framework')); ?></span><?php $post_tags = wp_get_post_tags($post->ID);
							if(!empty($post_tags)) {?>
								<span class="tags">
									<?php the_tags('', ', ', ''); ?>
								</span>
						<?php }?></p>
						
						</header> <!-- end article header -->
					
						<section class="post_content">
						
							<?php if ( has_post_thumbnail() ) {;?>
							
							<div class="postthumbnail">
								
								<img src="<?php echo get_template_directory_uri(); ?>/timthumb.php?src=<?php echo get_image_url(); ?>&amp;h=160&amp;w=160&amp;zc=1" alt="<?php the_title(); ?>">
							
							</div>
								
							<?php } else { ?>

							<?php }?>

						
							<?php the_excerpt(); ?>
					
						</section> <!-- end article section -->
						
						<footer>
							
						</footer> <!-- end article footer -->
					
					</article> <!-- end article -->
					
					<?php endwhile; ?>	
					
					<!-- begin #pagination -->
							<?php if (function_exists("wpthemess_paginate")) { wpthemess_paginate(); } ?>
					<!-- end #pagination -->
					
					<?php else : ?>
					
					<article id="post-not-found">
					    <header>
					    	<h1><?php _e("No Posts Yet", "site5framework"); ?></h1>
					    </header>
					    <section class="post_content">
					    	<p><?php _e("Sorry, What you were looking for is not here.", "site5framework"); ?></p>
					    </section>
					    <footer>
					    </footer>
					</article>
					
					<?php endif; ?>
			
				</div> <!-- end #main -->
    
				<?php get_template_part( 'blog', 'sidebar' ); ?>
    
			</div> <!-- end #content -->

<?php get_footer(); ?>