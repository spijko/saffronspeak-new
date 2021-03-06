<?php
/**
 * Series styling functions
 *
 * Different post series (categories) have unique styles applied.
 * We need to subvert how WordPress typically handles things for this.
 * Nothing too wild, but it calls for some special functions. These are them.
 *
 * @package Safflower
 */

/**
 * Get the featured series ID from the theme options. This is set
 * in the WordPress admin, via the Customizer panel. Alternatively,
 * you can set the featured series by manually entering its category ID below.
 */
$safflower_featured_series_id = get_theme_mod( 'safflower_featured_series' );
// Fetch the full category object for use.
$safflower_featured_series = get_term_by( 'id', $safflower_featured_series_id, 'category' );

/**
 * Categories often use complicated slugs: "dorm-decor-series-bedding-linen-ideas".
 * This is for SEO purposes, but can make things a bit confusing from a development
 * perspective. Instead, we're going to use simpler, more human-friendly slugs, based
 * on the actual category name. This resulting slug is then used for our file and folder
 * structures as well as our class names, so it makes sense to keep it simple.
 * Note that this means we can change the category slugs without any implication,
 * but that changing the title will require adjusting of the folder names and files.
 */
function safflower_smart_slug( $category ) {
  $slug = strtolower( $category->name );
  // Remove the "series" suffix if it exists. (Currently not used.)
  $slug = str_replace( 'series', '', $slug );
  $slug = str_replace( ' ', '-', trim( $slug ) );
  $slug = preg_replace( '/[^A-Za-z0-9\-]/', '', $slug ); // Strip special characters
  return $slug;
}


/**
* Get a link to the currently featured series.
* This function is primarily used on the homepage, in order to
* dynamically generate a link to the featured series.
*/
function safflower_featured_series_link( $slug ) {
  $category = get_category_by_slug( $slug );
  $series_link = '<a href="'. safflower_parent_post_link( $category->term_id ) . '">' . $category->name . '</a>';
  return $series_link;
}

/**
* Get a link to the "parent post" (sticky post) of a given "series" (category).
* Instead of using the category archive page, we use a custom post
* to list all the posts within that series. This allows us manual
* control of their display, order, and formatting from within WordPress.
* The parent post in a given category is marked as a sticky post in order
* differentiate it from all the other posts.
*
* This function returns the URL for a given category's parent post.
*/
function safflower_parent_post_link( $category_id ) {
  // Find all sticky posts in the category (there should only be one)
  $args = array(
    'cat'                 => $category_id,
    'post__in'            => get_option( 'sticky_posts' ),
    'ignore_sticky_posts' => 1,
    'orderby'             => 'date',
    'order'               => 'ASC',
  );
  $cat_posts = $the_query = new WP_Query( $args );

  /* If there's at least one sticky post, get its permalink.
   * Note that this technically loops through *all* sticky posts returned above
   * and returns a permalink, but we ordered the array above in ascending order
   * of publication date. If there are two sticky posts within the same category,
   * we'll end up returning a link to the most recently-published sticky post,
   * which is most likely to be the one we're looking for.
   */
  if ( $the_query->have_posts() ):
    while ( $the_query->have_posts() ) {
      $the_query->the_post();
      $series_link = get_permalink();
    }

  // If there are no sticky posts in the category, just show a link to the category page itself
  else:
    $series_link = get_category_link( $category_id );
  endif;
  wp_reset_postdata();
  return $series_link;
}


/**
* Enqueue series-specific stylesheets on single post pages and the homepage.
*
* Because each series uses its own unique fonts, there are going to be lots of HTTP requests going on.
* For the moment, we're simplifying things by ONLY loading the CSS for the particular series to which
* the current page belongs. This may not be the best way of doing this: it may instead be better to
* concatenating all series' stylesheets into our main style.css, thus removing the extra HTTP request.
* However, since this means all fonts (30 or so) would be loaded on every page, this may not be the
* most performant solution.
*
* @todo: A/B test performance.
*/
function safflower_series_styles() {
  global $safflower_featured_series;

  // Single posts
  if ( is_single() ):
    $categories = get_the_category( $post->ID );
    // For each of the post's categories, add its "slug" to the body class
    foreach( $categories as $category ):
      // If the category is a sub-category of the Holiday series, just use the "holidays" stylesheet
      if ( 130 === $category->parent ):
        $slug = "holidays";
      // Don't load a stylesheet for certain categories (@todo: Increase list of categories. Currently this creates 404 errors on some pages.)
      elseif ( "shopping-guides" != $category->slug ):
        $slug = safflower_smart_slug( $category );
      endif;
    endforeach;
  endif;

  // Homepage
  if ( is_front_page() ):
    $slug = safflower_smart_slug( $safflower_featured_series );
  endif;

  // Register our series-specific stylesheet
  if ( isset( $slug ) ) {
    wp_register_style( 'category-style',  get_template_directory_uri() . '/series/'.$slug.'/styles.css' );
    wp_enqueue_style( 'category-style' );
  }

  // Promotion pages also have specific stylesheets
  if ( "Promotions" == get_post_field( 'post_title', $post->post_parent ) ):
    $slug = basename( get_permalink() );
    wp_register_style( 'page-style',  get_template_directory_uri() . '/promotions/'.$slug.'.css' );
    wp_enqueue_style( 'page-style' );
  endif;
}
add_action( 'wp_enqueue_scripts', 'safflower_series_styles' );


/**
* Since we want to implement our styles based on the series (category) of a post, we need
* some way of indicating which series a particular post belongs to. To do this, we'll
* add an additional CSS class to the body tag for our custom styles to use. WordPress
* already natively adds them on certain pages, but we need a bit more.
*/
function safflower_series_class( $classes ) {
  if ( is_single() ):

    // Add category slugs to the body class
    $categories = get_the_category( $post->ID );
    foreach( $categories as $category ):
      // Generate a list of parent categories as well
      $parents = get_category_parents( $category, false, ',' );
      $parents = explode( ',', $parents );
      foreach ( $parents as $parent ):
        $string = str_replace( ' ', '-', strtolower( $parent ) ); // Replace spaces with hyphens & convert to lowercase
        $classes[] = preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Strip special characters
      endforeach;
    endforeach;

    // Add class to indicate whether this is the "parent" post
    if ( is_sticky() ):
      $classes[] = 'parent-post';
    endif;
  endif;
return $classes;
}
add_filter('body_class', 'safflower_series_class');


/**
* Add a navigation panel to the bottom of posts that allows users
* to easily navigate between posts of that series. This will need
* to be styled individually for each individual series.
*/
function safflower_series_nav() {
  if ( ! is_sticky() ): // Don't show the navigation on parent posts

    // Make sure we're only pulling a single category
    $categories = get_the_category( $post->ID );
    if ( 1 === count( $categories ) ) {
      $category = $categories[0];
    }

    // If we're in a nested sub-category (like in the Holiday series), we want to jump up to the parent category
    $parent = get_term_by( 'id', $category->category_parent, 'category' );
    if ( 0 != $parent->parent ) {
      $category = $parent;
    }

    /*
     * We only want to display the series navigation if this is, in fact, a series.
     * It also needs to have styles written for it, otherwise it'll look all funky.
     * To check if this is a styled series, we're going to see if the category has a
     * parent (sticky) post defined. If it does, we'll assume it's a styled series,
     * and we'll show the series nav.
     */
    $series_link = safflower_parent_post_link( $category->term_id );

    if ( get_category_link( $category->term_id ) !== $series_link ): ?>
      <section class="panel series-navigation">
        <p class="series-description"><?php echo $category->description; ?></p>

        <div class="previous-post">
          <?php
          /*
           * Since we're using sticky posts to determine the parent page, we don't want these to appear in our prev/next navigation.
           * So, if the previous post is sticky, or if it doesn't exist, we won't show a link to it.
           */
          $previous_post = get_adjacent_post( true, '', true ); ?>
          <?php if ( ! empty( $previous_post ) AND ! is_sticky( $previous_post->ID ) ): ?>
            <a href="<?php echo get_permalink( $previous_post->ID ); ?>">&laquo; Previous post</a>
          <?php endif; ?>
        </div>

        <div class="all-posts">
          <a href="<?php echo $series_link; ?>">View all <?php echo $category->name; ?> posts</a>
        </div>

        <div class="next-post">
          <?php
          /*
           * Since we're using sticky posts to determine the parent page, we don't want these to appear in our prev/next navigation.
           * So, if the next post is sticky, or if it doesn't exist, we won't show a link to it.
           */
          $next_post = get_adjacent_post( true, '', false );
          if ( ! empty( $next_post )  AND ! is_sticky( $next_post->ID ) ): ?>
            <a href="<?php echo get_permalink( $next_post->ID ); ?>">Next post &raquo;</a>
          <?php endif; ?>
        </div>

      </section>
    <?php endif;
  endif;
}
