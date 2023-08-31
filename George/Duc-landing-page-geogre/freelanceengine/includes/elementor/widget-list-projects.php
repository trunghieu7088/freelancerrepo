<?php
/**
 * Adds Foo_Widget widget.
 */
class Fre_List_Project_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Fre_List_Project_Widget', // Base ID
			esc_html__( 'Fre  List Projects', ET_DOMAIN ), // Name
			array( 'description' => esc_html__( 'List Projects In Homepage', ET_DOMAIN ), ) // Args
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
		if ( ! empty( $instance['title'] ) ) {
			$widget_title = $instance['title'];
		} else{
			$widget_title = __('Browse numerous freelance jobs online',ET_DOMAIN);
		}
 		?>
 		<!-- List Projects -->
		<div class="fre-jobs-online">
			<div class="container">
				<h2 id="title_project"><?php echo $widget_title;?></h2>
				<?php get_template_part( 'home-list', 'projects' );?>
			</div>
		</div>
		<!-- List Projects -->
		<?php

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Browse numerous freelance jobs online', ET_DOMAIN );
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

}