<?php
@session_start();
?>
<div class="csm_track_order_form">
  <form action="javascript:void(0);" method="post">
      <div class="form-group" style="margin-bottom: 15px;">
        <input type="number" class="form-control" name="consignment_number" id="consignment_number" placeholder="Consignment Number">
      </div>
      <button type="submit" name="submit_track" class="btn btn-default track-submit-btn">Track</button>
  </form>
</div>
<?php
if(isset($_SESSION['track_response'])){
  $_SESSION['track_response']['flag'] = 0;
  $consignment_number = '';
  $consignment_status = '';
  $consignment_pickup = '';
  $consignment_delivery = '';
  $success = 0;
  $trackRecord = $_SESSION['track_response'];
  
  if($trackRecord['ResponseResult']['ResponseCode'] == '01'){
    $success = 1;
    if(isset($trackRecord['Consignment']['ConsignmentNumber'])){
      $consignment_number = $trackRecord['Consignment']['ConsignmentNumber'];
    }

    if(isset($trackRecord['Consignment']['ConsignmentStatus'])){
      $consignment_status = $trackRecord['Consignment']['ConsignmentStatus'];
    }

    if(isset($trackRecord['Consignment']['PickupDateTime'])){
      $consignment_pickup = $trackRecord['Consignment']['PickupDateTime'];
    }

    if(isset($trackRecord['Consignment']['DeliveryDateTime'])){
      $consignment_delivery = $trackRecord['Consignment']['DeliveryDateTime'];
    }
  } ?>
  <div class="csm_track_record">
    <?php if($success){ ?>
    <table>
      <tr>
        <th>Consignment Number</th>
        <th>Consignment Status</th>
        <th>Pickup Date Time</th>
        <th>Delivery Date Time</th>
      </tr>
      <tr>
        <td><?php esc_html_e( $consignment_number, 'text_domain' ); ?></td>
        <td><?php esc_html_e( $consignment_status, 'text_domain' ); ?></td>
        <td><?php esc_html_e( $consignment_pickup, 'text_domain' ); ?></td>
        <td><?php esc_html_e( $consignment_delivery, 'text_domain' ); ?></td>
      </tr>
    </table>
    <?php } ?>
  </div>
<?php 
unset($_SESSION['track_response']);
}
?>
