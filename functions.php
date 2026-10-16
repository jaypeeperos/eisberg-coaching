<?php
function eisberg_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 46, 'width' => 180, 'flex-height' => true, 'flex-width' => true));
    register_nav_menus(array('primary' => __('Hauptnavigation', 'eisberg')));
}
add_action('after_setup_theme', 'eisberg_setup');

function eisberg_assets() {
    wp_enqueue_style('eisberg-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null);
    wp_enqueue_style('eisberg-style', get_stylesheet_uri(), array('eisberg-fonts'), '1.0.4');
    wp_enqueue_script('eisberg-navigation', get_theme_file_uri('js/navigation.js'), array(), '1.0.0', true);
    wp_enqueue_script('eisberg-region-switcher', get_theme_file_uri('js/region-switcher.js'), array(), '1.0.0', true);
    wp_enqueue_script('eisberg-custom', get_theme_file_uri('js/custom.js'), array('jquery'), '1.0.15', true);
}
add_action('wp_enqueue_scripts', 'eisberg_assets');

function eisberg_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : home_url('/' . trim($slug, '/') . '/');
}

/**
 * Regions offered in the header switcher. Each one pairs a language with a currency.
 *
 * 'url' is empty while every region is served from this site. Point it at a translated
 * page tree (e.g. a Polylang/WPML permalink) to give a region its own content, and the
 * switcher will send visitors there instead of reloading the current page.
 */
function eisberg_regions() {
    return apply_filters('eisberg_regions', array(
        'eu' => array(
            'label'    => 'EU',
            'name'     => 'Europe',
            'lang'     => 'de_de',
            'currency' => 'EUR',
            'url'      => '',
        ),'ch' => array(
            'label'    => 'CH',
            'name'     => 'Switzerland',
            'lang'     => 'de',///(is_front_page()? '' : 'de'),//de but since default, make it empty
            'currency' => 'CHF',
            'url'      => '',
        ),
        'uk' => array(
            'label'    => 'UK',
            'name'     => 'United Kingdom',
            'lang'     => 'en_gb',
            'currency' => 'GBP',
            'url'      => '',
        ),
        'us' => array(
            'label'    => 'US',
            'name'     => 'United States',
            'lang'     => 'en',
            'currency' => 'USD',
            'url'      => '',
        ),
    ));
}

function eisberg_current_region_key() {
    static $key = null;
    if ( $key !== null ) {
        return $key;
    }

    $regions = eisberg_regions();
    $key     = 'eu'; // default → Europe (EUR)

    if ( isset( $_COOKIE['eisberg_region'] ) ) {
        $cookie = sanitize_key( wp_unslash( $_COOKIE['eisberg_region'] ) );
        if ( isset( $regions[ $cookie ] ) ) {
            $key = $cookie;
        }
    }

    return $key;
}

function eisberg_current_region() {
    $regions = eisberg_regions();
    return $regions[eisberg_current_region_key()];
}

function eisberg_region_language_attributes($output) {
    $lang = eisberg_current_region()['lang'];
    if (strpos($output, 'lang=') !== false) {
        return preg_replace('/lang="[^"]*"/', 'lang="' . esc_attr($lang) . '"', $output);
    }
    return trim($output . ' lang="' . esc_attr($lang) . '"');
}
add_filter('language_attributes', 'eisberg_region_language_attributes');

/**
 * Strings – CH uses German, UK & US use English
 */
function eisberg_strings() {
    return apply_filters('eisberg_strings', array(
        'nav_label'      => array('de' => 'Hauptnavigation', 'en' => 'Main navigation'),
        'nav_product'    => array('de' => 'Produkt', 'en' => 'Product'),
        'nav_pricing'    => array('de' => 'Preise', 'en' => 'Pricing'),
        'nav_schools'    => array('de' => 'Für Schulleitungen', 'en' => 'For school leaders'),
        'nav_faq'        => array('de' => 'FAQ', 'en' => 'FAQ'),
        'nav_cta'        => array('de' => 'Eisberg entdecken', 'en' => 'Discover Eisberg'),
        'region_label'   => array('de' => 'Region und Währung', 'en' => 'Region and currency'),
        'menu_open'      => array('de' => 'Menü öffnen', 'en' => 'Open menu'),
        'per_year'       => array('de' => 'pro Jahr', 'en' => 'per year'),
        'footer_privacy' => array('de' => 'Datenschutzerklärung', 'en' => 'Privacy policy'),
        'footer_imprint' => array('de' => 'Impressum', 'en' => 'Imprint'),
        'footer_contact' => array('de' => 'Kontakt', 'en' => 'Contact'),
    ));
}

/**
 * Map region key → TranslatePress language code
 * 
 * Default language is German (no /de/ in URL)
 * English uses /en/
 */
function eisberg_region_to_trp_language( $region_key = null ) {
    if ( null === $region_key ) {
        $region_key = eisberg_current_region_key();
    }

    // IMPORTANT: These must match the exact codes shown in
    // Settings → TranslatePress → General → All Languages
    $map = array(
        'eu' => 'en_GB',   // or 'en_US' – check your TP settings
        'ch' => 'de_DE',   // German (default language)
        'uk' => 'en_GB',   // or 'en_US'
        'us' => 'en_US',   // or 'en_GB'
    );

    return isset( $map[ $region_key ] ) ? $map[ $region_key ] : 'de_DE';
}

function eisberg_t( $key ) {
    $strings = eisberg_strings();
    if ( ! isset( $strings[ $key ] ) ) {
        return $key;
    }

    // EU & CH → German, UK & US → English
    $lang = in_array( eisberg_current_region_key(), array( 'uk', 'us' ), true ) ? 'en' : 'de';

    return isset( $strings[ $key ][ $lang ] ) ? $strings[ $key ][ $lang ] : $strings[ $key ]['de'];
}

/**
 * Plan prices – update the real values for GBP & USD when ready
 */
function eisberg_plan_price( $plan ) {
    $prices = apply_filters( 'eisberg_plan_prices', array(
        'eisberg' => array(
            'EUR' => 'EUR 490.',
            'CHF' => 'CHF 490.–',
            'GBP' => '£490',
            'USD' => '$490',
        ),
        'plus' => array(
            'EUR' => 'EUR 490.',
            'CHF' => 'CHF 490.–',
            'GBP' => '£490',
            'USD' => '$490',
        ),
    ) );

    $currency = eisberg_current_region()['currency'];

    if ( isset( $prices[ $plan ][ $currency ] ) ) {
        return $prices[ $plan ][ $currency ];
    }

    return isset( $prices[ $plan ]['EUR'] ) ? $prices[ $plan ]['EUR'] : '';
}


function eisberg_region_flag( $key ) {
    $flags = array(
        // Europe (simple EU-style)
        'eu' => '<svg class="region-flag" viewBox="0 0 30 30" aria-hidden="true" focusable="false">
            <rect width="30" height="30" fill="#003399"/>
            <circle cx="15" cy="15" r="7" fill="none" stroke="#FFCC00" stroke-width="1.5"/>
            <circle cx="15" cy="8" r="1.2" fill="#FFCC00"/>
            <circle cx="19.5" cy="10" r="1.2" fill="#FFCC00"/>
            <circle cx="21" cy="14.5" r="1.2" fill="#FFCC00"/>
            <circle cx="19.5" cy="19" r="1.2" fill="#FFCC00"/>
            <circle cx="15" cy="21" r="1.2" fill="#FFCC00"/>
            <circle cx="10.5" cy="19" r="1.2" fill="#FFCC00"/>
            <circle cx="9" cy="14.5" r="1.2" fill="#FFCC00"/>
            <circle cx="10.5" cy="10" r="1.2" fill="#FFCC00"/>
        </svg>',

        // Switzerland
        'ch' => '<svg class="region-flag" viewBox="0 0 30 30" aria-hidden="true" focusable="false"><rect width="30" height="30" fill="#d52b1e"/><rect x="5" y="12" width="20" height="6" fill="#fff"/><rect x="12" y="5" width="6" height="20" fill="#fff"/></svg>',

        // United Kingdom
        'uk' => '<svg class="region-flag" viewBox="0 0 60 30" aria-hidden="true" focusable="false"><rect width="60" height="30" fill="#012169"/><path d="M0 0 60 30M60 0 0 30" stroke="#fff" stroke-width="6"/><path d="M0 0 60 30M60 0 0 30" stroke="#c8102e" stroke-width="3"/><path d="M30 0V30M0 15H60" stroke="#fff" stroke-width="10"/><path d="M30 0V30M0 15H60" stroke="#c8102e" stroke-width="6"/></svg>',

        // United States
        'us' => '<svg class="region-flag" viewBox="0 0 30 30" aria-hidden="true" focusable="false">
            <rect width="30" height="30" fill="#B22234"/>
            <rect y="2.3" width="30" height="2.3" fill="#fff"/>
            <rect y="6.9" width="30" height="2.3" fill="#fff"/>
            <rect y="11.5" width="30" height="2.3" fill="#fff"/>
            <rect y="16.1" width="30" height="2.3" fill="#fff"/>
            <rect y="20.7" width="30" height="2.3" fill="#fff"/>
            <rect y="25.3" width="30" height="2.3" fill="#fff"/>
            <rect width="12" height="16.1" fill="#3C3B6E"/>
        </svg>',
    );

    return isset( $flags[ $key ] ) ? $flags[ $key ] : '';
}

function eisberg_region_switcher() {
    $regions     = eisberg_regions();
    $current_key = eisberg_current_region_key();
    $current     = $regions[$current_key];
    $root_url    = 'https://www.eisberg-coaching.com';

    // Get current path (without leading/trailing slashes)
    global $wp;
    $current_path = trim( $wp->request ?? '', '/' );

    // Safely strip the current language prefix (only full path segment)
    $current_lang = trim( $current['lang'] ?? '', '/' );
    if ( $current_lang ) {
        if ( $current_path === $current_lang ) {
            $current_path = '';
        } elseif ( str_starts_with( $current_path, $current_lang . '/' ) ) {
            $current_path = substr( $current_path, strlen( $current_lang ) + 1 );
        }
    }
    
    ?>
    <div class="region-switcher" data-region-switcher>
        <button type="button"
                class="region-toggle"
                aria-expanded="false"
                aria-controls="region-menu"
                aria-label="<?php echo esc_attr(eisberg_t('region_label')); ?>">
            <?php echo eisberg_region_flag($current_key); ?>
            <span class="region-code"><?php echo esc_html($current['label']); ?></span>
            <span class="region-currency"><?php echo esc_html($current['currency']); ?></span>
            <span class="region-caret" aria-hidden="true"></span>
        </button>

        <ul class="region-menu" id="region-menu">
            <?php foreach ($regions as $key => $region) : ?>
                <?php
                $lang_path = trim( $region['lang'] ?? '', '/' );
                $url_parts = array_filter( [ $root_url, $lang_path, $current_path ] );
                $base_url  = implode( '/', $url_parts ) . '/';


                $the_url = esc_url( add_query_arg( 'region', $key, $base_url ) );

                if ( $region['lang'] == "de") {

                    //remove /de/
                    //$the_url  = $root_url . '/' . $current_path; // not working, // add redirect for now

                    //there is a script that auto edits the href tag with the 'de' var
                }

                ?>
                <li>
                    <a  data-the-path="<?php echo $the_url;?>" data-current-path="<?php echo $current_path;?>" data-lang="<?php echo $region['lang'];?>" href="<?php echo $the_url; ?>"
                       <?php echo $key === $current_key ? 'aria-current="true"' : ''; ?>>
                        <?php echo eisberg_region_flag($key); ?>
                        <span class="region-code"><?php echo esc_html($region['label']); ?></span>
                        <span class="region-name"><?php echo esc_html($region['name']); ?></span>
                        <span class="region-currency"><?php echo esc_html($region['currency']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * Map region key → currency code used by WC Currency Switcher
 */
function eisberg_region_currency( $region_key = null ) {
    if ( null === $region_key ) {
        $region_key = eisberg_current_region_key();
    }

    $map = array(
        'eu' => 'EUR',
        'ch' => 'CHF',
        'uk' => 'GBP',
        'us' => 'USD',
    );

    return isset( $map[ $region_key ] ) ? $map[ $region_key ] : 'EUR';
}

/**
 * Force WC Currency Switcher to use the currency that belongs to the current region.
 * This is the official filter documented by the plugin.
 */
add_filter( 'wccs_set_currency', function( $currency ) {
    // Only force on the frontend (and AJAX)
    if ( is_admin() && ! wp_doing_ajax() ) {
        return $currency;
    }

    return eisberg_region_currency();
}, 20 );

/**
 * Returns the WooPayments MultiCurrency instance if available and enabled.
 *
 * @return \WCPay\MultiCurrency\MultiCurrency|null
 */
function eisberg_get_wcpay_multi_currency() {
    if ( ! class_exists( '\WCPay\MultiCurrency\MultiCurrency' ) ) {
        return null;
    }

    // Optional extra safety: feature flag (exists in recent versions).
    if ( class_exists( '\WC_Payments_Features' )
        && method_exists( '\WC_Payments_Features', 'is_customer_multi_currency_enabled' )
        && ! \WC_Payments_Features::is_customer_multi_currency_enabled()
    ) {
        return null;
    }

    try {
        return \WCPay\MultiCurrency\MultiCurrency::instance();
    } catch ( Exception $e ) {
        return null;
    }
}

/**
 * Force WooPayments to use the currency that belongs to the current region.
 */
function eisberg_sync_wcpay_currency() {
    $multi = eisberg_get_wcpay_multi_currency();
    if ( ! $multi ) {
        return;
    }

    $desired = eisberg_region_currency();

    // Only update when necessary (avoids unnecessary session writes / cart recalcs).
    $current = $multi->get_selected_currency();
    if ( $current && strtoupper( $current->get_code() ) === strtoupper( $desired ) ) {
        return;
    }

    $multi->update_selected_currency( $desired, true );
}

/**
 * Keep WooPayments currency in sync on every frontend request.
 * Runs after WooPayments has initialised its own currency logic.
 */
//add_action( 'init', 'eisberg_sync_wcpay_currency', 20 );

/**
 * Handle region switch via ?region=xx
 * – saves the region cookie
 * – redirects cleanly
 * (currency is applied on the next request via wccs_set_currency)
 */
function eisberg_handle_region_request() {
    if ( is_admin() || empty( $_GET['region'] ) ) {
        return;
    }

    $regions = eisberg_regions();
    $key     = sanitize_key( wp_unslash( $_GET['region'] ) );

    if ( ! isset( $regions[ $key ] ) ) {
        return;
    }

    // Save region preference
    setcookie(
        'eisberg_region',
        $key,
        time() + YEAR_IN_SECONDS,
        COOKIEPATH ? COOKIEPATH : '/',
        COOKIE_DOMAIN,
        is_ssl(),
        false // not HttpOnly so JS can read it if needed
    );

    // Make the cookie available on the current request as well
    $_COOKIE['eisberg_region'] = $key;

    // Redirect (remove the region query arg)
    $target = $regions[ $key ]['url'] ? $regions[ $key ]['url'] : remove_query_arg( 'region' );
    wp_safe_redirect( $target );
    exit;
}
add_action( 'template_redirect', 'eisberg_handle_region_request' );

// ActiveHosted Form Shortcode
function kidster_activehosted_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '25',        // default form ID
    ), $atts);

    $id = intval($atts['id']);

    return '
        <div class="_form_' . $id . '" data-ac-form="' . $id . '"></div>
        <script src="https://kidster.activehosted.com/f/embed.php?id=' . $id . '" charset="utf-8"></script> 
    ';
}
add_shortcode('activehosted_form', 'kidster_activehosted_form_shortcode');

/**
 * Shortcode: [product_price id="123"]
 * Returns the product price in the current region’s currency
 * Example output: "EUR 490." | "CHF 490.–" | "£490" | "$490"
 */
function eisberg_product_price_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => 0,
    ), $atts, 'product_price' );

    $product_id = absint( $atts['id'] );
    if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
        return '';
    }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        return '';
    }

    // Price is already in the currency forced by eisberg_sync_wcpay_currency()
    $price = $product->get_price();

    if ( $price === '' || $price === null ) {
        return '';
    }

    $currency = eisberg_current_region()['currency'];
    $amount   = number_format( (float) $price, 0, '.', '' );

    // Match the style used in eisberg_plan_price()
    switch ( $currency ) {
        case 'CHF':
            $formatted = 'CHF ' . $amount . '.–';
            break;
        case 'GBP':
            $formatted = '£' . $amount;
            break;
        case 'USD':
            $formatted = '$' . $amount;
            break;
        case 'EUR':
        default:
            $formatted = 'EUR ' . $amount . '.';
            break;
    }

    return esc_html( $formatted );
}
add_shortcode( 'product_price', 'eisberg_product_price_shortcode' );

/**
 * Shortcode: [eisberg_school_leadership_pdf]
 * Just outputs the correct PDF URL as plain text based on the active region.
 *
 * Usage: [eisberg_school_leadership_pdf]
 */
function eisberg_school_leadership_pdf_shortcode() {
    $pdfs = [
        'uk' => 'eisberg-coaching.s3.us-east-2.amazonaws.com/wp-content/uploads/2026/09/Eisberg_School_Leadership_UK.pdf',
        'us' => 'eisberg-coaching.s3.us-east-2.amazonaws.com/wp-content/uploads/2026/09/Eisberg_School_Leadership_US.pdf',
        'ch' => 'eisberg-coaching.s3.us-east-2.amazonaws.com/wp-content/uploads/2026/09/Eisberg_Schulleitung_CH_Aktualisiert.pdf',
        'eu' => 'eisberg-coaching.s3.us-east-2.amazonaws.com/wp-content/uploads/2026/09/Eisberg_Schulleitung_DE_Aktualisiert.pdf',
    ];

    $current_key = eisberg_current_region_key();

    // Fallback to UK if the region is unknown
    return $pdfs[ $current_key ] ?? $pdfs['uk'];
}
add_shortcode( 'eisberg_school_leadership_pdf', 'eisberg_school_leadership_pdf_shortcode' );


add_action( 'wp_enqueue_scripts', function() {
    $handle = 'pa-modal';

    if ( ! wp_script_is( $handle, 'enqueued' ) ) {
        wp_enqueue_script(
            $handle,
            'https://www.eisberg-coaching.com/wp-content/plugins/premium-addons-for-elementor/assets/frontend/min-js/modal.min.js',
            array( 'jquery' ),
            '4.11.104',
            true
        );
    }
}, 20 );

// Register after Elementor has registered its styles
add_action( 'elementor/frontend/after_register_styles', function() {
    $handle = 'e-animation-fadeInDown';
    $src    = plugins_url( 'assets/lib/animations/styles/fadeInDown.min.css', WP_PLUGIN_DIR . '/elementor/elementor.php' );

    if ( ! wp_style_is( $handle, 'registered' ) ) {
        wp_register_style( $handle, $src, array(), '4.2.4' );
    }
});

// Enqueue after Elementor has finished its normal enqueue process
add_action( 'elementor/frontend/after_enqueue_styles', function() {
    $handle = 'e-animation-fadeInDown';

    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_enqueue_style( $handle );
    }
});

/**
 * Shortcode: [Eisberg_Price CHF="988" GBP="20" USD="55" EUR="300"]
 * Formats a static price string according to the active region's currency.
 */
function eisberg_static_price_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'eur' => '',
        'chf' => '',
        'gbp' => '',
        'usd' => '',
    ), $atts, 'Eisberg_Price' );

    $region   = eisberg_current_region();
    $currency = strtoupper( $region['currency'] );

    // Normalize input key lookup (shortcode attributes are parsed in lowercase)
    $key    = strtolower( $currency );
    $amount = isset( $atts[ $key ] ) ? trim( $atts[ $key ] ) : '';

    // Fallback to EUR if active currency amount is missing
    if ( '' === $amount && ! empty( $atts['eur'] ) ) {
        $amount   = trim( $atts['eur'] );
        $currency = 'EUR';
    }

    if ( '' === $amount ) {
        return '';
    }

    // Format based on currency output style matching eisberg_product_price_shortcode
    switch ( $currency ) {
        case 'CHF':
            $formatted = 'CHF ' . $amount . '.–';
            break;
        case 'GBP':
            $formatted = '£' . $amount;
            break;
        case 'USD':
            $formatted = '$' . $amount;
            break;
        case 'EUR':
        default:
            $formatted = 'EUR ' . $amount . '.';
            break;
    }

    return esc_html( $formatted );
}
add_shortcode( 'Eisberg_Price', 'eisberg_static_price_shortcode' );


/**
 * Process custom WooCommerce registration + AffiliateWP signup referral
 */
//add_action( 'template_redirect', 'process_custom_wc_registration', 5 );

function process_custom_wc_registration() {

    if ( ! isset( $_POST['register'] ) || is_user_logged_in() ) {
        return;
    }

    // Only run on real form posts
    if ( empty( $_POST['email'] ) ) {
        return;
    }

    $email    = sanitize_email( wp_unslash( $_POST['email'] ) );
    $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
    $password = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';

    $errors = new WP_Error();

    if ( empty( $email ) || ! is_email( $email ) ) {
        $errors->add( 'email', 'Please enter a valid email address.' );
    } elseif ( email_exists( $email ) ) {
        $errors->add( 'email', 'An account is already registered with this email.' );
    }

    if ( empty( $username ) && 'yes' === get_option( 'woocommerce_registration_generate_username' ) ) {
        $username = wc_create_new_customer_username( $email );
    }

    if ( empty( $username ) ) {
        $errors->add( 'username', 'Please enter a username.' );
    } elseif ( username_exists( $username ) ) {
        $errors->add( 'username', 'This username is already taken.' );
    }

    if ( empty( $password ) && 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
        $password = wp_generate_password();
    }

    if ( empty( $password ) ) {
        $errors->add( 'password', 'Please enter a password.' );
    }

    if ( $errors->has_errors() ) {
        foreach ( $errors->get_error_messages() as $message ) {
            wc_add_notice( $message, 'error' );
        }
        return;
    }

    // Create the account
    $customer_id = wc_create_new_customer( $email, $username, $password );

    if ( is_wp_error( $customer_id ) ) {
        wc_add_notice( $customer_id->get_error_message(), 'error' );
        return;
    }

    
    /*
    
    // ===== AffiliateWP signup referral =====
    if ( function_exists( 'affiliate_wp' ) ) {

        // 1) Normal AffiliateWP tracking
        $affiliate_id = affiliate_wp()->tracking->get_affiliate_id();
        $visit_id     = affiliate_wp()->tracking->get_visit_id();

        // 2) Fallback: read cookie directly (often needed with language prefixes / caching)
        if ( ! $affiliate_id && ! empty( $_COOKIE['affwp_ref'] ) ) {
            $affiliate_id = absint( $_COOKIE['affwp_ref'] );
        }

        // 3) Fallback: visit cookie
        if ( ! $visit_id && ! empty( $_COOKIE['affwp_ref_visit_id'] ) ) {
            $visit_id = absint( $_COOKIE['affwp_ref_visit_id'] );
        }

        // TEMP DEBUG – check wp-content/debug.log after one test signup
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( 'AFFWP REG → affiliate_id=' . var_export( $affiliate_id, true ) . ' visit_id=' . var_export( $visit_id, true ) );
        }

        if ( $affiliate_id && function_exists( 'affwp_is_affiliate_active' ) && affwp_is_affiliate_active( $affiliate_id ) ) {

            // Try both possible setting keys for signup amount
            $amount = floatval( affiliate_wp()->settings->get( 'signup_referrals_woocommerce_amount', 0 ) );
            if ( $amount <= 0 ) {
                $amount = floatval( affiliate_wp()->settings->get( 'signup_referral_amount', 0 ) );
            }

            $referral_id = affiliate_wp()->referrals->add( array(
                'affiliate_id' => absint( $affiliate_id ),
                'amount'       => $amount,
                'description'  => 'New user registration (User ID: ' . $customer_id . ')',
                'reference'    => $customer_id,
                'context'      => 'signup',
                'status'       => 'unpaid',
                'type'         => 'opt-in',
            ) );

            if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
                error_log( 'AFFWP REG → referral_id=' . var_export( $referral_id, true ) );
            }

            // Mark visit converted when possible
            if ( $referral_id && $visit_id ) {
                affiliate_wp()->visits->set_visit_converted( $visit_id );
            }
        }
    }
    // ===== end AffiliateWP =====

    */


    // Log the user in
    wc_set_customer_auth_cookie( $customer_id );

    wp_safe_redirect( home_url( '/' ) );
    exit;
}


/**
 * 2. The Shortcode (only displays the form)
 */
add_shortcode( 'wc_register_form', 'custom_wc_registration_form' );

function custom_wc_registration_form() {

    if ( is_user_logged_in() ) {
        return '<p class="wc-already-registered">You are already logged in. <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">Go to your account</a></p>';
    }

    ob_start();
    ?>
    <div class="custom-wc-register-wrapper">
        <h2 class="register-title">Create an Account</h2>

        <?php wc_print_notices(); // This will show success/error messages ?>

        <form method="post" class="woocommerce-form woocommerce-form-register">

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <p class="form-row form-row-wide">
                    <label for="reg_username">Username <span class="required">*</span></label>
                    <input type="text" class="input-text" name="username" id="reg_username" required />
                </p>
            <?php endif; ?>

            <p class="form-row form-row-wide">
                <label for="reg_email">Email address <span class="required">*</span></label>
                <input type="email" class="input-text" name="email" id="reg_email" required />
            </p>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                <p class="form-row form-row-wide">
                    <label for="reg_password">Password <span class="required">*</span></label>
                    <input type="password" class="input-text" name="password" id="reg_password" required />
                </p>
            <?php else : ?>
                <p style="font-size:14px; color:#666; margin-bottom:18px;">
                    A link to set your password will be sent to your email address.
                </p>
            <?php endif; ?>

            <p class="form-row">
                <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                <button type="submit" class="button" name="register" value="Register">
                    Create Account
                </button>
            </p>

        </form>
    </div>

    <style>
        .custom-wc-register-wrapper {
            max-width: 420px;
            margin: 0 auto;
            padding: 30px;
            font-family: inherit;
        }
        .custom-wc-register-wrapper .register-title {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
        }
        .custom-wc-register-wrapper .form-row {
            margin-bottom: 18px;
        }
        .custom-wc-register-wrapper label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 14px;
        }
        .custom-wc-register-wrapper input.input-text {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        .custom-wc-register-wrapper input.input-text:focus {
            border-color: #333;
            outline: none;
        }
        .custom-wc-register-wrapper .button {
            width: 100%;
            padding: 14px;
            background-color: #F8DD6E;
            color: #fff;
            border: none;
            border-radius: 999px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-family: "Poppins", Sans-serif;
        }
        .custom-wc-register-wrapper .button:hover {
        }
        .wc-already-registered {
            text-align: center;
            padding: 20px;
        }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Add "Customer" column to AffiliateWP Referrals admin table
 * Shows Name + Username + Email for signup referrals (and WooCommerce orders)
 */
add_filter( 'affwp_referral_table_columns', function( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        if ( 'reference' === $key ) {
            $new_columns['customer'] = __( 'Customer', 'affiliate-wp' );
        }
    }

    return $new_columns;
} );

add_filter( 'affwp_referral_table_customer', function( $value, $referral ) {

    $output = '';
    $reference = $referral->reference;

    // -------------------------------------------------
    // 1) Try as User ID first (signup / WooCommerce registration)
    // -------------------------------------------------
    if ( is_numeric( $reference ) ) {
        $user = get_user_by( 'id', absint( $reference ) );

        if ( $user ) {
            $display = $user->display_name ? $user->display_name : $user->user_login;

            $output  = '<strong>' . esc_html( $display ) . '</strong>';
            $output .= '<br><small>@' . esc_html( $user->user_login ) . '</small>';
            $output .= '<br><a href="mailto:' . esc_attr( $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a>';

            // Link to user edit screen
            $output = '<a href="' . esc_url( get_edit_user_link( $user->ID ) ) . '" style="text-decoration:none;color:inherit;">' . $output . '</a>';

            return $output;
        }
    }

    // -------------------------------------------------
    // 2) Fallback: real WooCommerce order
    // -------------------------------------------------
    if ( 'woocommerce' === $referral->context && function_exists( 'wc_get_order' ) ) {
        $order = wc_get_order( $reference );

        if ( $order ) {
            $full_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
            $email     = $order->get_billing_email();

            if ( $full_name || $email ) {
                $output = $full_name ? '<strong>' . esc_html( $full_name ) . '</strong>' : '';
                if ( $email ) {
                    $output .= ( $output ? '<br>' : '' ) . '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
                }
            }
        }
    }

    return $output ? $output : '—';
}, 10, 2 );

/**
 * Add "Customer" column to AffiliateWP Visits table
 * Shows name + email only when the visit was converted
 */
add_filter( 'affwp_visit_table_columns', function( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        // Insert after the "converted" column if it exists, otherwise at the end
        if ( 'converted' === $key ) {
            $new_columns['customer'] = __( 'Customer', 'affiliate-wp' );
        }
    }

    // Fallback if "converted" column is not present
    if ( ! isset( $new_columns['customer'] ) ) {
        $new_columns['customer'] = __( 'Customer', 'affiliate-wp' );
    }

    return $new_columns;
} );

add_filter( 'affwp_visit_table_customer', function( $value, $visit ) {

    // Only try to show customer data if this visit was converted
    if ( empty( $visit->referral_id ) ) {
        return '—';
    }

    $referral = affwp_get_referral( $visit->referral_id );

    if ( ! $referral ) {
        return '—';
    }

    $output = '';

    // Signup referral → reference is User ID
    if ( ( empty( $referral->context ) || 'signup' === $referral->context ) && is_numeric( $referral->reference ) ) {
        $user = get_user_by( 'id', absint( $referral->reference ) );

        if ( $user ) {
            $display = $user->display_name ? $user->display_name : $user->user_login;

            $output  = '<strong>' . esc_html( $display ) . '</strong>';
            $output .= '<br><small>@' . esc_html( $user->user_login ) . '</small>';
            $output .= '<br><a href="mailto:' . esc_attr( $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a>';
        }
    }

    // WooCommerce order referral
    if ( empty( $output ) && 'woocommerce' === $referral->context && function_exists( 'wc_get_order' ) ) {
        $order = wc_get_order( $referral->reference );

        if ( $order ) {
            $full_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
            $email     = $order->get_billing_email();

            if ( $full_name ) {
                $output = '<strong>' . esc_html( $full_name ) . '</strong>';
            }
            if ( $email ) {
                $output .= ( $output ? '<br>' : '' ) . '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
            }
        }
    }

    return $output ? $output : '—';
}, 10, 2 );

/**
 * When the visitor lands on a URL with NO language prefix
 * (e.g. https://www.eisberg-coaching.com/), force default language
 * and keep region + currency in sync.
 */
function eisberg_force_default_language_on_clean_url() {
    // Only on frontend
    if ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) ) {
        return;
    }

    // Skip if TranslatePress is not active
    if ( ! class_exists( 'TRP_Translate_Press' ) ) {
        return;
    }

    $trp           = TRP_Translate_Press::get_trp_instance();
    $url_converter = $trp->get_component( 'url_converter' );
    $settings      = $trp->get_component( 'settings' )->get_settings();

    // Language currently detected from the URL (null = no language prefix = default language)
    $lang_from_url = $url_converter->get_lang_from_url_string();

    // If there IS a language prefix → do nothing
    if ( ! empty( $lang_from_url ) ) {
        return;
    }

    // -------------------------------------------------
    // We are on a clean URL → force default language
    // -------------------------------------------------
    $default_lang = $settings['default-language']; // e.g. de_DE

    // Optional: also force the region to the one that matches the default language
    // Change 'ch' to 'eu' if you prefer Europe as the clean-URL default
    $default_region = 'ch';

    $regions = eisberg_regions();
    if ( ! isset( $regions[ $default_region ] ) ) {
        $default_region = 'eu'; // safety fallback
    }

    // Set / refresh the region cookie so currency stays correct
    if ( ! isset( $_COOKIE['eisberg_region'] ) || $_COOKIE['eisberg_region'] !== $default_region ) {
        setcookie(
            'eisberg_region',
            $default_region,
            time() + YEAR_IN_SECONDS,
            COOKIEPATH ? COOKIEPATH : '/',
            COOKIE_DOMAIN,
            is_ssl(),
            false
        );
        $_COOKIE['eisberg_region'] = $default_region; // available immediately
    }

    // Make sure TranslatePress knows we want the default language
    // (usually already the case on clean URLs, but this forces it)
    global $TRP_LANGUAGE;
    $TRP_LANGUAGE = $default_lang;
}
add_action( 'init', 'eisberg_force_default_language_on_clean_url', 5 );

/**
 * Auto-convert empty WOOCS fixed price fields using live exchange rates.
 */
add_filter('woocs_fixed_raw_perp', function($is_empty, $product_id, $currency, $type) {
    // If the fixed price field for the current currency is empty,
    // tell WOOCS to fall back to the live auto-calculated conversion rate.
    if ($is_empty) {
        return false;
    }
    return $is_empty;
}, 10, 4);

/**
 * Translate specific WooCommerce Subscriptions strings to German (Switzerland)
 */
add_filter( 'gettext', 'translate_wcs_strings_to_swiss_german', 20, 3 );
add_filter( 'ngettext', 'translate_wcs_strings_to_swiss_german', 20, 3 );

function translate_wcs_strings_to_swiss_german( $translated, $text, $domain ) {

    // Only run for WooCommerce / Subscriptions related domains
    if ( ! in_array( $domain, array( 'woocommerce', 'woocommerce-subscriptions', 'woocommerce-subscriptions-gifting' ), true ) ) {
        return $translated;
    }

    $translations = array(
        'Total due today'          => 'Heute fälliger Gesamtbetrag',
        'Yearly recurring total'   => 'Jährlich wiederkehrender Gesamtbetrag',
        'Starting'                 => 'Beginn',
    );

    if ( isset( $translations[ $text ] ) ) {
        return $translations[ $text ];
    }

    return $translated;
}