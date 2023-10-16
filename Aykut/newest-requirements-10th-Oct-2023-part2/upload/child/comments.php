<?php
/**
 * The template for displaying Comments
 *
 * The area of the page that contains comments and the comment form.
 *
 * @package MicrojobEngine
 * @since MicrojobEngine 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<!--<h2 class="comments-title">
			<?php
/*				printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'enginethemes' ),
					number_format_i18n( get_comments_number() ), get_the_title() );
			*/?>
		</h2>-->

		<?php //twentyfifteen_comment_nav(); ?>

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'style'       => 'ol',
					'short_ping'  => true,
					'callback'    => 'blog_comment_callback'
				) );
			?>
		</ol><!-- .comment-list -->

		<?php //twentyfifteen_comment_nav(); ?>

	<?php endif; // have_comments() ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'enginethemes' ); ?></p>
	<?php endif; ?>

	<?php comment_form( array(
							'comment_field'        => ' <div class="form-item"><label for="comment">' . __( 'Your Comment translated yy', 'enginethemes' ) . '</label><div class="input"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></div></div>',
							'comment_notes_before' => '',
							'comment_notes_after'  => '',
							'id_form'              => 'commentform',
							'id_submit'            => 'submit',
							'title_reply'          =>  "Comment translated xx",
							'title_reply_to'       =>  'Leave a Reply hh',
							'cancel_reply_link'    => 'Cancel reply',
							'label_submit'         => 'Comment button kk',
							'must_log_in'          => '<p class="must-log-in">'. __( 'You must be <a href="#" class="login login-btn">logged in</a> to post a comment.', 'enginethemes' ) . '</p>',
							//'logged_in_as' => 'you can edit as you want',
					)); ?>

</div><!-- .comments-area -->
<?php
function blog_comment_callback( $comment, $args, $depth ){
	$GLOBALS['comment'] = $comment;
	?>
<li class="media et-comment" id="li-comment-<?php comment_ID();?>">
	<div id="comment-<?php comment_ID(); ?>" class="clearfix">
		<div class="pull-left">
			<a class="avatar-comment" href="#">
				<?php echo get_avatar( $comment->comment_author_email, 40 );?>
			</a>
		</div>
		<div class="media-body pull-right">
			<h4 class="media-heading">
				<?php
				comment_author();
				?>
			</h4>
				<span class="time-review">
                	<i class="fa fa-clock-o"></i>
                	<time>
						<?php echo ae_the_time( strtotime($comment->comment_date)); ?>
					</time>
                </span>
			<div class="comment-text">
				<?php comment_text(); ?>
			</div>
			<?php
			comment_reply_link(array_merge($args, array(
				'reply_text' => __( 'Reply ss', 'enginethemes' ).'<i class="fa fa-edit"></i>',
				'depth'      => $depth,
				'max_depth'  => $args['max_depth']
			)));
			?>
		</div>
	</div>
	<?php
}