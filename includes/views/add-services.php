<tr valign="top">
	<th><?php echo $this->get_tooltip_html( $data ); ?>
		<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
<td>
	<fieldset>
		<table class="csm_shipping_services wc_shipping widefat wp-list-table" id="custom_csm_services" cellspacing="0">
			<thead>
				<tr>
				<th>Service Name</th>
				<th>CSM Service Mapping</th>
				<th>Add More</th>
				</tr>
			</thead>
			<?php $count = 1; ?>
			<?php foreach ($services as $key => $service) { ?>
			<tr>
				<td><input type="text" name="woocommerce_tutsplus_add_services[service_name][]" placeholder="Service Name" value="<?php echo $service['service_name']; ?>"></td>
				<td>
				<select name="woocommerce_tutsplus_add_services[csm_service][]" value="<?php echo $service['csm_service']; ?>">
				<option value="">Select CSM service</option>
				<?php foreach ($csm_services as $key => $csm_service) { ?>
				<option value="<?php echo $key ?>" <?php echo ($service['csm_service'] == $key)?"selected":"" ?> ><?php echo $csm_service; ?></option>
				<?php } ?>
				</select>
				</td>
				<?php if($count == 1){ ?>
				<td><a href="javascript:void(0);" class="addCF">Add</a></td>
				<?php } else { ?>
				<td><a href="javascript:void(0);" class="remCF">Remove</a></td>
				<?php } ?>
			</tr>
			<?php $count++; } ?>
		</table>			
	</fieldset>
</td>
</tr>