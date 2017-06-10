<?php
$data = str_replace(' ', '+', $_POST['bin_data']);
$data = base64_decode($data);
$fileName = '../public/uploads/chart.png';
$im = imagecreatefromstring($data);

if ($im !== false) {
	imagesavealpha($im, true);
	$color = imagecolorallocatealpha($im, 0, 0, 0, 127);
	imagefill($im, 0, 0, $color);
	imagepng($im, $fileName);
	imagedestroy($im);
	echo "Saved successfully";
} else {
	echo 'Error: An error occurred.';
}
?>