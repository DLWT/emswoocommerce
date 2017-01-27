<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Pay Integration.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Integration extends WC_Integration {

	/**
	 * The EMS store ID.
	 * @var String
	 */
	public $storename;

	/**
	 * EMS Shared Secret.
	 * @var string
	 */
	public $sharedsecret;

	/**
	 * Checkout option.
	 * @var string classic, checkoutoption
	 */
	public $checkoutoption;

	/**
	 * Pay mode.
	 * @var string payonly, payplus, fullpay
	 */
	public $mode;

	/**
	 * Transaction environment.
	 * @var string integration, production
	 */
	public $environment;

	/**
	 * Init and hook in the integration.
	 *
	 * @since  1.0.0
	 * @return Emspay_Integration
	 */
	public function __construct() {

		$this->id                 = 'emspay';
		$this->method_title       = __( 'EMS e-Commerce Gateway', 'emspay' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->storename      = $this->get_option( 'storename' );
		$this->sharedsecret   = $this->get_option( 'sharedsecret' );
		$this->checkoutoption = $this->get_option( 'checkoutoption', 'classic' );
		$this->mode           = $this->get_option( 'mode', 'payonly' );
		$this->environment    = $this->get_option( 'environment', 'integration' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'storename' => array(
				'title'             => __( 'Store Name', 'emspay' ),
				'type'              => 'text',
				'description'       => __( 'This is the ID of the store provided by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'sharedsecret' => array(
				'title'             => __( 'Shared Secret', 'emspay' ),
				'type'              => 'text',
				'description'       => __( 'This is the shared secret provided to you by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'checkoutoption' => array(
				'title'             => __( 'Checkout option', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'This field allows you to set the checkout option.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'classic',
				'options'           => array(
					// splits the payment process into multiple pages
					'classic'       => __( 'classic', 'emspay' ),
					// consolidates the payment method choice and the typical next step in a single page
					// limitations, supported payment methods are currently limited to:
					// credit cards, Maestro, PayPal, iDEAL, SOFORT and MasterPass
					'combinedpage'  => __( 'combinedpage', 'emspay' ),
				)
			),
			'mode' => array(
				'title'             => __( 'Pay mode', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'This field allows you to chosen mode for the transaction.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'payonly',
				'options'           => array(
					// shows a hosted page to collect the minimum set of information for the transaction
					// (e. g. cardholder name, card number, expiry date and card code for a credit card transaction)
					'payonly'  => __( 'payonly', 'emspay' ),
					// in addition to the above, the payment gateway collects a full set of billing information on an additional page
					'payplus'  => __( 'payplus', 'emspay' ),
					// in addition to the above, the payment gateway displays a third page to also collect shipping information
					'fullpay'  => __( 'fullpay', 'emspay' ),
				)
			),
			'environment' => array(
				'title'             => __( 'Environment', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'This setting specifies whether you will process live transactions, or whether you will process simulated transactions.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'integration',
				'options'           => array(
					'integration'  => __( 'Integration', 'emspay' ),
					'production'   => __( 'Production', 'emspay' ),
				)
			),
		);
	}


	/**
	 * Output the gateway settings screen.
	 */
	public function admin_options() {
		?>
		<h2><?php esc_html_e( $this->get_method_title() ) ?></h2>
		<div class="card">
			<h2><?php esc_html_e( 'Are you already a customer ?', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'If you are already registered as an EMS merchant then please enter the credentials and settings below.', 'emspay' ) ?>
			<br/><br/>
			<?php esc_html_e( 'For new customers please follow the link below to acquire an EMS merchant account.', 'emspay' ) ?>
			</p>

			<h2><?php esc_html_e( 'Becoming an EMS customer', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'Get a merchant account via this link:', 'emspay' ) ?>
			<a target="_blank" rel="external" href="https://www.emspay.eu/en/request-an-offer">https://www.emspay.eu/en/request-an-offer</a>
			</p>

			<h2><?php esc_html_e( 'Contact EMS Support', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'Visit the FAQ:', 'emspay' ) ?>
			<br/>
			<a target="_blank" rel="external" href="http://www.emspay.eu/en/customer-service/faq">http://www.emspay.eu/en/customer-service/faq</a>
			<br/><br/>
			<?php esc_html_e( 'Contact information:', 'emspay' ) ?>
			<br/>
			<a target="_blank" rel="external" href="https://www.emspay.eu/en/about-ems/contact">https://www.emspay.eu/en/about-ems/contact</a>
			<br/>
			</p>
		</div>

		<div><input type="hidden" name="section" value="<?php esc_attr_e( $this->id ) ?>" /></div>
		<table class="form-table">
		<?php echo $this->generate_settings_html( $this->get_form_fields(), false ) ?>
		</table>
		<?php
	}


	/**
	 * Validate the Store Name
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @return string
	 */
	public function validate_storename_field( $key, $value ) {
		return $this->validate_required_field( $key, $value, __( 'Store Name', 'emspay' ) );
	}


	/**
	 * Validate the Shared Secret
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @return string
	 */
	public function validate_sharedsecret_field( $key, $value ) {
		return $this->validate_required_field( $key, $value, __( 'Shared Secret', 'emspay' ) );
	}

	/**
	 * Validate a required field.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @param  string $title
	 * @return string
	 */
	public function validate_required_field( $key, $value, $title ) {
		$value = $this->validate_text_field( $key, $value );

		if ( empty( $value ) ) {
			WC_Admin_Settings::add_error( sprintf( __( 'Error: You must enter %s.', 'emspay' ), $title ) );
		}

		return $value;
	}

}
