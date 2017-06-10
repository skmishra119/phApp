	</div>
</main>
     <script>
	$(function(){
		$('.table-striped').each(function() {
	        var thetable=jQuery(this);
	        $(this).find('tbody td').each(function() {
	            $(this).attr('data-title',thetable.find('thead th:nth-child('+(jQuery(this).index()+1)+')').text());
	        });
	    });
	});
     $(document).ready(function(){
    	var table = $('.table-striped').DataTable({"columnDefs":[{'targets': 0, 'searchable': false, 'orderable': false }], 'order': [1, 'asc']});
		if($('.table-striped').attr('id')=='selTargetTable'){
			table.destroy();
			$('#selTargetTable').dataTable({'bPaginate':false, "columnDefs":[{'targets': 0, 'searchable': false, 'orderable': false }], 'order': [1, 'asc']});
		}
    	$('.check_design #chkHDR').on('click', function(){
			var hProp=$(this).closest('th,td').find('input[type="checkbox"]').prop('checked');
			var rows = table.rows({'search':'applied'}).nodes();
			if(hProp==true){
		    	$('.check_design .square', rows).removeClass('active');
			}else{
				$('.check_design .square', rows).addClass('active');
			}
			$('input[type="checkbox"]', rows).prop('checked',!hProp);
		});
		
		$('.check_design').each(function(){
			var checkbox = $(this).closest('th,td').find('input[type="checkbox"]');
			if(checkbox.prop('checked')==true){
				$(this).find('.square').addClass('active');
			}
		});				   
							
	    $(document).on('click', '.check_design .square', function(){ 
	    	$(this).toggleClass('active');												  
	    	$(this).closest('th,td').find('input[type="checkbox"]').click();   	
		});

	    $('#repTable tbody').on('change', 'input[type="checkbox"]', function(){
	        if(!this.checked){
	        	$('.check_design #chkHDR').removeClass('active');												  
		    	$('.check_design #chkHDR').closest('th').find('input[type="checkbox"]').prop('checked',false);
	        }
	    });

	    $("#btnRepPDF").bind("click",function(){
	    	canvg(document.getElementById("canvas"), ChtchtReport.getSVG())
			var canvas = document.getElementById("canvas");
			var img = canvas.toDataURL("image/png");
   			img = img.replace("data:image/png;base64,", "");
   			var data = "bin_data=" + img; 
   			$.ajax({
   					type: "POST", 
   					url: "<?php echo DROOT.XROOT.$st->AdminPath; ?>getimg", 
   					data: data,
   					success: function(data){ 
   						var dta=split(":");
   						if(dta.length>1){
   							jAlert(dta[1],"TruPhish");
   						}
   					} 
   			});
		});
	    
	});			   
     </script> 
     <div class="<?php echo ((isset($_SESSION['UID'])!='')?'cover':''); ?>"></div>  
</body>

</html>
