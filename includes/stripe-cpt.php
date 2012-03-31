<?php

function create_wp_stripe_cpt() {
    
    $labels = array(
        'name' => _x('Payments', ''),
        'singular_name' => _x('Payment', 'post type singular name'),
        'add_new' => _x('Add New', 'Payments'),
        'add_new_item' => __('Add New Payment'),
        'edit_item' => __('Edit Payment'),
        'new_item' => __('New Payment'),
        'view_item' => __('View Payment'),
        'search_items' => __('Search Payments'),
        'not_found' =>  __('No Payments found'),
        'not_found_in_trash' => __('No Payments found in Trash'),
        'parent_item_colon' => '',
    );

    $args = array(
        'labels' 		=> $labels,
        'public' 		=> false,
        'can_export' 	=> true,
        'capability_type' => 'post',
        'hierarchical' 	=> false,
        'supports'		=> array( 'title', 'editor' )
    );

    register_post_type( 'wp-stripe-trx', $args);
    
}

add_action( 'init', 'create_wp_stripe_cpt' );

?>
