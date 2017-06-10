<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> Demos: 99Points : reCaptcha Style Captcha with PHP and JQuery and CSS </title>

<script type="text/javascript" src="jquery-1.2.6.min.js"></script>
<link href="style.css" rel="stylesheet" type="text/css">

<script>
$(document).ready(function() { 

 // refresh captcha
 $('img#captcha-refresh').click(function() {  
		
		change_captcha();
 });
 
 function change_captcha()
 {
	document.getElementById('captcha').src="get_captcha.php?rnd=" + Math.random();
 }
 
});
 	
</script>		 

</head>

<body>

<div align="center" style=" height:600px; margin-top:150px;">
	
	
	
	<!-- Captcha HTML Code -->
	
	<div id="captcha-wrap">
		<div class="captcha-box">
			<img src="get_captcha.php" alt="" id="captcha" />
		</div>
		<div class="text-box">
			<label>Type the two words:</label>
			<input name="captcha-code" type="text" id="captcha-code">
		</div>
		<div class="captcha-action">
			<img src="refresh.jpg"  alt="" id="captcha-refresh" />
		</div>
	</div>
	
	<!--  Copy and Paste above html in any form and include CSS, get_captcha.php files to show the captcha  -->
	
	
</div>

		  
</body>
</html>