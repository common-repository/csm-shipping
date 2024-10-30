<?php function csmlogistics_shipping_method(){
  if ( ! class_exists( 'CsmLogistics_Shipping_Method' ) ) {
    class CsmLogistics_Shipping_Method extends WC_Shipping_Method {
      public function __construct($instance_id = 0) {
          $aParams = array();
          parent::__construct( $instance_id );
          $this->id                 = 'csmlogistics'; 
          $this->instance_id = absint($instance_id);
          $this->method_title       = __( 'CSM Shipping', 'csmlogistics' );  
          //$this->method_description = __( 'Custom Shipping Method for CSM', 'csmlogistics' );
          $this->csm_service_codes = array(
                                        ''  => 'Select CSM delivery service',
                                        '0' => 'Custom',
                                        '1' => 'Standard',
                                        '2' => 'Express Saver',
                                        '3' => 'Express',
                                        '4' => 'Express Plus',
                                        '5' => 'World Wide Standard',
                                        '6' => 'Economy',
                                        '7' => 'CSM Mail',
                                        '8' => 'CSM Mail Plus',
                                        '9' => 'CSM eCom',
                                        '10'=> 'CSM eCom Plus',
                                        '11'=> 'CSM eCom Express',
                                        '12'=> 'CSM eCom AM',
                                        '14'=> 'DHL Paket',
                                        '21'=> 'CSM 9',
                                        '22'=> 'CSM 10.30',
                                        '23'=> 'CSM 12',
                                        '24'=> 'CSM 24',
                                        '25'=> 'CSM Express',
                                        '26'=> 'CSM Economy',
                                        '80'=> 'Saturday',
                                        '81'=> 'Saturday 12:00',
                                        '82'=> 'Saturday 10:30',
                                        '83'=> 'Sunday',
                                        '84'=> 'Sunday 12:00'
                                      );

          $this->init(); 
          if ( $this->instance_id ) {
            $this->title = $this->get_instance_option( 'title', __( 'CSM', 'csmlogistics' ) );
          }

          $this->supports = array(
                'settings',
                'shipping-zones',
                'instance-settings',
                //'instance-settings-modal',
            );

          add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
      }

      function init() {
          $this->init_form_fields();
          $this->init_instance_form_fields();
          $this->init_instance_settings(); 
          $this->init_settings();           
      }

      // This is Use For Add Csm Method Option in Woocommerce Setting with form fields
      public function init_instance_form_fields() {
          $this->instance_form_fields = array(
            'title' => array(
              'title' => __( 'Title', 'csmlogistics' ),
                'type' => 'text',
                'description' => __( 'Title to be display on site', 'csmlogistics' ),
                ),

              'csm_mapped_service' => array(
                'title'       => __( 'Select Mapped service for the Zone', 'csmlogistics' ),
                'type'    => 'select',
                'default' => '',
                'options' => $this->csm_service_codes
                ),

                'csm_extra_price_checkbox' => array(
                'title'       => __( 'Check to add custom price', 'csmlogistics' ),
                'type'    => 'checkbox',
                'default' => 'no',
                'description' => __( 'Enable to add custom rate.', 'csmlogistics' ),
                ),

                'csm_extra_price' => array(
                //'title'       => __( 'Custom Rate', 'csmlogistics' ),
                'type'    => 'text',
                'placeholder' => 'Custom Rate',
                'class' => 'number-only',
                ),
            );
      }

      function init_form_fields() {
          $this->form_fields = array(
           'enabled' => array(
                'title' => __( 'Enable CSM Shipping Plugin', 'csmlogistics' ),
                'type' => 'checkbox',
                'default' => 'yes'
                ),

           'title' => array(
              'title' => __( 'Title', 'csmlogistics' ),
                'type' => 'text',
                'description' => __( 'Title to be display on site', 'csmlogistics' ),
                'default' => __( 'TutsPlus Shipping', 'csmlogistics' )
                ),

           'shippernumber' => array(
              'title' => __( 'CSM Account Number', 'csmlogistics' ),
                'type' => 'text',
                'description' => __( 'CSM Account Number', 'csmlogistics' )  
              ),

           'shipperusername' => array(
              'title' => __( 'CSM API User Name', 'csmlogistics' ),
                'type' => 'text',
                'description' => __( 'CSM API user name', 'csmlogistics' )  
              ),

           'shipperpassword' => array(
              'title' => __( 'Shipper Password', 'csmlogistics' ),
                'type' => 'text',
                'description' => __( 'CSM API password', 'csmlogistics' )  
              ), 

            'shipperduttypaid' => array(
              'title' => __( 'Duties to be paid by Shipper', 'csmlogistics' ),
                'type' => 'checkbox',
                'description' => __( 'Please check with your account manager before applying this option', 'csmlogistics' ) ,
                'default' => 'no' 
              ), 
           ); 
      }

      /**************** for zone setting ********************/

      public function generate_services_html($key, $data){
        $field_key = $this->get_field_key( $key );
        $data = wp_parse_args( $data );
        $services  = $this->settings['add_services']; // get available services
        $current_services = $this->get_instance_option( 'services', array() ); //get zone services
        $csm_services =  $this->csm_service_codes;
        ob_start();
        include 'views/enable-services.php';
        return ob_get_clean() ?: '';
      }

      public function validate_services_field( $key, $value ) {
        if ( is_array( $value ) ) {
          //return array_map( array( $this, 'prepare_single_service' ), $value );
          $service_count = isset($value['service_name']) ? count($value['service_name']) : 0;
          $final_arr = array();
          for($i = 0; $i<$service_count; $i++){
            if(isset($value['enabled'][$i]) && !empty($value['enabled'][$i])){
             $final_arr[$i]['service_name'] = isset($value['service_name'][$i])?$value['service_name'][$i]:'';
             $final_arr[$i]['enabled'] = $value['enabled'][$i];
            }
          }
        }
        return $final_arr;
      } 

      /**************** end for zone setting ********************/

      /**************** for global setting ********************/
      
      public function generate_addservices_html($key, $data){
        $field_key = $this->get_field_key( $key ); 
        echo $Custom_script =  '<script> var AppConfig = '.json_encode($this->csm_service_codes).'</script>';
      
        /*$defaults  = array(
          'title'             => '',
          'disabled'          => false,
          'class'             => '',
          'css'               => '',
          'placeholder'       => '',
          'type'              => 'text',
          'desc_tip'          => false,
          'description'       => '',
          'custom_attributes' => array(),
        );*/

        $data = wp_parse_args( $data );
        $services  = $this->settings['add_services'];
        $csm_services =  $this->csm_service_codes;
        ob_start();
        include 'views/add-services.php';
        return ob_get_clean() ?: '';
      }

      public function validate_addservices_field( $key, $value ) {
        if ( is_array( $value ) ) {
          //return array_map( array( $this, 'prepare_single_service' ), $value );
          $service_count = isset($value['service_name']) ? count($value['service_name']) : 0;
          $final_arr = array();
          for($i = 0; $i<$service_count; $i++){
             $final_arr[$i]['service_name'] = isset($value['service_name'][$i])?$value['service_name'][$i]:'';
             $final_arr[$i]['csm_service'] = isset($value['csm_service'][$i])?$value['csm_service'][$i]:'';
          }
        }
        return $final_arr;
      } 

      /**************** end for global setting ********************/

      public function calculate_shipping( $package = array() ) {

          $current_services_mapped = $this->get_instance_option('csm_mapped_service');
          $custom_price_enable = $this->get_instance_option('csm_extra_price_checkbox');
          if($custom_price_enable == 'yes'){
            $get_custom_price = $this->get_instance_option('csm_extra_price');
            $rate = array(
                'id' => $this->id . $this->instance_id,
                'label' => $this->title,
                'cost' => $get_custom_price
            );
          } else {
          $cost = CSML_get_shipping_rate($package, $this->settings, $current_services_mapped, 'Rate');
            $rate = array(
                'id' => $this->id . $this->instance_id,
                'label' => $this->title,
                'cost' => $cost
            );            
          }

          $this->add_rate( $rate );                    
      }

      public function process_admin_options() {
        $saved = parent::process_admin_options();
        return $saved;
      }
    } // end WC_Shipping_Method class
  } // end if condition
}