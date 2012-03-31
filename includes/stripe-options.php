<?php

function wp_stripe_options_init() {

        register_setting( 'wp_stripe_options', 'wp_stripe_options' );
        add_settings_section( 'wp_stripe_main', '', 'wp_stripe_options_header', 'wp_stripe_section' );
        add_settings_field( 'stripe_header', 'Payment Form Header', 'wp_stripe_field_header', 'wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_css_switch', 'Enable Payment Form CSS?', 'wp_stripe_field_css', 'wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_api_switch', 'Enable Test API Environment?', 'wp_stripe_field_switch', 'wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_test_api', 'API Secret Key (Test Environment)', 'wp_stripe_field_test', 'wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_test_api_publish', 'API Publishable Key (Test Environment)', 'wp_stripe_field_test_publish', 'wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_prod_api', 'API Secret Key (Production Environment)', 'wp_stripe_field_prod','wp_stripe_section', 'wp_stripe_main' );
        add_settings_field( 'stripe_prod_api_publish', 'API Publishable Key (Production Environment)', 'wp_stripe_field_prod_publish', 'wp_stripe_section', 'wp_stripe_main' );

}

function wp_stripe_options_header () {

    ?>

    <?php
}


function wp_stripe_field_header () {

        $options = get_option( 'wp_stripe_options' );
        $value = $options['stripe_header'];
	    echo "<input id='setting_api' name='wp_stripe_options[stripe_header]' type='text' size='40' value='$value' />";

}

function wp_stripe_field_css () {

    $options = get_option( 'wp_stripe_options' );
    $items = array( 'Yes', 'No' );
    echo "<select id='stripe_api_switch' name='wp_stripe_options[stripe_css_switch]'>";

    foreach( $items as $item ) {
        $selected = ($options['stripe_css_switch']==$item) ? 'selected="selected"' : '';
        echo "<option value='$item' $selected>$item</option>";
    }

    echo "</select>";
}

function wp_stripe_field_switch () {

        $options = get_option( 'wp_stripe_options' );
        $items = array( 'Yes', 'No' );
        echo "<select id='stripe_api_switch' name='wp_stripe_options[stripe_api_switch]'>";

            foreach( $items as $item ) {
                    $selected = ($options['stripe_api_switch']==$item) ? 'selected="selected"' : '';
                    echo "<option value='$item' $selected>$item</option>";
            }

        echo "</select>";
}

function wp_stripe_field_test () {

        $options = get_option( 'wp_stripe_options' );
        $value = $options['stripe_test_api'];
        echo "<input id='setting_api' name='wp_stripe_options[stripe_test_api]' type='text' size='40' value='$value' />";

}

function wp_stripe_field_test_publish () {

        $options = get_option( 'wp_stripe_options' );
        $value = $options['stripe_test_api_publish'];
        echo "<input id='setting_api' name='wp_stripe_options[stripe_test_api_publish]' type='text' size='40' value='$value' />";

}

function wp_stripe_field_prod () {

        $options = get_option( 'wp_stripe_options' );
        $value = $options['stripe_prod_api'];
        echo "<input id='setting_api' name='wp_stripe_options[stripe_prod_api]' type='text' size='40' value='$value' />";

}

function wp_stripe_field_prod_publish () {

        $options = get_option( 'wp_stripe_options' );
        $value = $options['stripe_prod_api_publish'];
        echo "<input id='setting_api' name='wp_stripe_options[stripe_prod_api_publish]' type='text' size='40' value='$value' />";

}

// Display Options Page

function wp_stripe_add_page() {

        add_options_page( 'WP Stripe', 'WP Stripe', 'manage_options', 'wp_stripe', 'wp_stripe_options_page' );

    }

function wp_stripe_options_page() {
    ?>

    <script type="text/javascript">
        jQuery(function() {
            jQuery("#wp-stripe-tabs").tabs();
        });
    </script>

    <div id="wp-stripe-tabs">

        <h1 class="stripe-title">WP Stripe</h1>

        <ul id="wp-stripe-tabs-nav">
            <li><a href="#wp-stripe-tab-transactions">Transactions</a></li>
            <li><a href="#wp-stripe-tab-settings">Settings</a></li>
            <li><a href="#wp-stripe-tab-about">About</a></li>
        </ul>

        <div style="clear:both"></div>

        <div id="wp-stripe-tab-settings">

            
            
            <form action="options.php" method="post">
                <?php settings_fields( 'wp_stripe_options' ); ?>
                <?php do_settings_sections( 'wp_stripe_section' ); ?>
                <br />
                <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
            </form>

            <p style="margin-top:20px;color:#777">I highly suggest you test payments using the <strong>Test Environment</strong> first. You can use the following details:</p>
            <ul style="color:#777">
                <li><strong>Card Number</strong> 4242424242424242</li>
				<li><strong>Card CVC</strong> 123</li>
                <li><strong>Card Month</strong> 05</li>
                <li><strong>Card Year</strong> 2015</li>
            </ul>
            <p style="color:#777"><strong>Note:</strong> CVC is optional when payments are made.</p>
        </div>

        <div id="wp-stripe-tab-transactions">

            <table class="wp-stripe-transactions">
              <thead><tr class="wp-stripe-absolute"></tr><tr>

                  <th style="width:44px;"><div class="dot-stripe-live"></div><div class="dot-stripe-public"></div></th>
                  <th style="width:200px;">Person</th>
                  <th style="width:100px;">Net Amount (Fee)</th>
                  <th style="width:80px;">Date</th>

                  <th>Comment</th>

              </tr></thead>

        <?php

        // Query Custom Post Types
        $args = array(
            'post_type' => 'wp-stripe-trx',
            'post_status' => 'publish',
            'orderby' => 'meta_value_num',
            'meta_key' => 'wp-stripe-date',
            'order' => 'DESC',
            'posts_per_page' => 50
        );

        // - query -
        $my_query = null;
        $my_query = new WP_query( $args );

        while ( $my_query->have_posts() ) : $my_query->the_post();

            $time_format = get_option( 'time_format' );

            // - variables -

            $custom = get_post_custom( get_the_ID() );
            $id = ( $my_query->post->ID );
            $public = $custom["wp-stripe-public"][0];
            $live = $custom["wp-stripe-live"][0];
            $name = $custom["wp-stripe-name"][0];
            $email = $custom["wp-stripe-email"][0];
            $content = get_the_content();
            $date = $custom["wp-stripe-date"][0];
            $cleandate = date('d M', $date);
            $cleantime = date('H:i', $date);
            $amount = $custom["wp-stripe-amount"][0];
            $fee = ($custom["wp-stripe-fee"][0])/100;
            $net = round($amount - $fee,2);

            echo '<tr>';

            // Dot

            if ( $live == 'LIVE' ) {
                $dotlive = '<div class="dot-stripe-live"></div>';
            } else {
                $dotlive = '<div class="dot-stripe-test"></div>';
            }

            if ( $public == 'YES' ) {
                $dotpublic = '<div class="dot-stripe-public"></div>';
            } else {
                $dotpublic = '<div class="dot-stripe-test"></div>';
            }

            // Person

            $img = get_avatar( $email, 32 );
            $person = $img . ' <span class="stripe-name">' . $name . '</span>';

            // Received

            $received = '<span class="stripe-netamount"> + ' . $net . '</span> (-' . $fee . ')';

            // Content

            echo '<td>' . $dotlive . $dotpublic . '</td>';
            echo '<td>' . $person . '</td>';
            echo '<td>' . $received . '</td>';
            echo '<td>' . $cleandate . ' - ' . $cleantime . '</td>';
            echo '<td class="stripe-comment">"' . $content . '"</td>';

            echo '</tr>';


            endwhile;

                ?>

            </table>

            <p style="color:#777">The amount of payments display is limited to 50. Log in to your Stripe account to see more.</p>
            <div style="color:#777"><div class="dot-stripe-live"></div>Live Environment (as opposed to Test API)</div>
            <div style="color:#777"><div class="dot-stripe-public"></div>Will show in Widget (as opposed to only being visible to you)</div>

        </div>


            <div id="wp-stripe-tab-about">

                <p>For any feedback and suggestions, please check out <a href="http://wordpress.org/tags/wp-stripe?forum_id=10" target="_blank">the official forums</a>. Support or questions will only be answered there.</p>

            </div>

    </div>

<?php
}

?>
