<?php
function wpb_load_widget() {
    register_widget( 'Fre_Banner' );
    register_widget( 'Fre_Pricing_Widget' );
    register_widget( 'Fre_Profiles_Widget' );
    register_widget( 'Fre_List_Project_Widget' );

}
add_action( 'widgets_init', 'wpb_load_widget' );