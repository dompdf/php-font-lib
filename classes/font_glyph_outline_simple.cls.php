<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: font_table_glyf.cls.php 46 2012-04-02 20:22:38Z fabien.menager $
 */

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Glyph_Outline_Simple extends Font_Glyph_Outline {
  const ON_CURVE       = 0x01;
  const X_SHORT_VECTOR = 0x02;
  const Y_SHORT_VECTOR = 0x04;
  const REPEAT         = 0x08;
  const THIS_X_IS_SAME = 0x10;
  const THIS_Y_IS_SAME = 0x20;
  
  function parse(){
    $data = parent::parse();
  
    if (!$this->size) {
      return;
    }
  
    $font = $this->getFont();
    
    $noc = $data["numberOfContours"];
    $data["endPtsOfContours"] = $font->r(array(self::uint16, $noc));
    
    if ($noc == 0) {
      $this->table = null;
      return;
    }
    
    $instructionLength = $font->readUInt16();
    $data["instructions"] = $font->r(array(self::uint8, $instructionLength));
    
    $count = $data["endPtsOfContours"][$noc-1] + 1;
    
    // Flags
    $flags = array();
    for ($index = 0; $index < $count; $index++) {
      $flags[$index] = $font->readUInt8();
      
      if ($flags[$index] & self::REPEAT) {
        $repeats = $font->readUInt8();
        
        for ($i = 1; $i <= $repeats; $i++) {
          $flags[$index+$i] = $flags[$index];
        }
        
        $index += $repeats;
      }
    }
    
    $points = array();
    foreach ($flags as $i => $flag) {
      $points[$i]["onCurve"] = $flag & self::ON_CURVE;
      $points[$i]["endOfContour"] = in_array($i, $data["endPtsOfContours"]);
    }
    
    // X Coords
    $x = 0;
    for($i = 0; $i < $count; $i++) {
      $flag = $flags[$i];
      
      if ($flag & self::THIS_X_IS_SAME) {
        if ($flag & self::X_SHORT_VECTOR) {
          $x += $font->readUInt8();
        }
      }
      else {
        if ($flag & self::X_SHORT_VECTOR) {
          $x -= $font->readUInt8();
        }
        else {
          $x += $font->readInt16();
        }
      }
      
      $points[$i]["x"] = $x;
    }
    
    // Y Coords
    $y = 0;
    for($i = 0; $i < $count; $i++) {
      $flag = $flags[$i];
      
      if ($flag & self::THIS_Y_IS_SAME) {
        if ($flag & self::Y_SHORT_VECTOR) {
          $y += $font->readUInt8();
        }
      }
      else {
        if ($flag & self::Y_SHORT_VECTOR) {
          $y -= $font->readUInt8();
        }
        else {
          $y += $font->readInt16();
        }
      }
      
      $points[$i]["y"] = $y;
    }
    
    $data["points"] = $points;
    
    $this->table = null;
    return $this->data = $data;
  }

  public function getSVGPath(){
    $path = "";
    
    $points = $this->data["points"];
    $length = count($points);
    $firstIndex = 0;
    $count = 0;
    
    for($i = 0; $i < $length; $i++) {
      $count++;
      
      if ($points[$i]["endOfContour"]) {
        $path .= $this->addContourToPath($points, $firstIndex, $count);
        $firstIndex = $i + 1;
        $count = 0;
      }
    }
    
    return $path;
  }
  
  protected function addContourToPath($points, $startIndex, $count) {
    $offset = 0;
    $path = "";
    
    while($offset < $count) {
      $point_m1 = $points[ ($offset == 0) ? ($startIndex+$count-1) : $startIndex+($offset-1)%$count ];
      $point    = $points[ $startIndex + $offset%$count ];
      $point_p1 = $points[ $startIndex + ($offset+1)%$count ];
      $point_p2 = $points[ $startIndex + ($offset+2)%$count ];
      
      if($offset == 0) {
        $path .= "M{$point['x']},{$point['y']} ";
      }
      
      if ($point["onCurve"] && $point_p1["onCurve"]) {
        $path .= "Q{$point_p1['x']},{$point_p1['y']} ";
        $offset++;
      } 
      else if ($point["onCurve"] && !$point_p1["onCurve"] && $point_p2["onCurve"]){
        $path .= "Q{$point_p1['x']},{$point_p1['y']},{$point_p2['x']},{$point_p2['y']} ";
        $offset += 2;
      } 
      else if ($point["onCurve"] && !$point_p1["onCurve"] && !$point_p2["onCurve"]){
        $path .= "Q{$point_p1['x']},{$point_p1['y']},".$this->midValue($point_p1['x'], $point_p2['x']).",".$this->midValue($point_p1['y'], $point_p2['y'])." ";
        $offset += 2;
      } 
      else if (!$point["onCurve"] && !$point_p1["onCurve"]) {
        $path .= "Q{$point['x']},{$point['y']},".$this->midValue($point['x'], $point_p1['x']).",".$this->midValue($point['y'], $point_p1['y'])." ";
        $offset++;
      } 
      else if (!$point["onCurve"] && $point_p1["onCurve"]) {
        $path .= "Q{$point['x']},{$point['y']},{$point_p1['x']},{$point_p1['y']} ";
        $offset++;
      } 
      else {
        break;
      }
    }
    
    $path .= "z ";
    
    return $path;
  }
  
  function midValue($a, $b){
    return $a + ($b - $a)/2;
  }
}