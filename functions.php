<?php

$theme_version = '2.1';

	/**
	 * Include Theme Customizer
	 *
	 * @since v1.0
	 */
	$theme_customizer = get_template_directory() . '/inc/customizer.php';
	if ( is_readable( $theme_customizer ) ) {
		require_once( $theme_customizer );
	}

	/**
	 * Include Support for wordpress.com-specific functions.
	 *
	 * @since v1.0
	 */
	$theme_wordpresscom = get_template_directory() . '/inc/wordpresscom.php';
	if ( is_readable( $theme_wordpresscom ) ) {
		require_once( $theme_wordpresscom );
	}


	/**
	 * Set the content width based on the theme's design and stylesheet
	 *
	 * @since v1.0
	 */
	if ( ! isset( $content_width ) ) {
		$content_width = 800;
	}


	/**
	 * Define Menu name (needed for Routing)
	 *
	 * @since v1.0
	 */
	function polyspa_get_menu_items( $menu_name ) {
		
		$menu_items_pages = array();
		if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[$menu_name] ) ) {

			$menu = wp_get_nav_menu_object( $locations[$menu_name] );
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			$count = 0;
			
			foreach ( (array) $menu_items as $key => $menu_item ) {

				$id = $menu_item->object_id; // = Page ID
				
				$post_data = get_post( $id, ARRAY_A );
				$slug = $post_data['post_name']; // = Page Slug
				
				// $url = $menu_item->url;
				$url = polyspa_site_base() . '/' . $slug;
				$title = apply_filters( 'the_title', $menu_item->title );

				if ( $id == get_option( 'page_on_front' ) ) {
					$slug = 'index';
					$url = polyspa_site_base() . '/';
				}

				array_push( $menu_items_pages, array( 'pageid' => $id, 'title' => $title, 'url' => $url, 'slug' => $slug, 'index' => $count ) ); // e.g. $value["pageid"]
				
				$count++;
			}

			return $menu_items_pages; // All pages in menu

		}
		
	}


	/**
	 * General Theme Settings
	 *
	 * @since v1.0
	 */
	if ( ! function_exists( 'polyspa_setup_theme' ) ) :
		function polyspa_setup_theme() {

			// Make theme available for translation: Translations can be filed in the /languages/ directory
			load_theme_textdomain( 'polyspa', get_template_directory() . '/languages' );

			// Theme Support
			add_theme_support( 'title-tag' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			) );

			// Date/Time Format
			$theme_dateformat = get_option( 'date_format' );
			$theme_timeformat = 'H:i';

			// Default Attachment Display Settings
			update_option( 'image_default_align', 'none' );
			update_option( 'image_default_link_type', 'none' );
			update_option( 'image_default_size', 'large' );

			// Custom CSS-Styles of Wordpress Gallery
			add_filter( 'use_default_gallery_style', '__return_false');

		}
		add_action( 'after_setup_theme', 'polyspa_setup_theme' );
	endif;


	/**
	 * Add title tag if < 4.1: https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1
	 *
	 * @since v1.0
	 */
	if ( ! function_exists( 'polyspa_render_title' ) ) :
		function polyspa_render_title() {
		?>
			<title><?php wp_title( '|', true, 'right' ); ?></title>
		<?php
		}
		add_action( 'wp_head', 'polyspa_render_title' );
	endif;


	/**
	 * Allow SVG in Media
	 *
	 * @since v1.0
	 */
	if ( ! function_exists( 'polyspa_upload_mimes' ) ) :
		function polyspa_upload_mimes( $mime_types = array() ) {
			$mime_types['svg'] = 'image/svg+xml';
			return $mime_types;
		}
		add_filter( 'upload_mimes', 'polyspa_upload_mimes' );
	endif;


	/**
	 * Add new User fields to Userprofile
	 *
	 * @since v1.0
	 */
	if ( ! function_exists( 'polyspa_add_user_fields' ) ) :
		function polyspa_add_user_fields( $fields ) {
			// Add new fields
			$fields['facebook_profile'] = 'Facebook URL';
			$fields['twitter_profile'] = 'Twitter URL';
			$fields['google_profile'] = 'Google+ URL';
			$fields['linkedin_profile'] = 'LinkedIn URL';
			$fields['xing_profile'] = 'Xing URL';
			$fields['github_profile'] = 'GitHub URL';

			return $fields;
		}
		add_filter( 'user_contactmethods', 'polyspa_add_user_fields' ); // get_user_meta( $user->ID, 'facebook_profile', true );
	endif;


	/**
	 * Test if a page is a blog page
	 * if ( is_blog() ) { ... }
	 *
	 * @since v1.0
	 */
	function is_blog() {
		global $post;
		$posttype = get_post_type( $post );
		return ( ((is_archive()) || (is_author()) || (is_category()) || (is_home()) || (is_single()) || (is_tag())) && ( 'post' === $posttype ) ) ? true : false ;
	}


	/**
	 * Get the page number
	 *
	 * @since v1.0
	 */
	function get_page_number() {
		if ( get_query_var( 'paged' ) ) {
			print ' | ' . __( 'Page ' , 'polyspa') . get_query_var( 'paged' );
		}
	}


	/**
	 * Get Site base (needed for Routing)
	 *
	 * @since v1.0
	 */
	function polyspa_site_base() {
		$site_url = network_site_url(); // http://www.domain.tld
		$home_url = home_url(); // http://www.domain.tld/base

		if ( $site_url !== $home_url ) {
			return '/' . str_replace( $site_url, '', $home_url ); // "/base"
		} else {
			return '';
		}
	}


	/**
	 * Disable comments for Media (Image-Post, Jetpack-Carousel, etc.)
	 *
	 * @since v1.0
	 */
	function polyspa_filter_media_comment_status( $open, $post_id ) {
		$media_post = get_post( $post_id );
		if ( 'attachment' === $media_post->post_type ) {
			return false;
		}
		return $open;
	}
	add_filter( 'comments_open', 'polyspa_filter_media_comment_status', 10 , 2 );


	/**
	 * Style Edit buttons as badges: http://getbootstrap.com/components/#badges
	 *
	 * @since v1.0
	 */
	function polyspa_custom_edit_post_link( $output ) {
		$output = str_replace( 'class="post-edit-link"', 'class="post-edit-link badge badge-info"', $output );
		return $output;
	}
	add_filter( 'edit_post_link', 'polyspa_custom_edit_post_link' );


	/**
	 * Responsive oEmbed filter: http://getbootstrap.com/components/#responsive-embed
	 *
	 * @since v1.0
	 */
	function polyspa_oembed_filter( $html, $url, $attr, $post_id ) {
		$return = '<div class="embed-responsive embed-responsive-16by9">' . $html . '</div>';
		return $return;
	}
	add_filter( 'embed_oembed_html', 'polyspa_oembed_filter', 10, 4 );


	if ( ! function_exists( 'polyspa_content_nav' ) ) :
		/**
		 * Display a navigation to next/previous pages when applicable: http://getbootstrap.com/components/#pagination-pager
		 *
		 * @since v1.0
		 */
		function polyspa_content_nav( $nav_id ) {
			global $wp_query;

			if ( $wp_query->max_num_pages > 1 ) : ?>
				<div class="clearfix"></div>
				<ul id="<?php echo $nav_id; ?>" class="pager">
					<li><?php next_posts_link( '<span aria-hidden="true">&larr;</span> ' . __( 'Older posts', 'polyspa' ) ); ?></li>
					<li><?php previous_posts_link( __( 'Newer posts', 'polyspa' ) . ' <span aria-hidden="true">&rarr;</span>' ); ?></li>
				</ul><!-- /.pager -->
			<?php
			else :
				echo '<div class="clearfix"></div>';
			endif;
		}

		// Add Class
		function posts_link_attributes() {
			return 'class="btn btn-default"';
		}
		add_filter( 'next_posts_link_attributes', 'posts_link_attributes' );
		add_filter( 'previous_posts_link_attributes', 'posts_link_attributes' );

	endif; // content navigation


	if ( ! function_exists( 'polyspa_article_posted_on' ) ) :
		/**
		 * "Theme posted on" pattern
		 *
		 * @since v1.0
		 */
		function polyspa_article_posted_on() {
			global $theme_dateformat, $theme_timeformat;

			printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author-meta vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'polyspa' ),
				esc_url( get_the_permalink() ),
				esc_attr( get_the_date( $theme_dateformat ) . ' - ' . get_the_time( $theme_timeformat ) ),
				esc_attr( get_the_date( 'c' ) ),
				esc_html( get_the_date( $theme_dateformat ) . ' - ' . get_the_time( $theme_timeformat ) ),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				esc_attr( sprintf( __( 'View all posts by %s', 'polyspa' ), get_the_author() ) ),
				get_the_author()
			);

		}
	endif;


	/**
	 * Template for Password protected post form
	 *
	 * @since v1.0
	 */
	function polyspa_password_form() {
		global $post;
		$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
		
		$output = '<div class="row">';
			$output .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
			$output .= '<h4 class="col-lg-12 alert alert-warning">' . __( 'This content is password protected. To view it please enter your password below.', 'polyspa' ) . '</h4>';
				$output .= '<div class="col-lg-6 col-md-6">';
					$output .= '<div class="input-group">';
						$output .= '<input name="post_password" id="' . $label . '" type="password" placeholder="' . __( 'Password', 'polyspa' ) . '" class="form-control" />';
						$output .= '<span class="input-group-btn"><input type="submit" name="submit" class="btn btn-default" value="' . esc_attr( __( 'Submit', 'polyspa' ) ) . '" /></span>';
					$output .= '</div><!-- /.input-group -->';
				$output .= '</div><!-- /.col -->';
			$output .= '</form>';
		$output .= '</div><!-- /.row -->';
		return $output;
	}
	add_filter( 'the_password_form', 'polyspa_password_form' );


	if ( ! function_exists( 'polyspa_comment' ) ) :
		/**
		 * Style Reply link
		 *
		 * @since v1.0
		 */
		function polyspa_replace_reply_link_class( $class ) {
			$class = str_replace( "class='comment-reply-link", "class='btn btn-default", $class );
			return $class;
		}
		add_filter( 'comment_reply_link', 'polyspa_replace_reply_link_class' );

		/**
		 * Template for comments and pingbacks:
		 * add function to comments.php ... wp_list_comments( array( 'callback' => 'polyspa_comment' ) );
		 *
		 * @since v1.0
		 */
		function polyspa_comment( $comment, $args, $depth ) {
			global $theme_dateformat, $theme_timeformat;

			$GLOBALS['comment'] = $comment;
			switch ( $comment->comment_type ) :
				case 'pingback' :
				case 'trackback' :
			?>
			<li class="post pingback">
				<p><?php _e( 'Pingback:', 'polyspa' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'polyspa' ), '<span class="edit-link">', '</span>' ); ?></p>
			<?php
					break;
				default :
			?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<footer class="comment-meta">
						<div class="comment-author vcard">
							<?php
								$avatar_size = 136;
								if ( '0' != $comment->comment_parent ) {
									$avatar_size = 68;
								}
								echo get_avatar( $comment, $avatar_size );

								/* translators: 1: comment author, 2: date and time */
								printf( __( '%1$s, %2$s', 'polyspa' ),
									sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
									sprintf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
										esc_url( get_comment_link( $comment->comment_ID ) ),
										get_comment_time( 'c' ),
										/* translators: 1: date, 2: time */
										//sprintf( __( '%1$s - %2$s', 'polyspa' ), get_comment_time( $theme_dateformat ), get_comment_time( $theme_timeformat ) )
										sprintf( __( '%1$s ago', 'polyspa' ), human_time_diff( get_comment_time('U'), current_time('timestamp') ) )
									)
								);
							?>

							<?php edit_comment_link( __( 'Edit', 'polyspa' ), '<span class="edit-link">', '</span>' ); ?>
						</div><!-- .comment-author .vcard -->

						<?php if ( '0' === $comment->comment_approved ) : ?>
							<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'polyspa' ); ?></em>
							<br />
						<?php endif; ?>

					</footer>

					<div class="comment-content"><?php comment_text(); ?></div>

					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'polyspa' ) . ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div><!-- .reply -->
				</article><!-- #comment-## -->

			<?php
					break;
			endswitch;

		}

		/**
		 * Custom Comment form
		 *
		 * @since v1.0
		 *
		 * @since v1.1: 'submit_button' and 'submit_field'
		 */
		function polyspa_custom_commentform( $args = array(), $post_id = null ) {
			if ( null === $post_id ) {
				$post_id = get_the_ID();
			}

			$commenter = wp_get_current_commenter();
			$user = wp_get_current_user();
			$user_identity = $user->exists() ? $user->display_name : '';

			$args = wp_parse_args( $args );

			$req = get_option( 'require_name_email' );
			$aria_req = ( $req ? " aria-required='true'" : '' );
			$fields = array (
				'author' => '<p><label for="author">' . __( 'Name', 'polyspa' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label>' . 
							'<br /><input is="paper-input" id="author" name="author" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '"' . $aria_req . ' /></p>',
				'email'  => '<p><label for="email">' . __( 'Email', 'polyspa' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label>' . 
							'<br /><input is="paper-input" id="email" name="email" class="form-control" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '"' . $aria_req . ' /></p>',
				'url'    => '',
			);

			$fields = apply_filters( 'comment_form_default_fields', $fields );
			$defaults = array(
				'fields'               => $fields,
				'comment_field'        => '<fieldset><textarea is="paper-textarea" id="comment" name="comment" class="form-control" aria-required="true" required placeholder="' . __( 'Comment', 'polyspa' ) . ( $req ? '*' : '' ) . '"></textarea></fieldset>',
				/** This filter is documented in wp-includes/link-template.php */
				'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'polyspa' ), wp_login_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
				/** This filter is documented in wp-includes/link-template.php */
				'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'polyspa' ), get_edit_user_link(), $user->display_name, wp_logout_url( apply_filters( 'the_permalink', get_the_permalink( get_the_ID() ) ) ) ) . '</p>',
				'comment_notes_before' => '',
				'comment_notes_after'  => '<p class="small comment-notes">' . __( 'Your Email address will not be published.', 'polyspa' ) . '</p>',
				'id_form'              => 'commentform',
				'id_submit'            => 'submit',
				'class_submit'         => 'btn btn-default',
				'name_submit'          => 'submit',
				'title_reply'          => '',
				'title_reply_to'       => __( 'Leave a Reply to %s', 'polyspa' ),
				'cancel_reply_link'    => __( 'Cancel reply', 'polyspa' ),
				'label_submit'         => __( 'Post Comment', 'polyspa' ),
				'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
				'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
				'format'               => 'html5',
			);

			return $defaults;

		}
		add_filter( 'comment_form_defaults', 'polyspa_custom_commentform' );

	endif;


	/**
	 * Nav menus
	 *
	 * @since v1.0
	 */
	if ( function_exists( 'register_nav_menus' ) ) {
		register_nav_menus( array(
			'main-menu' => 'Main Navigation Menu',
		) );
	}


	/**
	 * Loading All CSS Stylesheets
	 *
	 * @since v1.0
	 */
	function polyspa_scripts_loader() {
		global $theme_version;
		
		// 1. Styles
		wp_enqueue_style( 'style', get_template_directory_uri() . '/style.css', false, $theme_version, 'all' );
		//wp_enqueue_style( 'main', get_template_directory_uri() . '/css/main.css', false, $theme_version, 'all' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl', get_template_directory_uri() . '/css/rtl.css', false, $theme_version, 'all' );
		}
		
		// 2. Scripts
		wp_enqueue_script( 'jquery' );// enqueue JQuery
		
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
	add_action( 'wp_enqueue_scripts', 'polyspa_scripts_loader' );

/************* ENQUEUE JS *************************/
/* pull jquery from Google's CDN. If it's not available, grab the local copy. 3.1.1 2.2.4 1.12.4 */ 

$url = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'; // the URL to check against  
//$test_url = @fopen($url,'r'); // test parameters
$test_url = wp_remote_fopen($url); // test parameters / CDN accessibility
if( $test_url !== false ) { // test if the URL exists  

    function load_external_jQuery() { // load external file  
        wp_deregister_script( 'jquery' ); // deregisters the default WordPress jQuery  
        wp_register_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'); // register the external file  
        wp_enqueue_script('jquery'); // enqueue the external file  
    }
    add_action('wp_enqueue_scripts', 'load_external_jQuery'); // initiate the function  
} else {
    function load_local_jQuery() {
        wp_deregister_script('jquery'); // initiate the function  
        wp_register_script('jquery', THEME_DIR_URI.'/js/jquery.min.js', __FILE__, false, '1.12.4', true); // register the local file  
        wp_enqueue_script('jquery'); // enqueue the local file  
    }
    add_action('wp_enqueue_scripts', 'load_local_jQuery'); // initiate the function  
}
?>
