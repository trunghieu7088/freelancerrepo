<?php
/**
 * Adds Fre_Profiles_Widget .
 */
class Fre_Profiles_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Fre_Profiles_Widget', // Base ID
			esc_html__( 'Fre  List Profiles', ET_DOMAIN ), // Name
			array( 'description' =>  'List Profiles In Homepage', ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $widget_title;
		//custom code here
		global $post;		
		$category_skill=get_post_meta($post->ID,'categoryID',true);
		if(get_post_type()=='page' && $category_skill)
		{
			$skill=get_term($category_skill);
			$custom_link=site_url('/profiles/?catskill='.$skill->slug);
		}
		else
		{
			$custom_link=get_post_type_archive_link( PROFILE );
		}

		//end

		if ( ! empty( $instance['title'] ) ) {
			$widget_title = $instance['title'];
		} else{
			$widget_title = __('Find perfect freelancers for your projects',ET_DOMAIN);
		}
 		?>
 		<div class="fre-perfect-freelancer">
			<div class="container">
				<h2 id="title_freelance"><?php echo $widget_title;?></h2>
				<?php get_template_part( 'home-list', 'profiles' );?>
				<div class="fre-perfect-freelancer-more">
					<a class="fre-btn-o primary-color" href="<?php echo $custom_link; ?>"><?php _e('See all freelancers', ET_DOMAIN);?></a>
				</div>
			</div>
		</div> <?php
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Find perfect freelancers for your projects', ET_DOMAIN );
		?>
		 <p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Foo_Widget