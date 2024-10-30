jQuery(document).ready(function(){
  jQuery('.track-submit-btn').on("click",function(event) {
     var consignment_number = jQuery("#consignment_number").val();
      var data = { 'action': 'track_order_form_response','consignment_number': consignment_number};        
        jQuery.ajax({
          type: 'POST',
          data: data,
          url: ajax_object.ajaxurl,
          dataType : 'json',
          success: function(response){
            console.log(response);
            //jQuery("#track_order_response").html(response['message']); 
            if(response.message){
              location.reload();
            } 
          }
      }); 
  });
});



