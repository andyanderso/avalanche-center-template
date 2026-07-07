<?php

/**
 * @file
 * Danger rose for avalanche advisories, now in glorious "3-D"
 */

/**
 * Rose class
 */
class Rose3d {
  var $_size = 24;
  var $_destination = NULL;
  var $_font_path = NULL;
  var $_size_small = FALSE;
  var $_rose_array = array();
  var $_rose_width = 400;
  var $_rose_height = 440;
  var $_rose_labels = array();

  function __construct($size) { $this->_size = $size * 8; }
  function set_destination($s) { $this->_destination = $s; }
  function set_font_path($s) { $this->_font_path = $s; }

  function set_size_small() {
    $this->_size_small = TRUE;
    $this->_rose_width = 390;
    $this->_rose_height = 390;
  }

  function set_values($a) { $this->_rose_array = $a; }
  function set_labels($a) { 
    $this->_rose_labels = array();
    foreach ($a as $k => $v) { $this->_rose_labels[] = $v; }
  }

  function generate() {
    $filename = NULL;
    $label_font = $this->_font_path . '/' . 'Chivo-Regular.ttf';

    $w = $this->_rose_width;
    $h = $this->_rose_height;

    $img = imagecreatetruecolor($w, $h);
    if ($img) {

      // Initialize color tables
      $white     = imagecolorallocate($img, 255, 255, 255);
      $black     = imagecolorallocate($img,   0,   0,   0);
      $grey      = imagecolorallocate($img, 192, 192, 192);
      $red       = imagecolorallocate($img, 237,  28,  36);
      $orange    = imagecolorallocate($img, 247, 148,  30);
      $yellow    = imagecolorallocate($img, 255, 242,   0);
      $green     = imagecolorallocate($img,  80, 184,  72);
      $dkgrey    = imagecolorallocate($img, 162, 162, 162);
      $dkred     = imagecolorallocate($img, 207,  14,  18);
      $dkorange  = imagecolorallocate($img, 227, 108,  15);
      $dkyellow  = imagecolorallocate($img, 235, 202,   0);
      $dkgreen   = imagecolorallocate($img,  60, 144,  52);
      $ddkgrey   = imagecolorallocate($img, 132, 132, 132);
      $ddkred    = imagecolorallocate($img, 187,   0,   0);
      $ddkorange = imagecolorallocate($img, 207,  68,   0);
      $ddkyellow = imagecolorallocate($img, 215, 162,   0);
      $ddkgreen  = imagecolorallocate($img,  40, 104,  32);

      $colors = array($grey, 0, $green, $green, $yellow, $yellow, $orange, $orange, $red, $red, $black);
      $dkcolors = array($dkgrey, 0, $dkgreen, $dkgreen, $dkyellow, $dkyellow, $dkorange, $dkorange, $dkred, $dkred, $black);
      $ddkcolors = array($ddkgrey, 0, $ddkgreen, $ddkgreen, $ddkyellow, $ddkyellow, $ddkorange, $ddkorange, $ddkred, $ddkred, $black);

      imagefilltoborder($img, 0, 0, $white, $white);


      // Rose values
      list($hn, $hne, $he, $hse, $hs, $hsw, $hw, $hnw,
           $mn, $mne, $me, $mse, $ms, $msw, $mw, $mnw,
           $ln, $lne, $le, $lse, $ls, $lsw, $lw, $lnw) = $this->_rose_array;


      // Base colors
      $segments = array(
          array(200,155,182,123,200,115,218,123),                            //  0 High N
          array(200,155,218,123,239,128,242,143),                            //  1 High NE
          array(200,155,242,143,254,155,245,170),                            //  2 High E
          array(200,155,245,170,238,191,216,197),                            //  3 High SE
          array(200,155,216,197,200,205,184,197),                            //  4 High S
          array(200,155,184,197,162,191,155,170),                            //  5 High SW
          array(200,155,155,170,146,155,158,143),                            //  6 High W
          array(200,155,158,143,161,128,182,123),                            //  7 High NW
          array(200,85,234,99,218,123,200,115,182,123,166, 99),              //  8 Mid N
          array(234,99,270,107,276,132,247,143,239,128,218,123),             //  9 Mid NE
          array(276,132,295,163,275,190,250,180,260,162,247,143),            // 10 Mid E
          array(250,180,275,190,264,225,230,234,220,208,244,202),            // 11 Mid SE
          array(180,208,200,220,220,208,230,234,200,250,170,234),            // 12 Mid S
          array(125,190,150,180,156,202,180,208,170,234,136,225),            // 13 Mid SW
          array(124,132,153,143,140,162,150,180,125,190,105,163),            // 14 Mid W
          array(130,107,166,99,182,123,161,128,153,143,124,132),             // 15 Mid NW
          array(146,75,200,50,254,75,240,100,234,99,200,85,166,99,160,100),  // 16 Low N
          array(254,75,308,83,320,130,287,142,281,132,270,107,240,100),      // 17 Low NE
          array(320,130,355,172,323,215,283,203,303,173,287,142),            // 18 Low E
          array(283,203,323,215,307,268,250,280,236,248,275,241),            // 19 Low SE
          array(164,248,200,268,236,248,250,280,200,308,150,280),            // 20 Low S
          array(77,215,117,203,125,241,164,248,150,280,93,268),              // 21 Low SW
          array(80,130,113,142, 97,173,117,203,77,215,45,172),               // 22 Low W
          array(92,83,146,75,160,100,130,107,119,132,113,142,80,130),        // 23 Low NW
      );
      imagefilledpolygon($img, $segments[0],  4, $colors[$hn]);
      imagefilledpolygon($img, $segments[1],  4, $colors[$hne]);
      imagefilledpolygon($img, $segments[2],  4, $colors[$he]);
      imagefilledpolygon($img, $segments[3],  4, $colors[$hse]);
      imagefilledpolygon($img, $segments[4],  4, $colors[$hs]);
      imagefilledpolygon($img, $segments[5],  4, $colors[$hsw]);
      imagefilledpolygon($img, $segments[6],  4, $colors[$hw]);
      imagefilledpolygon($img, $segments[7],  4, $colors[$hnw]);
      imagefilledpolygon($img, $segments[8],  6, $colors[$mn]);
      imagefilledpolygon($img, $segments[9],  6, $colors[$mne]);
      imagefilledpolygon($img, $segments[10], 6, $colors[$me]);
      imagefilledpolygon($img, $segments[11], 6, $colors[$mse]);
      imagefilledpolygon($img, $segments[12], 6, $colors[$ms]);
      imagefilledpolygon($img, $segments[13], 6, $colors[$msw]);
      imagefilledpolygon($img, $segments[14], 6, $colors[$mw]);
      imagefilledpolygon($img, $segments[15], 6, $colors[$mnw]);
      imagefilledpolygon($img, $segments[16], 8, $colors[$ln]);
      imagefilledpolygon($img, $segments[17], 7, $colors[$lne]);
      imagefilledpolygon($img, $segments[18], 6, $colors[$le]);
      imagefilledpolygon($img, $segments[19], 6, $colors[$lse]);
      imagefilledpolygon($img, $segments[20], 6, $colors[$ls]);
      imagefilledpolygon($img, $segments[21], 6, $colors[$lsw]);
      imagefilledpolygon($img, $segments[22], 6, $colors[$lw]);
      imagefilledpolygon($img, $segments[23], 7, $colors[$lnw]);


      // Pockets of higher danger
      $pockets = array($grey, 0, $green, $yellow, $yellow, $orange, $orange, $red, $red, $black, $black);
      imagefilledellipse($img, 200, 128, 25, 18, $pockets[$hn]);
      imagefilledellipse($img, 225, 136, 25, 18, $pockets[$hne]);
      imagefilledellipse($img, 237, 156, 25, 18, $pockets[$he]);
      imagefilledellipse($img, 225, 177, 25, 18, $pockets[$hse]);
      imagefilledellipse($img, 200, 190, 25, 18, $pockets[$hs]);
      imagefilledellipse($img, 175, 177, 25, 18, $pockets[$hsw]);
      imagefilledellipse($img, 163, 156, 25, 18, $pockets[$hw]);
      imagefilledellipse($img, 175, 136, 25, 18, $pockets[$hnw]);
      imagefilledellipse($img, 200, 100, 25, 18, $pockets[$mn]);
      imagefilledellipse($img, 250, 119, 25, 18, $pockets[$mne]);
      imagefilledellipse($img, 275, 161, 25, 18, $pockets[$me]);
      imagefilledellipse($img, 252, 211, 25, 18, $pockets[$mse]);
      imagefilledellipse($img, 200, 235, 25, 18, $pockets[$ms]);
      imagefilledellipse($img, 148, 211, 25, 18, $pockets[$msw]);
      imagefilledellipse($img, 125, 161, 25, 18, $pockets[$mw]);
      imagefilledellipse($img, 150, 119, 25, 18, $pockets[$mnw]);
      imagefilledellipse($img, 200,  68, 25, 18, $pockets[$ln]);
      imagefilledellipse($img, 287,  99, 25, 18, $pockets[$lne]);
      imagefilledellipse($img, 323, 172, 25, 18, $pockets[$le]);
      imagefilledellipse($img, 292, 252, 25, 18, $pockets[$lse]);
      imagefilledellipse($img, 200, 288, 25, 18, $pockets[$ls]);
      imagefilledellipse($img, 108, 252, 25, 18, $pockets[$lsw]);
      imagefilledellipse($img,  77, 172, 25, 18, $pockets[$lw]);
      imagefilledellipse($img, 113,  99, 25, 18, $pockets[$lnw]);


      // Shaded sides
      $segments = array(
          array(239,128,247,143,242,143),                         //  0 High NE
          array(242,143,247,143,260,162,254,155),                 //  1 High E1
          array(254,155,260,162,250,180,245,170),                 //  2 High E1
          array(245,170,250,180,244,202,220,208,216,197,238,191), //  3 High SE
          array(184,197,200,205,216,197,220,208,200,220,180,208), //  4 High S
          array(155,170,162,191,184,197,180,208,156,202,150,180), //  5 High SW
          array(146,155,155,170,150,180,140,162),                 //  6 High W2
          array(153,143,158,143,146,155,140,162),                 //  7 High W1
          array(161,128,158,143,153,143),                         //  8 High NW
          array(270,107,281,132,276,132),                         //  9 Mid NE
          array(276,132,281,132,303,173,295,163),                 // 10 Mid E1
          array(295,163,303,173,283,203,275,190),                 // 11 Mid E2
          array(275,190,283,203,275,241,236,248,230,234,264,225), // 12 Mid SE
          array(170,234,200,250,230,234,236,248,200,268,164,248), // 13 Mid S
          array(124,190,136,225,170,234,164,248,125,241,117,203), // 14 Mid SW
          array(105,163,125,190,117,203,97,173),                  // 15 Mid W2
          array(119,132,124,132,105,163,97,173),                  // 16 Mid W1
          array(130,107,124,132,119,132),                         // 17 Mid NW
          array(308,83,328,130,320,130),                          // 18 Low NE
          array(320,130,328,130,365,184,355,172),                 // 19 Low E1
          array(355,172,365,184,332,228,323,215),                 // 20 Low E2
          array(323,215,332,228,317,284,256,296,250,280,307,268), // 21 Low SE
          array(150,280,200,308,250,280,256,296,200,329,144,296), // 22 Low S
          array(77,215,93,268,150,280,144,296,83,284,68,228),     // 23 Low SW
          array(45,172,77,215,68,228,35,184),                     // 24 Low W2
          array(72,130,80,130,45,172,35,184),                     // 25 Low W1
          array(92,83,80,130,72,130),                             // 26 Low NW
      );
      imagefilledpolygon($img, $segments[0], 3, $ddkcolors[$hne]);
      imagefilledpolygon($img, $segments[1], 4, $ddkcolors[$he]);
      imagefilledpolygon($img, $segments[2], 4, $dkcolors[$he]);
      imagefilledpolygon($img, $segments[3], 6, $dkcolors[$hse]);
      imagefilledpolygon($img, $segments[4], 6, $dkcolors[$hs]);
      imagefilledpolygon($img, $segments[5], 6, $dkcolors[$hsw]);
      imagefilledpolygon($img, $segments[6], 4, $dkcolors[$hw]);
      imagefilledpolygon($img, $segments[7], 4, $ddkcolors[$hw]);
      imagefilledpolygon($img, $segments[8], 3, $ddkcolors[$hnw]);
      imagefilledpolygon($img, $segments[9], 3, $ddkcolors[$mne]);
      imagefilledpolygon($img, $segments[10], 4, $ddkcolors[$me]);
      imagefilledpolygon($img, $segments[11], 4, $dkcolors[$me]);
      imagefilledpolygon($img, $segments[12], 6, $dkcolors[$mse]);
      imagefilledpolygon($img, $segments[13], 6, $dkcolors[$ms]);
      imagefilledpolygon($img, $segments[14], 6, $dkcolors[$msw]);
      imagefilledpolygon($img, $segments[15], 4, $dkcolors[$mw]);
      imagefilledpolygon($img, $segments[16], 4, $ddkcolors[$mw]);
      imagefilledpolygon($img, $segments[17], 3, $ddkcolors[$mnw]);
      imagefilledpolygon($img, $segments[18], 3, $ddkcolors[$lne]);
      imagefilledpolygon($img, $segments[19], 4, $ddkcolors[$le]);
      imagefilledpolygon($img, $segments[20], 4, $dkcolors[$le]);
      imagefilledpolygon($img, $segments[21], 6, $dkcolors[$lse]);
      imagefilledpolygon($img, $segments[22], 6, $dkcolors[$ls]);
      imagefilledpolygon($img, $segments[23], 6, $dkcolors[$lsw]);
      imagefilledpolygon($img, $segments[24], 4, $dkcolors[$lw]);
      imagefilledpolygon($img, $segments[25], 4, $ddkcolors[$lw]);
      imagefilledpolygon($img, $segments[26], 3, $ddkcolors[$lnw]);


      // Upper perimeter
      $segments = array(
          array(200,218,239,242,254,245,238,216,200,184,162,155,146,158,161,182,200), // 0 High X
          array(115,123,128,143,155,170,191,197,205,197,191,170,155,143,128,123,115), // 1 High Y
          array(200,234,270,276,295,275,264,230,200,170,136,125,105,124,130,166,200), // 2 Mid X
          array(85,99,107,132,163,190,225,234,250,234,225,190,163,132,107,99,85),     // 3 Mid Y
          array(200,254,308,320,355,323,307,250,200,150,93,77,45,80,92,146,200),      // 4 Low X
          array(50,75,83,130,172,215,268,280,308,280,268,215,172,130,83,75,50),       // 5 Low Y
      );
      $count = count($segments[0]) - 1;
      $z = 0;
      while($z < $count) {
        imageline($img, $segments[0][$z], $segments[1][$z], $segments[0][$z + 1], $segments[1][$z + 1], $black);
        imageline($img, $segments[2][$z], $segments[3][$z], $segments[2][$z + 1], $segments[3][$z + 1], $black);
        imageline($img, $segments[4][$z], $segments[5][$z], $segments[4][$z + 1], $segments[5][$z + 1], $black);
        $z++;
      }


      // Lower perimiter
      $segments = array(
          array(239,247,260,250,244,220,200,180,156,150,140,153,161), // 0 High X
          array(128,143,162,180,202,208,220,208,202,180,162,143,128), // 1 High Y
          array(270,281,303,283,275,236,200,164,125,117,97,119,130),  // 2 Mid X
          array(107,132,173,203,241,248,268,248,241,203,173,132,107), // 3 Mid Y
          array(308,328,365,332,317,256,200,144,83,68,35,72,92),      // 4 Low X
          array(83,130,184,228,284,296,329,296,284,228,184,130,83),   // 5 Low Y
      );
      $count = count($segments[0]) - 1;
      $z = 0;
      while ($z < $count) {
        imageline($img, $segments[0][$z], $segments[1][$z], $segments[0][$z + 1], $segments[1][$z + 1], $black);
        imageline($img, $segments[2][$z], $segments[3][$z], $segments[2][$z + 1], $segments[3][$z + 1], $black);
        imageline($img, $segments[4][$z], $segments[5][$z], $segments[4][$z + 1], $segments[5][$z + 1], $black);
        $z++;
      }


      // Vertical segments
      $segments = array(
          array(242,254,245,238,216,200,184,162,155,146,158), //  0 High XS
          array(247,260,250,244,220,200,180,156,150,140,153), //  1 High XE
          array(143,155,170,191,197,205,197,191,170,155,143), //  2 High YS
          array(143,162,180,202,208,220,208,202,180,162,143), //  3 High YE
          array(276,295,275,264,230,200,170,136,125,105,124), //  4 Mid XS
          array(281,303,283,275,236,200,164,125,117,97,119),  //  5 Mid XE
          array(132,163,190,225,234,250,234,225,190,163,132), //  6 Mid YS
          array(132,173,203,241,248,268,248,241,203,173,132), //  7 Mid YE
          array(320,355,323,307,250,200,150,93,77,45,80),     //  8 Low XS
          array(328,365,332,317,256,200,144,83,68,35,72),     //  9 Low XE
          array(130,172,215,268,280,308,280,268,215,172,130), // 10 Low YS
          array(130,184,228,284,296,329,296,284,228,184,130), // 11 Low YE
      );
      $count = count($segments[0]);
      $z = 0;
      while ($z < $count) {
       imageline($img, $segments[0][$z], $segments[2][$z], $segments[1][$z], $segments[3][$z], $black);
       imageline($img, $segments[4][$z], $segments[6][$z], $segments[5][$z], $segments[7][$z], $black);
       imageline($img, $segments[8][$z], $segments[10][$z], $segments[9][$z], $segments[11][$z], $black);
       $z++;
      }


      // Spoke segments
      $segments = array(
        array(200,200,200,200,200,200,200,200), //  0 High XS
        array(218,242,245,216,184,155,158,182), //  1 High XE
        array(155,155,155,155,155,155,155,155), //  2 High YS
        array(123,143,170,197,197,170,143,123), //  3 High YE
        array(218,247,250,220,180,150,153,182), //  4 Mid XS
        array(234,276,275,230,170,125,124,166), //  5 Mid XE
        array(123,143,180,208,208,180,143,123), //  6 Mid YS
        array(99,132,190,234,234,190,132,99),   //  7 Mid YE
        array(240,288,283,236,164,117,112,160), //  8 Low XS
        array(254,320,323,250,150,77,80,146),   //  9 Low XE
        array(100,142,203,248,248,203,142,100), // 10 Low YS
        array(75,130,215,280,280,215,130,75),   // 11 Low YE
      );
      $count = count($segments[0]);
      $z = 0;
      while ($z < $count) {
       imageline($img, $segments[0][$z], $segments[2][$z], $segments[1][$z], $segments[3][$z], $black);
       imageline($img, $segments[4][$z], $segments[6][$z], $segments[5][$z], $segments[7][$z], $black);
       imageline($img, $segments[8][$z], $segments[10][$z], $segments[9][$z], $segments[11][$z], $black);
       $z++;
      }

      if (!$this->_size_small) {
        imagettftext($img, 16, 0, 195, 45, $black, $label_font, N);
        imagettftext($img, 16, 0, 195, 350, $black, $label_font, S);
        imagettftext($img, 16, 0, 370, 190, $black, $label_font, E);
        imagettftext($img, 16, 0, 12, 190, $black, $label_font, W);
        imagettftext($img, 14, 0, 72, 76, $black, $label_font, NW);
        imagettftext($img, 14, 0, 304, 76, $black, $label_font, NE);
        imagettftext($img, 14, 0, 72, 306, $black, $label_font, SW);
        imagettftext($img, 14, 0, 304, 306, $black, $label_font, SE);
        if (!empty($this->_rose_labels)) {
          $label_font = $this->_font_path . '/' . 'vera.ttf';
          imageline($img, 165, 198, 106, 375, $black);
          imageline($img, 106, 375, 126, 375, $black);
          imagettftext($img, 13, 0, 135, 380, $black, $label_font, $this->_rose_labels[2]);

          imageline($img, 140, 235,  88, 391, $black);
          imageline($img,  88, 391, 126, 391, $black);
          imagettftext($img, 13, 0, 135, 398, $black, $label_font, $this->_rose_labels[1]);

          imageline($img, 113, 278, 67, 408, $black);
          imageline($img,  67, 408, 126, 408, $black);
          imagettftext($img, 13, 0, 135, 415, $black, $label_font, $this->_rose_labels[0]);
        }
      } else {
        $nimg = imagecreatetruecolor(500, 500);
        imagefilltoborder($nimg, 0, 0, $white, $white);
        imagecopy($nimg, $img, 50, 50, 0, 0, 387, 340);

        imagettftext($nimg, 28, 0, 238, 77, $black, $label_font, N); 
        imagettftext($nimg, 28, 0, 238, 427, $black, $label_font, S); 
        imagettftext($nimg, 28, 0, 432, 245, $black, $label_font, E); 
        imagettftext($nimg, 28, 0, 32, 245, $black, $label_font, W); 

        $img = imagecreatetruecolor(450, 400);
        imagefilltoborder($img, 0, 0, $white, $white);
        imagecopy($img, $nimg, 0, 0, 20, 40, 440, 400);
        imagedestroy($nimg);
      }

      // For testing, don't set a destination in the calling code and the
      // rose will be rendered to the screen.
      if ($this->_destination == NULL) { header("Content-type: image/png"); imagepng($img); }
      else { $filename = $this->_writepng($img); }
      imagedestroy($img);

      return $filename;
    }
  }

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
}
