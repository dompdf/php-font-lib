<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

require_once dirname(__FILE__)."/font_glyph_outline.cls.php";

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_glyf extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    $offset = $font->pos();
    
    $loca = $font->getData("loca");
    $real_loca = array_slice($loca, 0, -1); // Not the last dummy loca entry
    
    $data = array();
    
    foreach($real_loca as $gid => $location) {
      $_offset = $offset + $loca[$gid];
      $_size   = $loca[$gid+1] - $loca[$gid];
      $data[$gid] = Font_Glyph_Outline::init($this, $_offset, $_size);
    }
    
    $this->data = $data;
  }
  
  public function toHTML(){
    $max = 160;
    $font = $this->getFont();
    
    $head = $font->getData("head");
    $head_json = json_encode($head);
    
    $os2 = $font->getData("OS/2");
    $os2_json  = json_encode($os2);
    
    $hmtx = $font->getData("hmtx");
    $hmtx_json = json_encode($hmtx);
      
    $names = $font->getData("post", "names");
    $glyphIndexArray = array_flip($font->getUnicodeCharMap());
    
    $width  = (abs($head["xMin"]) + $head["xMax"]);
    $height = (abs($head["yMin"]) + $head["yMax"]);
    
    if ($width > $max || $height > $max) {
      $ratio = max($width, $height) / $max;
      $width  = round($width/$ratio);
      $height = round($height/$ratio);
    }
    
    $n = 200;
    
    $s = "<h3>Only the first $n simple glyphs are shown</h3><script>
    Glyph.ratio = $ratio; 
    Glyph.head  = $head_json;
    Glyph.os2   = $os2_json;
    Glyph.hmtx  = $hmtx_json;
    </script>";
    
    foreach($this->data as $g => $glyph) {
      if (!$glyph instanceof Font_Glyph_Outline_Simple) {
        continue;
      }
      
      if ($n-- == 0) {
        break;
      }
      
      $shape = $glyph->getGlyphData();
      $shape["SVGContours"] = $glyph->getSVGContours();
      unset($shape["points"]);
      unset($shape["instructions"]);
      $shape_json = json_encode($shape);
    
      $char = isset($glyphIndexArray[$g]) ? "&#{$glyphIndexArray[$g]};" : "";
      $name = isset($names[$g]) ? $names[$g] : sprintf("uni%04x", $char);
      
      $s .= "<div class='glyph-view'>
              <span class='glyph-id'>$g</span> 
              <span class='char'>$char</span>
              <span class='char-name'>$name</span>
              <br />
              <canvas width='$width' height='$height' id='glyph-$g'></canvas>
            </div>
            <script>Glyph.draw(\$('#glyph-$g'), $shape_json, $g)</script>";
    }
    
    return $s;
  }
  
  /*
  protected function _encode() {
    $font = $this->getFont();
    $subset = $font->getSubset();
    $compoundGlyphOffsets = $font->compound_glyph_offsets;
    $data = $this->data;
    
    $loca = array();
    
    $length = 0;
    foreach($subset as $gid) {
      $loca[] = $length;
      $raw = $data[$gid];
      $len = strlen($raw);
      
      if (isset($compoundGlyphOffsets[$gid])) {
        $offsets = $compoundGlyphOffsets[$gid];
        foreach($offsets as $offset => $newGid) {
          list($raw[$offset], $raw[$offset+1]) = pack("n", $newGid);
        }
      }
      
      $length += $font->write($raw, strlen($raw));
    }
    
    $loca[] = $length; // dummy loca
    $font->getTableObject("loca")->data = $loca;
    
    return $length;
  }*/
}