jQuery(document).ready(function(){ 
	  
	jQuery(".addCF").click(function(){
		if(AppConfig != undefined){
			var html = '';
			html = '<tr><td><input type="text" name="woocommerce_tutsplus_add_services[service_name][]" value="" placeholder="Service Name" /></td><td>';
			
			var sel = '<select name="woocommerce_tutsplus_add_services[csm_service][]">';
				jQuery.each(AppConfig, function(i, val) {
				 sel += '<option value='+i+'>'+val+'</option>';
				});
				sel += '</select></td>';

			html += sel;
			html += '<td> <a href="javascript:void(0);" class="remCF">Remove</a></td>';
			html += '</td></tr>';
			jQuery("#custom_csm_services").append(html);
		}
	});
    jQuery("#custom_csm_services").on('click','.remCF',function(){
        jQuery(this).parent().parent().remove();
    });



  /*jQuery(document).on("click", '.csm_print_label', function(event) {
       var current_order_id = jQuery(this).attr('data-order-id');
        var data = { 'action': 'print_label_response','current_order_id': current_order_id};        
          jQuery.ajax({
            type: 'POST',
            data: data,
            url: ajax_object.ajaxurl,
            dataType : 'json',
           
            success: function(response){
              console.log(response['message']);
            	alert(response['message']);
            	if(response['message']){ 
            	var pdf = 'data:application/octet-stream;base64,' + response['message'];
            	downloadPDF(pdf);

            }

            }
      	
        }); 
    });*/
    
  /*window.downloadPDF = function downloadPDF(pdf) {
      var dlnk = document.getElementById('dwnldLnk');
        dlnk.href = pdf;
       dlnk.click();
   } */

  jQuery("#progress").progressbar({  });

  quiz_count = 0;
  var currValue = 0,
      toValue = 0; 

  jQuery('.download_multiple_labels li a').each(function(){
    jQuery(this).parent().find( '#dwnldLnk' )
      quiz_count += jQuery(this)[0].click();
      var data = jQuery(this).attr('data-attr');
      download_single_label(data);

      // jQuery('.download_multiple_labels').parent().find( '.count_process' )
      jQuery('#cont')[0].click();
      jQuery("#cont").button().click(function() {
       setTimeout(function(){
        jQuery('.download_multiple_labels').parent().find( '#overlay' ).fadeOut(); }, 700);
        currValue = jQuery('.download_multiple_labels').parent().find( '#progress' ).progressbar("value");
        if (currValue + 100 <= 100) {
            toValue = currValue + 100;
            animateProgress();
        }
        //jQuery('#back_to_order')[0].click();
      });
    });
  jQuery('#cont').trigger('click');


  function animateProgress() {
    if (currValue < toValue) {
        jQuery("#progress").progressbar("value", currValue + 1);
        currValue = jQuery("#progress").progressbar("value");
        setTimeout(animateProgress, 4);
    }
  }


  jQuery('input#woocommerce_csmlogistics_csm_extra_price_checkbox').change(function(){          
     if (this.checked) {
        jQuery('#woocommerce_csmlogistics_csm_extra_price').removeClass('hide_field');
     } else {
        jQuery('#woocommerce_csmlogistics_csm_extra_price').addClass('hide_field');
        jQuery('#woocommerce_csmlogistics_csm_extra_price').val('');         
     }
  });


  jQuery(document).on("click", '#dwnldLnk', function() {
    var data = jQuery(this).attr('data-attr');
    download_single_label(data);
  });

  function download_single_label(data){
      var fileName = "label.pdf";
      if (window.navigator && window.navigator.msSaveOrOpenBlob) { // IE workaround
          var byteCharacters = atob(data);
          var byteNumbers = new Array(byteCharacters.length);
          for (var i = 0; i < byteCharacters.length; i++) {
              byteNumbers[i] = byteCharacters.charCodeAt(i);
          }
          var byteArray = new Uint8Array(byteNumbers);
          var blob = new Blob([byteArray], {type: 'application/pdf'});
          window.navigator.msSaveOrOpenBlob(blob, fileName);
      }
      else { // much easier if not IE
         //window.location.href = "data:application/octet-stream;base64," + data;
          window.open('data:application/pdf;base64,' + data);
          //location.reload();

        // var link = document.createElement("a");
        // link.href = "data:application/octet-stream;base64,"+data;
        // link.target = '_blank';
        // link.download = "label.pdf";
        // link.click();
       // window.open(link.download, '_blank');
       // window.open(link.href,'_blank');
        //let pdfWindow = window.open("");
//pdfWindow.document.write("<iframe width='100%' height='100%' src='data:application/octet-stream;base64, " + encodeURI(data)+"'></iframe>")

      }
  }

  jQuery('.number-only').on('keypress', function(e){
      if (e.charCode >= 32 && e.charCode < 127 && 
          !/^-?\d*[.,]?\d*$/.test(this.value + '' + String.fromCharCode(e.charCode))){
        return false;
      }
  });

});
// jQuery( function() {  
// var data_attr_value = jQuery(".hideifempty").attr('data-attr');
//         if(data_attr_value== ''){
//           jQuery(".hideifempty").hide();
//         }

//         } );




