<?php

// Create Shortcode [wp-stripe]

function wp_stripe_shortcode( $atts ){
    wp_stripe_form();
}
add_shortcode( 'wp-stripe', 'wp_stripe_shortcode' );

// Create Charge

function wp_stripe_charge($amount, $card, $description) {

    /*
     * Currency - All amounts must be denominated in USD when creating charges with Stripe — the currency conversion happens automatically
     */

    $currency = 'usd';

    /*
     * Card - Token from stripe.js is provided (not individual card elements)
     */

    $charge = Stripe_Charge::create(array(
        'card' => $card,
        'amount' => $amount,
        'currency' => $currency
    ));

    if ( $description ) {
        $charge['description'] = $description;
    }

    return $charge;

}

// 1) Capture POST, 2) Create Charge, 3) Store Transaction (Custom Post Type)

function wp_stripe_charge_initiate() {

    if ( isset($_POST['wp_stripe_form'] ) == '1') {

        $public = $_POST['wp_stripe_public'];
        $name = $_POST['wp_stripe_name'];
        $email = $_POST['wp_stripe_email'];
        $comment = $_POST['wp_stripe_comment'];
        $amount = $_POST['wp_stripe_amount'];
        if ( !$comment ) { $comment = __('No comment provided', 'wp-stripe'); }
        if ( !$name ) { $name = __('Anonymous', 'wp-stripe'); }

        $amount = $amount * 100;
        $card = $_POST['stripeToken'];
        $description = $name . ' - ' . email . ' - ' . $comment;

        if ( !$description ) {
            $description = __('This transaction has no additional details', 'wp-stripe');
        }

        // Create Charge

        try {

            $response = wp_stripe_charge($amount, $card, $description);

            $id = $response->id;
            $amount = ($response->amount)/100;
            $currency = $response->currency;
            $created = $response->created;
            $live = $response->livemode;
            $paid = $response->paid;
            $fee = $response->fee;

            echo '<div class="wp-stripe-notification wp-stripe-success"> ' . __('Success, you just transferred ', 'wp-stripe') . '<span class="wp-stripe-currency">' . $currency . '</span> ' . $amount . ' !</div>';

            // Save Charge

            $new_post = array(
                'ID' => '',
                'post_type' => 'wp-stripe-trx',
                'post_author' => 1,
                'post_content' => $comment,
                'post_title' => $id,
                'post_status' => 'publish',
            );

            $post_id = wp_insert_post( $new_post );

            // Define Livemode

            if ( $live ) {
                $live = 'LIVE';
            } else {
                $live = 'TEST';
            }

            // Define Payment

            if ( $paid == 1 ) {
                $live = 'PAID';
            } else {
                $live = 'NOT PAID';
            }

            // Define Public

            if ( $public == 'public' ) {
                $public = 'YES';
            } else {
                $public = 'NO';
            }

            // Update Meta

            update_post_meta( $post_id, 'wp-stripe-public', $public);
            update_post_meta( $post_id, 'wp-stripe-name', $name);
            update_post_meta( $post_id, 'wp-stripe-email', $email);

            update_post_meta( $post_id, 'wp-stripe-live', $live);
            update_post_meta( $post_id, 'wp-stripe-date', $created);
            update_post_meta( $post_id, 'wp-stripe-amount', $amount);
            update_post_meta( $post_id, 'wp-stripe-currency', strtoupper($currency));
            update_post_meta( $post_id, 'wp-stripe-fee', $fee);
            update_post_meta( $post_id, 'wp-stripe-paid', $paid);

        // Error

        } catch (Exception $e) {
            echo '<div class="wp-stripe-notification wp-stripe-failure">' . __('Oops, something went wrong', 'wp-stripe' ) . ' (' . $e->getMessage() . ')</div>';
        }

        }
}

?>