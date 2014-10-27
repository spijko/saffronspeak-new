<?php

// Using hooks is absolutely the smartest, most bulletproof way to implement things like plugins,
// custom design elements, and ads. You can add your hook calls below, and they should take the 
// following form:
// add_action('thesis_hook_name', 'function_name');
// The function you name above will run at the location of the specified hook. The example
// hook below demonstrates how you can insert Thesis' default recent posts widget above
// the content in Sidebar 1:
// add_action('thesis_hook_before_sidebar_1', 'thesis_widget_recent_posts');


// Get rid of empty p tags in posts
add_filter('the_content', 'remove_empty_p', 20, 1);
function remove_empty_p($content){
    $content = force_balance_tags($content);
    return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
}


// Disable certain plugins' ugly stylesheets
function remove_plugin_styles() {
    wp_dequeue_style('yarppRelatedCss');
    wp_deregister_style('yarppRelatedCss');
}
add_action('wp_footer', remove_plugin_styles);

// Re-queue Sharedaddy JS
function tweakjp_add_sharing_js() {
    wp_enqueue_script( 'sharing-js', WP_SHARING_PLUGIN_URL . 'sharing.js', array( ), 3 );
}
add_action( 'wp_enqueue_scripts', 'tweakjp_add_sharing_js' );


// Add custom JS 
function custom_scripts() {
    wp_enqueue_script( 'scripts', get_template_directory_uri() . '/custom/scripts.js', array('jquery'), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'custom_scripts' );


// Enable featured images/post thumbnails

add_theme_support('post-thumbnails');

function add_custom_sizes() {
    add_image_size('yarpp-thumbnail', 400, auto, true);
}
add_action('after_setup_theme','add_custom_sizes');

define('YARPP_GENERATE_THUMBNAILS', true); 


function auto_featured_image() {
    global $post;

    if (!has_post_thumbnail($post->ID)) {
        $attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
        
      if ($attached_image) {
              foreach ($attached_image as $attachment_id => $attachment) {
                   set_post_thumbnail($post->ID, $attachment_id);
              }
         }
    }
}
// Use it temporarily to generate all featured images
add_action('the_post', 'auto_featured_image');
// Used for new posts
add_action('save_post', 'auto_featured_image');
add_action('draft_to_publish', 'auto_featured_image');
add_action('new_to_publish', 'auto_featured_image');
add_action('pending_to_publish', 'auto_featured_image');
add_action('future_to_publish', 'auto_featured_image');



// Remove sharing links from post excerpts
add_action( 'init', 'my_remove_filters_func' );

function my_remove_filters_func() {
     remove_filter( 'the_excerpt', 'sharing_display', 19 );
}

// Add a "more" link to excerpts
function new_excerpt_more($more) {
    global $post;
    return '&hellip;';
}
add_filter('excerpt_more', 'new_excerpt_more');





/*****************************
     SERIES-SPECIFIC STYLES
******************************/

// Set featured category here
$featured_series = 128;
$featured = get_term_by('id', $featured_series, 'category');


// Return a more logical slug for categories (will relate to folder locations in theme)
function smarter_slug($category) {
    $slug = strtolower($category->name);
    $slug = str_replace('series', '', $slug);
    $slug = str_replace(' ', '-', trim($slug));
return $slug;   
}


// Add page-specific stylesheets and tags
function set_custom_styles() {
    
    // for individual post pages
    if (is_single()): 
        $categories = get_the_category($post->ID);
        foreach($categories as $category): 
            if ($category->slug != "shopping-guides"):
                $slug = smarter_slug($category);
            endif;
        endforeach;
    endif;

    // for category archive pages 
    if (is_category()):
        $slug = get_category(get_query_var('cat'))->slug;
    endif;

    // for homepage
    if (is_front_page()):
        $slug = 'thanksgiving';
    endif;

    wp_register_style('category-style',  get_template_directory_uri() . '/custom/series/'.$slug.'/styles.css');
    wp_enqueue_style('category-style');
}

add_action( 'wp_enqueue_scripts', 'set_custom_styles' );



// Add classes to different pages for styling purposes
function category_class($classes) {
    
    // for individual post pages
    if (is_single()): 
        $categories = get_the_category($post->ID);
        foreach($categories as $category): 
            $classes[] = smarter_slug($category);
        endforeach;
    $classes[] = "single-post";
    endif;

    // for category archive pages 
    if (is_category()):
        $classes[] = get_category(get_query_var('cat'))->slug;
        $classes[] = "post-archive";
    endif;

    // for the homepage
    if (is_front_page()):
        $classes[] = "homepage";
    endif;

return $classes;
}

add_filter('thesis_body_classes', 'category_class');








/*****************************
           HEADER
******************************/

// This replaces the header with a custom header to match the catalog site

function custom_header() { ?>
		<a id="logo" href="http://www.saffronmarigold.com"><img src="<?php bloginfo('stylesheet_directory'); ?>/custom/images/saffronmarigold.gif" border="0" alt="Saffron Marigold" title=" Saffron Marigold " width="324" height="90"></a>

		<form id="newsletter-signup" name="emailSignUp" method="post" action="http://www.saffronmarigold.com/catalog/email_signup.php">        
    		Sign up for exclusive savings &amp; announcements
    		<br><input name="emailAddress" type="text" value="Email Address" size="18" maxlength="100" class="main" onFocus="if(this.value=='Email Address')this.value='';" onFocusout="if(this.value=='')this.value='Email Address';">&nbsp;
		<input type="image" src="http://saffronmarigold.com/catalog/images/gosign_up.gif" alt="Go" border="0">
    	</form>

    	<div id="contact-cart">
			<img src="http://saffronmarigold.com/catalog/images/saffronmarigold_small_dark.gif" width="20" height="20"> Contact us: <b>877-749-9948</b> 
			or <a href="http://www.saffronmarigold.com/catalog/contact_us.php"><u>email</u></a>
			
        	<br>

        	<img src="http://saffronmarigold.com/catalog/images/saffronmarigold_small_light.gif" width="20" height="20">
        	<a href="http://www.saffronmarigold.com/catalog/account.php" >Sign in</a> | 

        	<img src="http://saffronmarigold.com/catalog/images/shopping_bag_small.gif">
        	<a href="http://www.saffronmarigold.com/catalog/shopping_cart.php">Shopping Bag</a> |

        	<a href="http://www.facebook.com/SaffronMarigold" rel="nofollow" target="_blank"><img src="http://saffronmarigold.com/catalog/images/icons/facebook.gif" align="bottom" border="0" /></a>
    		<a href="http://twitter.com/saffronmarigold" rel="nofollow" target="_blank"><img src="http://saffronmarigold.com/catalog/images/icons/twitter.gif" align="bottom" border="0" /></a>
        </div>
<?php 
}

remove_action('thesis_hook_header', 'thesis_default_header');
add_action('thesis_hook_header', 'custom_header');




// This replaces the menu with a custom menu to match the catalog site

function custom_menu() { ?>

<ul id="main-nav">
		
		<li><a href="http://www.saffronmarigold.com/catalog/gateway.php?cPath=22">Bed<br>Spreads</a>
			<ul> 
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=22_30">Twin Bedspreads</a></li>
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=22_31">Queen Bedspreads</a></li>
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=22_32">King Bedspreads</a></li>
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=22_33">Quilted Bedspreads</a></li>
			</ul>
		</li>
		
		<li><a href="http://www.saffronmarigold.com/catalog/gateway.php?cPath=21">Duvet<br>Covers</a>
			<ul> 
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=21_34">Twin Duvet Covers</a></li>
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=21_35">Queen Duvet Covers</a></li>
			  	<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=21_36">King Duvet Covers</a></li>
			</ul>
		</li>
		
		<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=24">Shower<br>Curtains</a></li>
		
	    <li><a href="http://www.saffronmarigold.com/catalog/gateway.php?cPath=25">Sheer<br>Curtains</a>
			<ul> 
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=25">Sheer Curtain Panels</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=26">Beaded Valances</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=62">Kitchen Curtains</a></li>
			</ul>
	    </li>
	    
	    <li><a href="http://www.saffronmarigold.com/catalog/gateway.php?cPath=43">Table<br>Linen</a>
	    	<ul> 
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=43_29">Tablecloth Rectangular</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=43_52">Tablecloth Round</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=43_45">Dinner Napkins</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=43_56">Table Runner</a></li>
			</ul>
	    </li>
	    
	    
	    <li><a href="http://www.saffronmarigold.com/catalog/gateway.php?cPath=23">Pillow<br>Covers</a>
	    	<ul> 
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=23">Pillow Shams</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=28_38">Euro Shams</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=28_37">Decorative Throws</a></li>
				<li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=23_63">Boudoir Shams</a></li>
			</ul>
	    </li>
	    
	    <li id="sale-tab"><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=60">SALE</a></li>
	    
	    <li id="shopby-tab"><a href="http://www.saffronmarigold.com/catalog/shop_by_print.php?cPath=59">Shop&nbsp;by<br>Print</a>
		    <ul>
		    	<li><a href="http://www.saffronmarigold.com/catalog/shop_by_print.php?cPath=59">Shop by Print</a>
			    <li><a href="http://www.saffronmarigold.com/catalog/directory.php?cPath=58">Shop by Swatches</a></li>
		    </ul>
	    </li>
	    
	    <li id="blog-tab" class="active"><a href="/blog">Blog</a>
	    	<ul>
                <?php
                $categories = get_categories(array('parent' => 0, 'exclude' => 1));
                foreach ( $categories as $category ):
                    echo '<li class="cat-item"><a href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a></li>';
                endforeach;
                ?>
				<li class="rss"><a href="http://feeds.feedburner.com/SaffronMarigoldBlog" title="Saffron Marigold Blog RSS Feed" rel="nofollow">Subscribe</a></li>
			</ul>
	    </li>
	</ul>

<?php
}

// replace with custom menu and move below header
remove_action('thesis_hook_before_header', 'thesis_nav_menu');
add_action('thesis_hook_after_header', 'custom_menu');



// Add location-aware breadcrumbs for improved usability

function thesis_breadcrumbs() {
    if (!is_home() && !is_front_page()): {
        echo '<div class="breadcrumbs">';
        echo '<a href="';
        echo get_option('home');
        echo '">';
        //bloginfo('name');
        echo 'Blog';
        echo "</a>";
            
            if (is_single()):
                echo " &raquo; ";
                the_category(' &raquo; ', 'multiple');
                echo " &raquo; ";
                the_title();
            
            elseif (is_category()):
                echo " &raquo; ";
                // get a list of the category's parent
                $category_list = get_category_parents(get_query_var('cat'), true, ' &raquo; ' );
                // remove current category from list (in order to display without a link or trailing arrow)
                $categories = explode(' &raquo; ', $category_list);
                array_pop($categories);
                array_pop($categories);
                foreach ($categories as $category):
                    echo $category ." &raquo; ";
                endforeach;                
                // and display current category name, without a link or trailing arrow
                echo get_the_category_by_id(get_query_var('cat'));
           
            elseif (is_page()):
                echo " &raquo; ";
                echo the_title();
            
            elseif (is_search()):
                echo " &raquo; Search results for: ";
                echo '"<em>';
                echo the_search_query();
                echo '</em>"';
            endif;
        }
        echo '</div>';
    endif;
}

add_action('thesis_hook_after_header','thesis_breadcrumbs');







/*****************************
           HOMEPAGE
******************************/

// This creates an entirely different layout for the homepage

function new_homepage() {
    global $featured;
    if (is_home() || is_front_page()): ?>
        <div id="content" class="home-content">
            
            <h2><?php echo bloginfo('title'); ?></h2>
            <p class="tagline"><?php echo bloginfo('description'); ?></p>
            <?php echo show_categories(); ?> 

            <?php echo featured_series(smarter_slug($featured), $featured->slug); ?>

            <h2>Read more posts</h2>
            <?php echo list_posts('latest'); ?>

            <?php if (function_exists('wpp_get_mostpopular'))
                wpp_get_mostpopular("range=monthly&limit=10");
            ?>

    <?php endif; 
}

remove_action('thesis_hook_custom_template', 'thesis_custom_template_sample');
add_action('thesis_hook_custom_template', 'new_homepage');


// Show categories widget
function show_categories($args = array('parent' => 0, 'exclude' => 1)) {
    $categories = get_categories($args);
    foreach($categories as $category):
        ?>
        <div class="category <?php echo $category->slug; ?>">
            <a href="<?php echo get_category_link( $category->term_id ); ?>" title="<?php echo sprintf( __( "View all $category->count posts in %s" ), $category->name ); ?>"><img src="<?php bloginfo(stylesheet_directory); ?>/custom/images/categories/<?php echo $category->slug; ?>.jpg"></a>
            <h3><a href="<?php echo get_category_link( $category->term_id ); ?>"><?php echo $category->name; ?></a></h3>
            <p><?php echo str_replace('#', get_category_link($category->term_id), $category->description); ?></p>
        </div>
    <?php 
    endforeach; 
}


// Get custom link for series index (parent or category index)
function get_parent_post_link($category_id) {
    
    // get link to parent post (should be sticky)
    $args = array(
        'cat' => $category_id,
        'post__in' => get_option( 'sticky_posts' ),
        'ignore_sticky_posts' => 1,
    );
    $cat_posts = $the_query = new WP_Query($args);

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $series_link = get_permalink();
        }
    
    // otherwise, just show a link to the category page
    } else {
        $series_link = get_category_link($category->term_id);
    }
    wp_reset_postdata();
    return $series_link;
}


// Show custom HTML for featured series
function featured_series($slug, $seo_slug=false) {
    $dir = plugin_dir_path( __FILE__ );
    $template = parse_url(get_bloginfo('template_directory'));
    $path = $template['path']."/custom/series/".$slug;
    if ($seo_slug) {
        $category = get_category_by_slug($seo_slug);
    } else {
        $category = get_category_by_slug($slug);
    }

    $series_link = get_parent_post_link($category->term_id);

    echo '<h2>Featured series: <a href="'.$series_link.'">'. $category->name . '</a></h2>';
    echo '<div class="featured-series '.$slug.'">';
    include($dir."/series/".$slug."/".$slug.".php"); 
    echo '</div>';
}

// List posts widget
function list_posts($type, $number=10) {
    ?>
    <div class="post-list <?php echo $type; ?>">
        <h3><?php echo $type; ?> Posts</h3>
        <?php
        global $post;
        if ($type === "latest") {
            $args = array('posts_per_page' => $number, 'orderby' => 'post_date', 'order' => 'DESC', 'post_type' => 'post');
        } elseif ($type === "favorite") {
            $args = array('posts_per_page' => $number, 'orderby' => 'comment_count', 'order' => 'DESC', 'post_type' => 'post');
        }
        $recent_posts = get_posts($args);
        foreach($recent_posts as $post):
            setup_postdata($post);
            preview_post($post);
            wp_reset_postdata();
        endforeach; ?>
    </div>
<?php
}


// Show custom popular posts widget
function custom_popular_posts_list($mostpopular, $instance) {
    ?>
    <div class="post-list favourite">
        <h3>Most-loved Posts</h3>
        <?php 
        global $post;
        foreach($mostpopular as $popular):
            $post = get_post($popular->id); 
            setup_postdata($post);
            preview_post($post);
            wp_reset_postdata();
        endforeach; ?>
    </div> 
<?php
}
add_filter( 'wpp_custom_html', 'custom_popular_posts_list', 10, 2 );


// Format post previews in lists
function preview_post($post) {
    ?>
    <div class="post-preview">
        <a href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) { the_post_thumbnail(''); } ?></a>
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <p><?php the_advanced_excerpt('length=10&use_words=1&no_custom=1&ellipsis=&finish_sentence=1'); ?></p>
    </div>
<?php
}




/*****************************
           ARCHIVE PAGES
******************************/

// Remove pagination for Shopping Guides parent
function nix_nav() {
    if (is_category()):
        if(get_query_var('cat') === 5):
            remove_action('thesis_hook_after_content', 'thesis_post_navigation');
        endif;
    endif;
}
add_action('thesis_hook_before_content','nix_nav');       


// Show custom archive pages for different archive types
class archive_looper extends thesis_custom_loop {

    function category() {

        // Determine what category is being shown, and generate category object & list of subcategories
        $category_id = get_query_var('cat');
        $category = get_category($category_id);
        $subcategories = get_categories('hide_empty=0&parent='.$category_id); 

        // Display a customized category intro panel, for top-level categories only 
        if ($category->category_parent === 0):
            ?>
            <header class="category-intro <?php echo $category->slug; ?>">
                <img src="<?php bloginfo(stylesheet_directory); ?>/custom/images/categories/<?php echo $category->slug; ?>.jpg">
                <div>
                    <h1><i class="icon-header-fleuron-left"></i><?php echo $category->name; ?><i class="icon-header-fleuron-right"></i></h1>
                    <p><?php echo str_replace('#', get_category_link($category->term_id), $category->description); ?></p>
                </div>
            </header>
        <?php else: ?>
            <section class="headline_area">
                <h1 class="entry-title"><?php echo $category->name; ?></h1>
            </section>
        <?php endif; 


        // If we're in the Design Resources category, display an expandable panel with a list of sub-categories
        if($category->name === "Design Resources"): 
            $series_subcategories = array_slice($subcategories, 0, 4);
            $print_subcategories = array_slice($subcategories, 4);
            ?>
            <a href="#" class="subcategory-expander-link"><i class="icon-caret-down"></i>By series or print</a>
            <section class="subcategory-expander">
                <div>
                    <section>
                        <h2><i class="icon-bullet-fleuron"></i>By series</h2>
                        <ul>
                    <?php
                    foreach ($series_subcategories as $subcategory):
                        echo '<li>'.$count.'<a href="'.get_category_link($subcategory->term_id).'">'.$subcategory->name.'</a></li>';
                    endforeach; ?>
                        </ul>
                    </section>

                    <section>
                        <h2><i class="icon-bullet-fleuron"></i>By print</h2>
                    <?php $count = 0;
                    foreach ($print_subcategories as $subcategory):
                        if ($count % 3 === 0) { echo '<ul>'; }
                        echo '<li><a href="'.get_category_link($subcategory->term_id).'">'.$subcategory->name.'</a></li>';
                        $count++;
                        if ($count % 3 === 0 or $count - 1 === count($print_subcategories)) { echo '</ul>'; } 
                    endforeach; ?>
                    </section>


                
                </div>
            </section>
        <?php endif; ?>


        <?php // If we're in the Shopping Guides category, display a styled block for each sub-category
        if($category->name === "Shopping Guides"):
            foreach ($subcategories as $subcategory):
                ?>
                <div class="subcategory <?php echo smarter_slug($subcategory); ?>">
                    <h2><?php echo $subcategory->name; ?></h2>
                    <?php if ($subcategory->name == "Fall"): ?>
                        <img class="badge" src="<?php bloginfo(stylesheet_directory); ?>/custom/images/new-for-2014.png" alt="New for 2014"/>
                    <?php elseif ($subcategory->name == "Thanksgiving"): ?>
                        <img class="badge" src="<?php bloginfo(stylesheet_directory); ?>/custom/images/updated-for-2014.png" alt="Updated for 2014"/>
                    <?php endif; ?>
                    <p class="read-more"><a href="<?php echo get_category_link($subcategory->term_id); ?>">Read more</a></p>
                    <a class="div-link" href="<?php echo get_category_link($subcategory->term_id); ?>"></a>
                </div>
            <?php endforeach; 


        // Otherwise, display a list of posts
        else:

            while (have_posts()):
                the_post();
                echo '<div class="post-excerpt">';
                if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
                        <?php the_post_thumbnail(''); ?>
                    </a>
                <?php endif; ?>
                    <div class="headline_area">
                        <?php echo post_meta(); ?>
                        <a href="<?php the_permalink() ?>"<?php echo '><h2 class="entry-title">' . get_the_title() . '</h2>' . "\n"?></a>
                    </div>
                    <div class="format_text entry-content">
                        <p><?php the_advanced_excerpt('length=40&use_words=1&no_custom=1&ellipsis=&finish_sentence=1'); ?></p>
                        <p class="read-more"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>">Read more</a></p>
                    </div>
                </div>
            <?php endwhile;
        endif;
    }
 
}
$the_looper = new archive_looper;



/*****************************
           POSTS
******************************/

// Remove post title for parent posts only 

function suppress_title() {
  return (!is_page() and is_sticky()) ? false : true;
}
add_filter('thesis_show_headline_area', 'suppress_title');


// This creates a custom instance of the byline/post meta boxes—publishing information on top, category information below

function post_meta() {
    if (!is_page() and !is_sticky()): ?>
        </section>
        <section class="post-meta">
            Published 
            <abbr class="published" title="<?php echo get_the_time('Y-m-d H:i'); ?>"><?php echo get_the_time(get_option('date_format')); ?></abbr>
            by <?php the_author_posts_link(); ?> 
        </section>

    <?php
    endif; 
}

add_action('thesis_hook_before_headline', 'post_meta');



// This adds series-specific navigation and text blocks to the bottom of posts

function series_navigation() {
    if (is_single() and !is_sticky()): 

        // Make sure we only have a single category to work with
        $categories = get_the_category($post->ID);
        if (count($categories) === 1) {
            $category = $categories[0];
        }

        $series_link = get_parent_post_link($category->term_id);

        // Only show for Shopping Guide posts (at least for now!)
        if ($category->category_parent === 5): ?>
        <section class="panel series-navigation">

            <h3>Explore more of our <a href="<?php echo $series_link; ?>"><?php echo $category->name; ?></a> series</h3>
            <p><?php echo $category->description; ?></p>
            <?php $previous_post = get_adjacent_post(true, '', true); ?>
            <?php if (!empty($previous_post)): ?>
                <div class="previous-post">
                    <span>&laquo; Previous post in series</span>
                    <a href="<?php echo get_permalink($previous_post->ID); ?>"><?php echo $previous_post->post_title; ?></a>
                </div>
            <?php endif; ?>

            <?php $next_post = get_adjacent_post(true, '', false); ?>
            <?php if (!empty($next_post)): ?>
                <div class="next-post">
                    <span>Next post in series &raquo;</span>
                    <a href="<?php echo get_permalink($next_post->ID); ?>"><?php echo $next_post->post_title; ?></a>
                </div>
            <?php endif; ?>
        </section>
        <?php endif;

    endif;
}
add_action('thesis_hook_after_post', 'series_navigation', '1');



// This adds tags to the bottom of posts, and moves YARPP below the tags

function post_tags() {
    if (is_single()): ?>
        <section class="post-tags">
            <span>Find related posts by tags: </span> <?php echo get_the_tag_list('', ' &middot; ', ''); ?>
        </section>

    <?php
    add_action('thesis_hook_after_post', 'related_posts', '2');
    endif; 
}

add_action('thesis_hook_after_post', 'post_tags', '1');




// This replaces the footer with a custom footer and search box

function custom_footer() { ?>

		<div id="my-search">
			<form method="get" class="search_form_visible" action="http://www.saffronmarigold.com/blog/">
				<input class="text_input" type="text" value="Enter Text &amp; Click Search" name="s" id="s" onfocus="if (this.value == 'Enter Text &amp; Click Search') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Enter Text &amp; Click Search';}" />
				<input type="submit" class="my-search" id="searchsubmit" value="SEARCH" />
			</form>
		</div>

	<p>Copyright &copy; <?php echo date('Y'); ?>  Saffron Marigold</p>  

<?php }

remove_action('thesis_hook_footer', 'thesis_attribution');
add_action('thesis_hook_footer', 'custom_footer');



/**
 * function custom_bookmark_links() - outputs an HTML list of bookmarking links
 * NOTE: This only works when called from inside the WordPress loop!
 * SECOND NOTE: This is really just a sample function to show you how to use custom functions!
 *
 * @since 1.0
 * @global object $post
*/

function custom_bookmark_links() {
	global $post;
?>
<ul class="bookmark_links">
	<li><a rel="nofollow" href="http://delicious.com/save?url=<?php urlencode(the_permalink()); ?>&amp;title=<?php urlencode(the_title()); ?>" onclick="window.open('http://delicious.com/save?v=5&amp;noui&amp;jump=close&amp;url=<?php urlencode(the_permalink()); ?>&amp;title=<?php urlencode(the_title()); ?>', 'delicious', 'toolbar=no,width=550,height=550'); return false;" title="Bookmark this post on del.icio.us">Bookmark this article on Delicious</a></li>
</ul>
<?php
}

/*
remove_action('thesis_hook_after_post', 'thesis_comments_link');
add_action('thesis_hook_after_headline', 'thesis_comments_link');
*/

/*
function fb_like() {
	if (is_single()) { ?>
		<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:450px; height:60px;"></iframe>
		<?php }
}
add_action('thesis_hook_after_post','fb_like');
*/


/*
function my_comments_link() {
  if (!is_single() && !is_page()) {
    echo '<p class="to_comments"><a href="';
    the_permalink();
    echo '#comments" rel="nofollow">';
    comments_number(__('Be the first to comment >', 'thesis'), __('<span>1</span> comment... add one >', 'thesis'), __('<span>%</span> comments... add one >', 'thesis'));
    echo '</a></p>';
  }
}
add_action('thesis_hook_after_post', 'my_comments_link');
*/
remove_action('thesis_hook_after_post', 'thesis_comments_link');


/* 2014-04-26 Sandip: Get rid of this. Hardly anyone subscribes to the blog anyway */
/**
function single_subscribe() { ?>
<!--  if (is_single()) { ?> -->
<!--
  <div id="singlesubscribe">
    <form action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=SaffronMarigoldBlog', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
    <span style="font-size:14px;font-weight:normal">You can also receive these posts via email..</span>
    <input class="txt" value="Enter email address here" onfocus="if (this.value == 'Enter email address here') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Enter email address here';}" name="email" type="text">
    <input name="uri" value="SaffronMarigoldBlog" type="hidden">
    <input value="en_US" name="loc" type="hidden"> 
    <input value="Subscribe" class="btn" type="submit" onclick="_gaq.push(['_trackEvent', 'Lead Capture', 'Subscribe', 'Blog Feedburner email subscription']);">
    </form>
    </div>
-->
    <?php
}

//add_action('thesis_hook_after_post_box', 'single_subscribe');
