<?php
/**
 * Template Name: ChatGPT Resource Center
 *
 * A custom page template for the ChatGPT Resource Center.
 *
 * @package GeneratePress Child Theme
 */

get_header(); ?>

<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
    <article class="post">
      <header class="entry-header">
        <h1 class="entry-title">ChatGPT Resource Center</h1>
        <p class="entry-subtitle">Essential Information, Tips, and Updates on ChatGPT</p>
      </header><!-- .entry-header -->
      
      <div class="entry-content">
        <!-- Introduction Section -->
        <section class="intro">
          <h2>Introduction</h2>
          <p>Explore everything you need to know about ChatGPT. Our Resource Center provides essential guides, practical tips, and the latest updates to help you use ChatGPT safely and effectively.</p>
        </section>
        
        <!-- Quick Guide Section -->
        <section class="quick-guide">
          <h2>Quick Guide</h2>
          <div class="guide-item">
            <h3>What is ChatGPT?</h3>
            <p>A conversational tool designed to help you interact, learn, and create content in a natural, human-like way.</p>
          </div>
          <div class="guide-item">
            <h3>How to Use ChatGPT</h3>
            <p>Follow simple steps and tips to get started with ChatGPT for various applications—whether for personal use or integration into your projects.</p>
          </div>
          <div class="guide-item">
            <h3>Is It Safe?</h3>
            <p>Learn about the safety considerations, best practices, and responsible usage guidelines for interacting with ChatGPT.</p>
          </div>
        </section>
        
        <!-- Blog Feed Section -->
        <section class="blog-feed">
          <h2>Latest Tips & Tutorials</h2>
          <?php
          // Query posts from the "ChatGPT" category (slug: ChatGPT)
          $args = array(
            'category_name'  => 'ChatGPT',
            'posts_per_page' => 5,
          );
          $query = new WP_Query( $args );
          if( $query->have_posts() ) :
            while( $query->have_posts() ) : $query->the_post(); ?>
              <article class="post-summary">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo wp_trim_words( get_the_content(), 20, '...' ); ?></p>
              </article>
            <?php endwhile;
            wp_reset_postdata();
          else : ?>
            <p>No posts found in the ChatGPT category.</p>
          <?php endif; ?>
        </section>
        
      </div><!-- .entry-content -->
      
      <footer class="entry-footer">
        <p><a href="<?php echo home_url('/'); ?>">Back to Home</a></p>
      </footer><!-- .entry-footer -->
      
    </article><!-- .post -->
  </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
?>