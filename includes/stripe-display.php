<?php


function wp_stripe_js() {

    // Get API Key

    $options = get_option('wp_stripe_options');

    if ( $options['stripe_api_switch'] ) {
        if ( $options['stripe_api_switch'] == 'Yes') {
            $apikey = $options['stripe_test_api_publish'];
        } else {
            $apikey = $options['stripe_prod_api_publish'];
        }
    }
    // Generate Token

    ?>

    <script type="text/javascript">

    Stripe.setPublishableKey('<?php echo $apikey; ?>');

    function stripeResponseHandler(status, response) {
        if (response.error) {
            console.log(status);
            console.log(response);
            // re-enable the submit button
            jQuery('.stripe-submit-button').removeAttr("disabled");
            // show the errors on the form
            jQuery(".payment-errors").show().html(response.error.message);
        } else {
            var form$ = jQuery("#wp-stripe-payment-form");
            // token contains id, last4, and card type
            var token = response['id'];
            // insert the token into the form so it gets submitted to the server
            form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

            // and submit
            form$.get(0).submit();
        }
    }

    jQuery(document).ready(function() {
        jQuery("#wp-stripe-payment-form").submit(function(event) {

            jQuery(".payment-errors").hide();

            // disable the submit button to prevent repeated clicks

            jQuery('.stripe-submit-button').attr("disabled", "disabled");

            var amount = jQuery('.wp-stripe-card-amount').val() * 100; //amount you want to charge in cents
            Stripe.createToken({
                number: jQuery('.card-number').val(),
                cvc: jQuery('.card-cvc').val(),
                exp_month: jQuery('.card-expiry-month').val(),
                exp_year: jQuery('.card-expiry-year').val()
            }, amount, stripeResponseHandler);

            // prevent the form from submitting with the default action

            return false;
        });
    });
    </script>

    <?php

}

function wp_stripe_form() {

    echo '<!-- Start WP-Stripe --><div id="wp-stripe-wrap">';

    // Insert Stripe JS

    wp_stripe_js();

    // Display POST data again for non-sensitive data

    if ( isset($_POST['wp_stripe_form'] ) == '1') {

        $stripe_post_name = $_POST['wp_stripe_name'];
        $stripe_post_email = $_POST['wp_stripe_email'];
        $stripe_post_comment = $_POST['wp_stripe_comment'];

    }

    ?>

    <form action="" method="POST" id="wp-stripe-payment-form">
    <h2 class="stripe-header"><?php $options = get_option('wp_stripe_options'); echo $options['stripe_header']; ?></h2>
    <div class="wp-stripe-details">
            <div class="wp-stripe-notification wp-stripe-failure payment-errors" style="display:none"></div>
            <?php wp_stripe_charge_initiate(); ?>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('Name', 'wp-stripe'); ?></label>
            </div>
            <div class="stripe-row-right">
                <input type="text" name="wp_stripe_name" class="" value="<?php echo $stripe_post_name; ?>" />
            </div>
        </div>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('E-mail', 'wp-stripe'); ?></label>
            </div>
            <div class="stripe-row-right">
                <input type="text" name="wp_stripe_email" value="<?php echo $stripe_post_email; ?>" />
            </div>
        </div>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('Comment', 'wp-stripe'); ?></label>
            </div>
            <div class="stripe-row-right">
                <textarea name="wp_stripe_comment"><?php echo $stripe_post_comment; ?></textarea>
            </div>
        </div>
    </div>
    <div class="wp-stripe-card">
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('Amount (USD)', 'wp-stripe'); ?> *</label>
            </div>
            <div class="stripe-row-right">
                <input type="text" name="wp_stripe_amount" autocomplete="off" class="wp-stripe-card-amount" />
            </div>
        </div>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('Card Number', 'wp-stripe'); ?> *</label>
            </div>
            <div class="stripe-row-right">
                <input type="text" autocomplete="off" class="card-number" />
            </div>
        </div>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('CVC Number', 'wp-stripe'); ?> *</label>
            </div>
            <div class="stripe-row-right">
                <input type="text" autocomplete="off" class="card-cvc" />
            </div>
        </div>
        <div class="stripe-row">
            <div class="stripe-row-left">
                <label><?php _e('Expiration', 'wp-stripe'); ?> *</label>
            </div>
            <div class="stripe-row-right">
            <select class="card-expiry-month">
                <option value="1">01</option>
                <option value="2">02</option>
                <option value="3">03</option>
                <option value="4">04</option>
                <option value="5">05</option>
                <option value="6">06</option>
                <option value="7">07</option>
                <option value="8">08</option>
                <option value="9">09</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
            </select>
            <span> / </span>
            <select class="card-expiry-year">
            <?php
                $year = date(Y,time());
                $num = 1;

                while ( $num <= 7 ) {
                    echo '<option value="' . $year .'">' . $year . '</option>';
                    $year++;
                    $num++;
                }
            ?>
            </select>
            </div>

        </div>



        </div>

        <div class="stripe-row">

            <input type="checkbox" name="wp_stripe_public" value="public" checked="checked" /> <label><?php _e('Display on Website?', 'wp-stripe'); ?></label>

            <p class="stripe-display-comment"><?php _e('If you check this box, the name as you enter it (including the avatar from your e-mail) and comment will be shown in recent donations. Your e-mail address and donation amount will not be shown.', 'wp-stripe'); ?></p>

        </div>

        <input type="hidden" name="wp_stripe_form" value="1"/>

        <button type="submit" class="stripe-submit-button">Submit Payment</button>

        <div class="wp-stripe-poweredby">Payments powered by <a href="http://wordpress.org/extend/plugins/wp-stripe" target="_blank">WP-Stripe</a>. No card information is stored on this server.</div>

    </form>

    </div><!-- End WP-Stripe -->

    <?php

}

?>
