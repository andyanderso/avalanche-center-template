<?php

/**
 * @file
 * Danger rose for avalanche advisories.
 */

/*$_rose_array stores colors as follows
0 none
1 pkt low
2 low
3 pkt mod
4 mod
5 pkt con
6 con
7 pkt high
8 high
9 pkt ext
10 ext
11 pkt blue
12 blue
13 blue pkt ext
array number increases clockwise
hn = [0]
mn =[8]
ln = [16]
*/
/**
 * Rose class
 */

function drawStar($x, $y, $radius, $spikes=5) {
    // $x, $y -> Position in the image
    // $radius -> Radius of the star
    // $spikes -> Number of spikes

    $coordinates = array();
    $angle = 360 / $spikes ;

    // Get the coordinates of the outer shape of the star
    $outer_shape = array();
    for($i=0; $i<$spikes; $i++){
        $outer_shape[$i]['x'] = $x + ($radius * cos(deg2rad(270 - $angle*$i)));
        $outer_shape[$i]['y'] = $y + ($radius * sin(deg2rad(270 - $angle*$i)));
      }

    // Get the coordinates of the inner shape of the star
    $inner_shape = array();
    for($i=0; $i<$spikes; $i++){
        $inner_shape[$i]['x'] = $x + (0.825*$radius * cos(deg2rad(270-158.5 - $angle*$i)));
        $inner_shape[$i]['y'] = $y + (0.825*$radius * sin(deg2rad(270-158.5 - $angle*$i)));
      }

    // Bring the coordinates in the right order
    foreach($inner_shape as $key => $value){
        if($key == (floor($spikes/2)+1))
             break;
        $inner_shape[] = $value;
        unset($inner_shape[$key]);
      }

    // Reset the keys
    $i=0;
    foreach($inner_shape as $value){
        $inner_shape[$i] = $value;
        $i++;
      }

    // "Merge" outer and inner shape
    foreach($outer_shape as $key => $value){
         $coordinates[] = $outer_shape[$key]['x'];
         $coordinates[] = $outer_shape[$key]['y'];
         $coordinates[] = $inner_shape[$key]['x'];
         $coordinates[] = $inner_shape[$key]['y'];
      }

    // Return the coordinates
    return $coordinates ;
    }

class Rose {
  var $_shadow;
  var $_size = 24;
  var $_arc_coord = array();
  var $_dot_coord = array();
  var $_destination = NULL;
  var $_font_path = NULL;
  var $_watermark = '';
  var $_size_small = FALSE;
  var $_rose_array = array();
  var $_rose_width = 402;
  var $_rose_height = 472;

  function set_labels($a) {
    $this->_rose_labels = array();
    foreach ($a as $k => $v) { $this->_rose_labels[] = strtoupper($v); }
  }

  function set_values($a) { $this->_rose_array = $a; }
  function set_width($w) { $this->_rose_width = $w; }
  function set_destination($s) { $this->_destination = $s; }
  function set_font_path($s) { $this->_font_path = $s; }
  function set_watermark($s) { $this->_watermark = $s; }
  function set_size_small() {
    $this->_size_small = TRUE;
    $this->_rose_width = $this->_rose_height;
  }
  
  



  // XXX: add error handling for creating dirs/files
  function _writepng($img) {
    $date = date('Ymd-His');
    $ym = substr($date, 0, 6);
    $counter = 0;
    $path = $this->_destination;
    if (!is_dir($path .'/'. $ym)) { mkdir($path .'/'. $ym, 0755, TRUE); }
    while (file_exists($path .'/'. $ym .'/'. $date .'-'. $counter .'.png')) { $counter += 1; }
    $filename = $date .'-'. $counter .'.png';
    imagepng($img, $path .'/'. $ym .'/'. $filename);

    return $ym . '/' . $filename;
  }

  // draw the rose, font handling, and any overlays
  function generate() {
    require_once 'gdext.php';

    $label_font = $this->_font_path . '/' . 'vera.ttf';

    $w = $this->_rose_width;
    $h = $this->_rose_height;

    $img = imagecreatetruecolor($w, $h);
    imageantialias($img, TRUE);

    if ($img) {
      imagesetthickness($img, 1);
      imagealphablending($img, TRUE);

      // base colors
      $white = imagecolorallocate($img, 255, 255, 255);
      $gray  = imagecolorallocate($img, 248, 248, 248);
      $lgray = imagecolorallocate($img, 153, 153, 153);
      
      $this->_shadow = imagecolorallocate($img, 102, 102, 102);
      $dgray = imagecolorallocate($img, 102, 102, 102);

      // danger ratings
      $black  = imagecolorallocate($img,   0,   0,  0);
      $red    = imagecolorallocate($img, 255,   0,  0);
      $orange = imagecolorallocate($img, 255, 153, 51);
      $yellow = imagecolorallocate($img, 255, 255, 51);
      $green  = imagecolorallocate($img,   0, 255,  0);
      $blue  = imagecolorallocate($img,   77, 184, 248);

      // background
      imagefilledrectangle($img, 0, 0, $w, $h, $gray);

      
// Make arrays for pies and pockets      
      $rose_values = $this->_rose_array;
      $PktArray = array();
      $PieArray = array();
      $CB = 0;
        while ($CB < 24) 
         {	
	 if(!isset($rose_values[$CB])) $rose_values[$CB]=0;
          $O=$rose_values[$CB];
	  if ($O==0) {$Pie="$lgray";$Pkt="$lgray";} 
	  elseif ($O==1) {$Pie="$lgray";$Pkt="$green";}
	  elseif ($O==2) {$Pie="$green";$Pkt="$green";}
	  elseif ($O==3) {$Pie="$green";$Pkt="$yellow";} 
	  elseif ($O==4) {$Pie="$yellow";$Pkt="$yellow";}	
	  elseif ($O==5) {$Pie="$yellow";$Pkt="$orange";}	
	  elseif ($O==6) {$Pie="$orange";$Pkt="$orange";}	
	  elseif ($O==7) {$Pie="$orange";$Pkt="$red";}	
	  elseif ($O==8) {$Pie="$red";$Pkt="$red";}
	  elseif ($O==9) {$Pie="$red";$Pkt="$black";}
	  elseif ($O==10) {$Pie="$black";$Pkt="$black";}
	  elseif ($O==11) {$Pie="$lgray";$Pkt="$lgray";}
	  elseif ($O==12) {$Pie="$blue";$Pkt="$blue";}
          elseif ($O==13) {$Pie="$lgray";$Pkt="$lgray";}

        array_push($PieArray,$Pie);	
        array_push($PktArray,$Pkt);

        $CB=$CB+1;
        }


//parameters for the look and size of the rose
$spikes = 8;
$outer_radius = 166; //big radius 250
$middle_radius = $outer_radius * .71;
$inner_radius = $outer_radius * .42;
$xsize = 200;//$outer_radius * 2.25 big size 600;
$ysize = 200;//$outer_radius * 2.25 big size 800;
$x= $xsize;
$y= $ysize;
$pcktrad = .1*$outer_radius;
//end rose parameters

//get coords for above treeline pie pieces      
$values_inner = drawStar($x, $y, $inner_radius , $spikes);
$inner_pie_values_n = array (
$values_inner[30] , 
$values_inner[31] , 
$values_inner[0] , 
$values_inner[1] , 
$values_inner[2] , 
$values_inner[3] , 
$values_inner[0] , 
$values_inner[9] );

$inner_pie_values_nw = array (
$values_inner[2] , 
$values_inner[3] , 
$values_inner[4] , 
$values_inner[5] , 
$values_inner[6] , 
$values_inner[7] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_w = array (
$values_inner[6] , 
$values_inner[7] , 
$values_inner[8] , 
$values_inner[9] , 
$values_inner[10] , 
$values_inner[11] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_sw = array (
$values_inner[10] , 
$values_inner[11] , 
$values_inner[12] , 
$values_inner[13] , 
$values_inner[14] , 
$values_inner[15] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_s = array (
$values_inner[14] , 
$values_inner[15] , 
$values_inner[16] , 
$values_inner[17] , 
$values_inner[18] , 
$values_inner[19] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_se = array (
$values_inner[18] , 
$values_inner[19] , 
$values_inner[20] , 
$values_inner[21] , 
$values_inner[22] , 
$values_inner[23] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_e = array (
$values_inner[22] , 
$values_inner[23] , 
$values_inner[24] , 
$values_inner[25] , 
$values_inner[26] , 
$values_inner[27] , 
$values_inner[0] , 
$values_inner[9] );


$inner_pie_values_ne = array (
$values_inner[26] , 
$values_inner[27] , 
$values_inner[28] , 
$values_inner[29] , 
$values_inner[30] , 
$values_inner[31] , 
$values_inner[0] , 
$values_inner[9] );

//draw colored above treeline rose pies
imagefilledpolygon($img, $inner_pie_values_n , 4 , $PieArray[0]);
imagefilledpolygon($img, $inner_pie_values_nw , 4 ,$PieArray[7]);
imagefilledpolygon($img, $inner_pie_values_w , 4 , $PieArray[6]);
imagefilledpolygon($img, $inner_pie_values_sw , 4 , $PieArray[5]);
imagefilledpolygon($img, $inner_pie_values_s , 4 , $PieArray[4]);
imagefilledpolygon($img, $inner_pie_values_se , 4 , $PieArray[3]);
imagefilledpolygon($img, $inner_pie_values_e , 4 , $PieArray[2]);
imagefilledpolygon($img, $inner_pie_values_ne , 4 , $PieArray[1]);

// get coords for the the near treeline tier
$values_middle = drawStar($x, $y, $middle_radius , $spikes);
$middle_pie_values_n = array (
$values_middle[30] , 
$values_middle[31] , 
$values_middle[0] , 
$values_middle[1] , 
$values_middle[2] , 
$values_middle[3] , 
$values_inner[2] , 
$values_inner[3] ,
$values_inner[0] , 
$values_inner[1] ,
$values_inner[30] , 
$values_inner[31]
 );


$middle_pie_values_nw = array (
$values_middle[2] , 
$values_middle[3] , 
$values_middle[4] , 
$values_middle[5] , 
$values_middle[6] , 
$values_middle[7] , 
$values_inner[6] , 
$values_inner[7] ,
$values_inner[4] , 
$values_inner[5] ,
$values_inner[2] , 
$values_inner[3]
);

$middle_pie_values_w = array (
$values_middle[6] , 
$values_middle[7] , 
$values_middle[8] , 
$values_middle[9] , 
$values_middle[10] , 
$values_middle[11] , 
$values_inner[10] , 
$values_inner[11] ,
$values_inner[8] , 
$values_inner[9] ,
$values_inner[6] , 
$values_inner[7]
);

$middle_pie_values_sw = array (
$values_middle[10] , 
$values_middle[11] , 
$values_middle[12] , 
$values_middle[13] , 
$values_middle[14] , 
$values_middle[15] , 
$values_inner[14] , 
$values_inner[15] ,
$values_inner[12] , 
$values_inner[13] ,
$values_inner[10] , 
$values_inner[11]  

);

$middle_pie_values_s = array (
$values_middle[14] , 
$values_middle[15] , 
$values_middle[16] , 
$values_middle[17] , 
$values_middle[18] , 
$values_middle[19] , 
$values_inner[18] , 
$values_inner[19] ,
$values_inner[16] , 
$values_inner[17] ,
$values_inner[14] , 
$values_inner[15] 
 );

$middle_pie_values_se = array (
$values_middle[18] , 
$values_middle[19] , 
$values_middle[20] , 
$values_middle[21] , 
$values_middle[22] , 
$values_middle[23] , 
$values_inner[22] , 
$values_inner[23] ,
$values_inner[20] , 
$values_inner[21] ,
$values_inner[18] , 
$values_inner[19] 
 );

$middle_pie_values_e = array (
$values_middle[22] , 
$values_middle[23] , 
$values_middle[24] , 
$values_middle[25] , 
$values_middle[26] , 
$values_middle[27] , 
$values_inner[26] , 
$values_inner[27] ,
$values_inner[24] , 
$values_inner[25] ,
$values_inner[22] , 
$values_inner[23]

);

$middle_pie_values_ne = array (
$values_middle[26] , 
$values_middle[27] , 
$values_middle[28] , 
$values_middle[29] , 
$values_middle[30] , 
$values_middle[31] , 
$values_inner[30] , 
$values_inner[31] ,
$values_inner[28] , 
$values_inner[29] ,
$values_inner[26] , 
$values_inner[27]

 );
//draw near treeline tier

imagefilledpolygon($img, $middle_pie_values_n , 6 , $PieArray[8]);
imagefilledpolygon($img, $middle_pie_values_nw , 6 , $PieArray[15]);
imagefilledpolygon($img, $middle_pie_values_w , 6 , $PieArray[14]);
imagefilledpolygon($img, $middle_pie_values_sw , 6 , $PieArray[13]);
imagefilledpolygon($img, $middle_pie_values_s , 6 , $PieArray[12]);
imagefilledpolygon($img, $middle_pie_values_se , 6 , $PieArray[11]);
imagefilledpolygon($img, $middle_pie_values_e , 6 , $PieArray[10]);
imagefilledpolygon($img, $middle_pie_values_ne , 6 , $PieArray[9]);


// get coords for the below treeline tier
$values_outer = drawStar($x, $y, $outer_radius , $spikes);
$outer_pie_values_n = array (
$values_outer[30] , 
$values_outer[31] , 
$values_outer[0] , 
$values_outer[1] , 
$values_outer[2] , 
$values_outer[3] , 
$values_middle[2] , 
$values_middle[3] ,
$values_middle[0] , 
$values_middle[1] ,
$values_middle[30] , 
$values_middle[31]
 );

$outer_pie_values_nw = array (
$values_outer[2] , 
$values_outer[3] , 
$values_outer[4] , 
$values_outer[5] , 
$values_outer[6] , 
$values_outer[7] , 
$values_middle[6] , 
$values_middle[7] ,
$values_middle[4] , 
$values_middle[5] ,
$values_middle[2] , 
$values_middle[3]
);

$outer_pie_values_w = array (
$values_outer[6] , 
$values_outer[7] , 
$values_outer[8] , 
$values_outer[9] , 
$values_outer[10] , 
$values_outer[11] , 
$values_middle[10]-1 , 
$values_middle[11] ,
$values_middle[8]-1 , 
$values_middle[9] ,
$values_middle[6]-1 , 
$values_middle[7]

);

$outer_pie_values_sw = array (
$values_outer[10] , 
$values_outer[11] , 
$values_outer[12] , 
$values_outer[13] , 
$values_outer[14] , 
$values_outer[15] , 
$values_middle[14] , 
$values_middle[15] ,
$values_middle[12]-1 , 
$values_middle[13] ,
$values_middle[10]-1 , 
$values_middle[11]  

);

$outer_pie_values_s = array (
$values_outer[14] , 
$values_outer[15] , 
$values_outer[16] , 
$values_outer[17] , 
$values_outer[18] , 
$values_outer[19] , 
$values_middle[18] , 
$values_middle[19] ,
$values_middle[16] , 
$values_middle[17] ,
$values_middle[14] , 
$values_middle[15] 
 );

$outer_pie_values_se = array (
$values_outer[18] , 
$values_outer[19] , 
$values_outer[20] , 
$values_outer[21] , 
$values_outer[22] , 
$values_outer[23] , 
$values_middle[22] , 
$values_middle[23] ,
$values_middle[20] , 
$values_middle[21] ,
$values_middle[18] , 
$values_middle[19] 
 );

$outer_pie_values_e = array (
$values_outer[22] , 
$values_outer[23] , 
$values_outer[24] , 
$values_outer[25] , 
$values_outer[26] , 
$values_outer[27] , 
$values_middle[26] , 
$values_middle[27] ,
$values_middle[24] , 
$values_middle[25] ,
$values_middle[22] , 
$values_middle[23]
);

$outer_pie_values_ne = array (
$values_outer[26] , 
$values_outer[27] , 
$values_outer[28] , 
$values_outer[29] , 
$values_outer[30] , 
$values_outer[31] , 
$values_middle[30] , 
$values_middle[31] ,
$values_middle[28] , 
$values_middle[29] ,
$values_middle[26] , 
$values_middle[27]
);

//Draw below treeline tier
imagefilledpolygon($img, $outer_pie_values_n , 6 , $PieArray[16]);
imagefilledpolygon($img, $outer_pie_values_nw , 6 , $PieArray[23]);
imagefilledpolygon($img, $outer_pie_values_w , 6 , $PieArray[22]);
imagefilledpolygon($img, $outer_pie_values_sw , 6 , $PieArray[21]);
imagefilledpolygon($img, $outer_pie_values_s , 6 , $PieArray[20]);
imagefilledpolygon($img, $outer_pie_values_se , 6 , $PieArray[19]);
imagefilledpolygon($img, $outer_pie_values_e , 6 , $PieArray[18]);
imagefilledpolygon($img, $outer_pie_values_ne , 6 , $PieArray[17]);

//Calculations for Pocket Offsets
$soffset = $outer_radius/6;
$diagoffset = ($outer_radius/6)/1.25;


// insert pockets on below treeline tier
imagefilledellipse($img, $values_outer[0], $values_outer[1]+$soffset, $pcktrad, $pcktrad, $PktArray[16]); //n
imagefilledellipse($img, $values_outer[28]-$diagoffset, $values_outer[29]+$diagoffset, $pcktrad, $pcktrad, $PktArray[17]); //ne
imagefilledellipse($img, $values_outer[24]-$soffset, $values_outer[25], $pcktrad, $pcktrad, $PktArray[18]); //e
imagefilledellipse($img, $values_outer[20]-$diagoffset, $values_outer[21]-$diagoffset, $pcktrad, $pcktrad, $PktArray[19]); //se
imagefilledellipse($img, $values_outer[16], $values_outer[17]-$soffset, $pcktrad, $pcktrad, $PktArray[20]); //s
imagefilledellipse($img, $values_outer[12]+$diagoffset, $values_outer[13]-$diagoffset, $pcktrad, $pcktrad, $PktArray[21]);//sw
imagefilledellipse($img, $values_outer[8]+$soffset, $values_outer[9], $pcktrad, $pcktrad, $PktArray[22]);//w
imagefilledellipse($img, $values_outer[4]+$diagoffset, $values_outer[5]+$diagoffset, $pcktrad, $pcktrad, $PktArray[23]);//nw

// insert pockets on near treeline tier
imagefilledellipse($img, $values_middle[0], $values_middle[1]+$soffset, $pcktrad, $pcktrad, $PktArray[8]); //n
imagefilledellipse($img, $values_middle[28]-$diagoffset, $values_middle[29]+$diagoffset, $pcktrad, $pcktrad, $PktArray[9]); //ne
imagefilledellipse($img, $values_middle[24]-$soffset, $values_middle[25], $pcktrad, $pcktrad, $PktArray[10]); //e
imagefilledellipse($img, $values_middle[20]-$diagoffset, $values_middle[21]-$diagoffset, $pcktrad, $pcktrad, $PktArray[11]); //se
imagefilledellipse($img, $values_middle[16], $values_middle[17]-$soffset, $pcktrad, $pcktrad, $PktArray[12]); //s
imagefilledellipse($img, $values_middle[12]+$diagoffset, $values_middle[13]-$diagoffset, $pcktrad, $pcktrad, $PktArray[13]);//sw
imagefilledellipse($img, $values_middle[8]+$soffset, $values_middle[9], $pcktrad, $pcktrad, $PktArray[14]);//w
imagefilledellipse($img, $values_middle[4]+$diagoffset, $values_middle[5]+$diagoffset, $pcktrad, $pcktrad, $PktArray[15]);//nw

// insert pockets on above treeline tier
imagefilledellipse($img, $values_inner[0], $values_inner[1]+$soffset, $pcktrad, $pcktrad, $PktArray[0]); //n
imagefilledellipse($img, $values_inner[28]-$diagoffset, $values_inner[29]+$diagoffset, $pcktrad, $pcktrad, $PktArray[1]); //ne
imagefilledellipse($img, $values_inner[24]-$soffset, $values_inner[25], $pcktrad, $pcktrad, $PktArray[2]); //e
imagefilledellipse($img, $values_inner[20]-$diagoffset, $values_inner[21]-$diagoffset, $pcktrad, $pcktrad, $PktArray[3]); //se
imagefilledellipse($img, $values_inner[16], $values_inner[17]-$soffset, $pcktrad, $pcktrad, $PktArray[4]); //s
imagefilledellipse($img, $values_inner[12]+$diagoffset, $values_inner[13]-$diagoffset, $pcktrad, $pcktrad, $PktArray[5]);//sw
imagefilledellipse($img, $values_inner[8]+$soffset, $values_inner[9], $pcktrad, $pcktrad, $PktArray[6]);//w
imagefilledellipse($img, $values_inner[4]+$diagoffset, $values_inner[5]+$diagoffset, $pcktrad, $pcktrad, $PktArray[7]);//nw



// draw a polygons used additional outline polygons to create thicker lines because of problems with imagaantialias and imagesetthickness
imagepolygon($img, $values_outer, 16, $black);
imagepolygon($img, $values_middle, 16, $black);
imagepolygon($img, $values_inner, 16, $black);

$outer_outline_radius = $outer_radius+1;
$values_outer_outline = drawStar($x, $y, $outer_outline_radius , $spikes);
imagepolygon($img, $values_outer_outline, 16, $black);
$outer_outline_radius2 = $outer_radius+2;
$values_outer_outline = drawStar($x, $y, $outer_outline_radius2 , $spikes);
imagepolygon($img, $values_outer_outline, 16, $black);
$outer_outline_radius3 = $outer_radius+3;
$values_outer_outline = drawStar($x, $y, $outer_outline_radius3 , $spikes);
//imagepolygon($img, $values_outer_outline, 16, $black);


$middle_outline_radius = $middle_radius+1;
$values_middle_outline = drawStar($x, $y, $middle_outline_radius , $spikes);
imagepolygon($img, $values_middle_outline, 16, $black);
$middle_outline_radius2 = $middle_radius+2;
$values_middle_outline = drawStar($x, $y, $middle_outline_radius2 , $spikes);
imagepolygon($img, $values_middle_outline, 16, $black);$middle_outline_radius = $middle_radius+2;
$middle_outline_radius3 = $middle_radius+3;
$values_middle_outline = drawStar($x, $y, $middle_outline_radius3 , $spikes);
//imagepolygon($img, $values_middle_outline, 16, $black);

$inner_outline_radius = $inner_radius+1;
$values_inner_outline = drawStar($x, $y, $inner_outline_radius , $spikes);
imagepolygon($img, $values_inner_outline, 16, $black);
$inner_outline_radius2 = $inner_radius+2;
$values_inner_outline = drawStar($x, $y, $inner_outline_radius2 , $spikes);
imagepolygon($img, $values_inner_outline, 16, $black);
$inner_outline_radius3 = $inner_radius+3;
$values_inner_outline3 = drawStar($x, $y, $inner_outline_radius3 , $spikes);
//imagepolygon($img, $values_inner_outline, 16, $black);


// draw lines that divide up the rose used polygons to create thicker lines because of problems with imagaantialias and imagesetthickness
//NNW->SSE
$thickline1=array ( $values_outer[2]+(3/4) , $values_outer[3]-(3/4) , $values_outer[2]-(3/4) , $values_outer[3]+(3/4) , $values_outer[18]-(3/4) , $values_outer[19]+(3/4) , $values_outer[18]+(3/4) , $values_outer[19]-1);
imagefilledpolygon($img, $thickline1 , 4, $black);
//imageline($img, $values_outer[2] , $values_outer[3] , $values_outer[18] , $values_outer[19] , $black);

//NNE->SSW
$thickline2=array ( $values_outer[30]+1 , $values_outer[31]-1 , $values_outer[30]-1 , $values_outer[31]+1 , $values_outer[14]-1 , $values_outer[15]+1 , $values_outer[14]+1 , $values_outer[15]-1);
imagefilledpolygon($img, $thickline2 , 4, $black);
//imageline($img, $values_outer[30] , $values_outer[31] , $values_outer[14] , $values_outer[15] , $black);


//ENE->WSW
$thickline3=array ( $values_outer[26]+(3/2) , $values_outer[27]-(3/2) , $values_outer[26]-(3/2) , $values_outer[27]+(3/2) , $values_outer[10]-(3/2) , $values_outer[11]+(3/2) , $values_outer[10]+(3/2) , $values_outer[11]-(3/2));
imagefilledpolygon($img, $thickline3 , 4, $black);
//imageline($img, $values_outer[26] , $values_outer[27] , $values_outer[10] , $values_outer[11] , $black);

//ESE->WNW
$thickline4=array ( $values_outer[22]+(3/4) , $values_outer[23]-(3/4) , $values_outer[22]-(3/4) , $values_outer[23]+(3/4) , $values_outer[6]-(3/4) , $values_outer[7]+(3/4) , $values_outer[6]+(3/4) , $values_outer[7]-(3/4));
imagefilledpolygon($img, $thickline4 , 4, $black);
//imageline($img, $values_outer[22] , $values_outer[23] , $values_outer[6] , $values_outer[7] , $black);


      // aspect letters
//build coords for Direction labels
$Nx = $values_outer[0]-10;
$Ny = $values_outer[1] - 6;
$NWx = $values_outer[4] -26;
$NWy = $values_outer[5] - 6;
$Wx = $values_outer[8] -34 ;
$Wy = $values_outer[9] +12;
$SWx = $values_outer[12] -26 ;
$SWy = $values_outer[13] +22;
$Sx = $values_outer[16] - 10 ;
$Sy = $values_outer[17] +30;
$SEx = $values_outer[20] -4;
$SEy = $values_outer[21] +22;
$Ex = $values_outer[24] + 6 ;
$Ey = $values_outer[25] +12;
$NEx = $values_outer[28] -4 ;
$NEy = $values_outer[29] - 6;


// insert letters for the various directions
   
      imagettftext($img, 23, 0, $Nx+1, $Ny+1, $lgray, $label_font, "N");
      imagettftext($img, 23, 0, $Nx, $Ny, $black, $label_font, "N");
      imagettftext($img, 23, 0, $Sx+1, $Sy+1, $lgray, $label_font, "S");
      imagettftext($img, 23, 0, $Sx, $Sy, $black, $label_font, "S");
      imagettftext($img, 23, 0,  $Wx+1, $Wy+1, $lgray, $label_font, "W");
      imagettftext($img, 23, 0,  $Wx, $Wy, $black, $label_font, "W");
      imagettftext($img, 23, 0, $Ex+1, $Ey+1, $lgray, $label_font, "E");
      imagettftext($img, 23, 0, $Ex, $Ey, $black, $label_font, "E");

      if (!$this->_size_small) {
        // minor aspect letters
        imagettftext($img, 15, 0,  $NWx+1, $NWy+1, $lgray, $label_font, "NW");
        imagettftext($img, 15, 0,  $NWx, $NWy, $dgray, $label_font, "NW");

        imagettftext($img, 15, 0,  $SWx+1, $SWy+1, $lgray, $label_font, "SW");
        imagettftext($img, 15, 0,  $SWx, $SWy, $dgray, $label_font, "SW");

        imagettftext($img, 15, 0, $NEx+1, $NEy+1, $lgray, $label_font, "NE");
        imagettftext($img, 15, 0, $NEx, $NEy, $dgray, $label_font, "NE");

        imagettftext($img, 15, 0,  $SEx+1, $SEy+1, $lgray, $label_font, "SE");
        imagettftext($img, 15, 0,  $SEx, $SEy, $dgray, $label_font, "SE");
      }
          // triple picker
imagefilledellipse($img, $values_inner[12]+$diagoffset+1, $values_inner[13]-$diagoffset+17, $pcktrad/2, $pcktrad/2, $black);//above treeline
imagefilledellipse($img, $values_middle[12]+$diagoffset+10, $values_middle[13]-$diagoffset+17, $pcktrad/2, $pcktrad/2, $black);//near treeline
imagefilledellipse($img, $values_outer[12]+$diagoffset+18, $values_outer[13]-$diagoffset+17, $pcktrad/2, $pcktrad/2, $black);//below treeline

                    imageline($img, $values_inner[12]+$diagoffset+1, $values_inner[13]-$diagoffset+17, $values_inner[12]+$diagoffset-68.5, $values_inner[13]-$diagoffset+195, $black);
	  imageline($img, $values_inner[12]+$diagoffset-68.5, $values_inner[13]-$diagoffset+195, $values_inner[12]+$diagoffset-57, $values_inner[13]-$diagoffset+195, $black);
          imagettftext($img, 13, 0, $values_inner[12]+$diagoffset-55, $values_inner[13]-$diagoffset+200, $black, $label_font, $this->_rose_labels[2]);//above treeline

                    imageline($img, $values_middle[12]+$diagoffset+10, $values_middle[13]-$diagoffset+17, $values_middle[12]+$diagoffset-55, $values_middle[13]-$diagoffset+180, $black);
	  imageline($img, $values_middle[12]+$diagoffset-55, $values_middle[13]-$diagoffset+180, $values_middle[12]+$diagoffset-44, $values_middle[13]-$diagoffset+180, $black);
          imagettftext($img, 13, 0, $values_middle[12]+$diagoffset-42, $values_middle[13]-$diagoffset+185, $black, $label_font, $this->_rose_labels[1]);//near treeline

          imageline($img, $values_outer[12]+$diagoffset+18, $values_outer[13]-$diagoffset+17, $values_outer[12]+$diagoffset-43, $values_outer[13]-$diagoffset+165, $black);
	  imageline($img, $values_outer[12]+$diagoffset-43, $values_outer[13]-$diagoffset+165, $values_outer[12]+$diagoffset-32, $values_outer[13]-$diagoffset+165, $black);
          imagettftext($img, 13, 0, $values_outer[12]+$diagoffset-30, $values_outer[13]-$diagoffset+170, $black, $label_font, $this->_rose_labels[0]);//below treeline


      $ow = $this->_rose_width;
      $oh = $this->_rose_height;
      $nw = $nh = 0;

      if (!$this->_size_small) {
/*        // add the bounding box for the watermark
        $bbox = imagettfbbox(13, 0, $label_font, $this->_watermark);
        $bbox_h = abs($bbox[5] - $bbox[3]);
        $bbox_w = abs($bbox[2] - $bbox[0]);
        $bbox_x = $w - $bbox_w;
        imagettftext($img, 13, 0, $bbox_x - 7, $oh - 7, $lgray, $label_font, $this->_watermark);
*/
        // seems a strange bug in gd2... odd text blur if 50% reduction in size. Adding 1 corrects it.
        $nw = $ow / 2 + 1;
        $nh = $oh / 2 + 1;

      } else {
        $nw = $nh = 100;
      }

      if ($this->_destination == NULL) { header("Content-type: image/png"); }

      $nimg = imagecreatetruecolor($nw, $nh);
      imageantialias($nimg, FALSE);
      imagecopyresampled($nimg, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
      $filename = $this->_writepng($nimg);
      imagedestroy($img);
      imagedestroy($nimg);

      return $filename;
    }
  }
}
