$(document).ready(function(){
	$(document).on('click','#header .menu_icon_cont', function(){
		  	$('#header #rlink').toggle();												   
	});	
	
	/*$(document).on('click','.date_picker input', function(){
		  	$(this).closest('.date_picker').find('img.ui-datepicker-trigger').click();										   
	});	;*/
	
	
	$(document).on('click', 'input.hasDatepicker', function(){
			$(this).closest('.date_picker').find('img.ui-datepicker-trigger').click();
			setTimeout(function(){
				$('#ui-datepicker-div').find('div').each(function(){
			    var ifTimePicker = $(this).hasClass('ui-timepicker-div');
					 if(!ifTimePicker) {
						 if($('.txt').hasClass('date_picker')){
							     $('#ui-datepicker-div').addClass('only_datepicker_cont');
						         var leftOffset = $('.date-selector .date_picker').offset().left;
						        $('#ui-datepicker-div').css('left', leftOffset+'px');
							 }
						
						 } else if(ifTimePicker) {
							  $('#ui-datepicker-div').removeClass('only_datepicker_cont');
							   return false;
					     }
	             });														
			}, 0);													
	});
	
});