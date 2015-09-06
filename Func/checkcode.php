<?PHP
session_start(); 
$image = imagecreatetruecolor(58,22); 
$color_Background = imagecolorallocate($image,255,255,255); 
imagefill($image,0,0,$color_Background); 
$key = range(0,9);
$string = null; 
$char_X = 6; 
$char_Y = 0; 
for($i=0;$i<4;$i++) 
{ 
$char_Y = mt_rand(0,5); 
$char = $key[mt_rand(0,count($key)-1)]; 
$string .= $char; 
$color_Char = imagecolorallocate($image,mt_rand(0,230),mt_rand(0,230),mt_rand(0,230)); 
imagechar($image,100,$char_X,$char_Y,$char,$color_Char); 
$char_X = $char_X + mt_rand(8,15); 
} 
$line_X1 = 0; 
$line_Y1 = 0; 
$line_X2 = 0; 
$line_Y2 = 0; 
for($i=0;$i<mt_rand(0,64);$i++) 
{ 
$line_X1 = mt_rand(0,58); 
$line_Y1 = mt_rand(0,22); 
$line_X2 = mt_rand(0,58); 
$line_Y2 = mt_rand(0,22); 
$line_X1 = $line_X1; 
$line_Y1 = $line_Y1; 
$line_X2 = $line_X1 + mt_rand(1,8); 
$line_Y2 = $line_Y1 + mt_rand(1,8); 
$color_Line = imagecolorallocate($image,mt_rand(0,230),mt_rand(0,230),mt_rand(0,230)); 
imageline($image,$line_X1,$line_Y1,$line_X2,$line_Y2,$color_Line); 
} 
$_SESSION['checkcode'] = $string;
@header("Expires: -1"); 
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE); 
@header("Pragma: no-cache"); 
header('Content-Type: image/jpeg'); 
imagepng($image); 
imagedestroy($image); 
?>