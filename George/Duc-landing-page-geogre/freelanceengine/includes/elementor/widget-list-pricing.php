<?php
/**
 * Adds Fre_Pricing_Widget widget.
 */
class Fre_Pricing_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Fre_Pricing_Widget', // Base ID
			esc_html__( 'Fre List Pricing', ET_DOMAIN ), // Name
			array( 'description' =>'List Pricing  In Homepage' ) // Args
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


		$fre_title = ! empty( $instance['fre_title'] ) ? $instance['fre_title'] : esc_html__( 'Select the level of service you need for project bidding', ET_DOMAIN );
		$emp_title = ! empty( $instance['emp_title'] ) ? $instance['emp_title'] : esc_html__( 'Select the level of service you need for project posting', ET_DOMAIN );


		global $is_post_free, $pay_to_bid, $show_project_pack, $show_bid_pack, $user_ID;
		$is_post_free 	= (int) ae_get_option( 'disable_plan', false );
		$pay_to_bid 	= ae_get_option( 'pay_to_bid', false );
		$show_bid_pack 	= $show_project_pack = false;
		$user_role 		= ae_user_role($user_ID);


		if(  is_user_logged_in() ) {

			if( ( in_array( $user_role, array( EMPLOYER,'administrator' ) ) || current_user_can('manage_options') )  &&  ! $is_post_free ) {
				$show_project_pack = true;
			} else if(  in_array( $user_role, array( FREELANCER,'subscriber' ) ) && $pay_to_bid && ! current_user_can('manage_options') ) {
				$show_bid_pack = true;
			}

		} else { // visitor.
			if( $pay_to_bid ){
				$show_bid_pack = true;
			} else if( ! $is_post_free ){
				$show_project_pack = true;
			}
		}

		if( $show_project_pack || $show_bid_pack ){ ?>
			<div class="fre-service">
				<div class="container">
					<h2 id="title_service">
						<?php

						if( $show_project_pack ){
							echo $emp_title;

						} else if($show_bid_pack) {
							echo $fre_title;
						}
						?>
					</h2>
					<?php
					if( ! is_acti_fre_membership() ){
						get_template_part( 'home-list', 'pack' );
					} else {
						echo do_shortcode('[fre_membership_plans]');
					}?>
				</div>
			</div>
		<?php } ?>
<!-- List Pricing Plan -->
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
		$fre_title = ! empty( $instance['fre_title'] ) ? $instance['fre_title'] : esc_html__( 'Select the level of service you need for project bidding', ET_DOMAIN );
		$emp_title = ! empty( $instance['emp_title'] ) ? $instance['emp_title'] : esc_html__( 'Select the level of service you need for project posting', ET_DOMAIN );
		?>
		 <p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'fre_title' ) ); ?>"><?php esc_attr_e( 'Freelancer heading:', ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'fre_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fre_title' ) ); ?>" type="text" value="<?php echo esc_attr( $fre_title ); ?>">
		</p>
		  <p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'emp_title' ) ); ?>"><?php esc_attr_e( 'Employer heading:', ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'emp_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'emp_title' ) ); ?>" type="text" value="<?php echo esc_attr( $emp_title ); ?>">
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
		$instance['fre_title'] = ( ! empty( $new_instance['fre_title'] ) ) ? strip_tags( $new_instance['fre_title'] ) : '';
		$instance['emp_title'] = ( ! empty( $new_instance['emp_title'] ) ) ? strip_tags( $new_instance['emp_title'] ) : '';

		return $instance;
	}

} // class Foo_Widget


