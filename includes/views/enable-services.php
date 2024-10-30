<tr valign="top">
	<th><?php echo $this->get_tooltip_html( $data ); ?>
		<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
<td>
	<fieldset>
		<table class="csm_shipping_services wc_shipping widefat wp-list-table" id="custom_csm_services" cellspacing="0">
			<thead>
				<tr>
				<th>Service Code</th>
				<th>Service Name</th>
				<!-- <th>CSM Service Mapped</th> -->
				<th>Enable</th>
				</tr>
			</thead>
			<?php $count = 1; ?>
			<?php foreach ($services as $key => $service) { 
				$service_code = $service['csm_service'];
				$csm_service_name = $csm_services[$service_code];
				$enabled_services = array_column($current_services, 'enabled');
				?>
			<tr>
				<td><?php echo $service_code; ?></td>
				<td><input type="text" name="woocommerce_tutsplus_services[service_name][]" placeholder="Service Name" value="<?php echo $service['service_name']; ?>" readonly></td>
				<!-- <td><input type="text" name="woocommerce_tutsplus_services[service_name][]" placeholder="CSM Service" value="<?php //echo $csm_service_name; ?>" readonly></td> -->
				<td><input name="woocommerce_tutsplus_services[enabled][]" type="checkbox" class="checkbox-select-service" value="<?php echo esc_attr( $service_code ); ?>" <?php echo in_array($service_code, $enabled_services) ? 'checked' : '';?> /></td>
			</tr>
			<?php $count++; } ?>
		</table>			
	</fieldset>
</td>
</tr>