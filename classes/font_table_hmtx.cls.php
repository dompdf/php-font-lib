<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `hmtx` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_hmtx extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $data = array();
    
    $numOfLongHorMetrics = $font->getData("hhea", "numOfLongHorMetrics");
    for($gid = 0; $gid < $numOfLongHorMetrics; $gid++) {
      $advanceWidth = $font->readUInt16();
      $leftSideBearing = $font->readUInt16();
      $data[$gid] = array($advanceWidth, $leftSideBearing);
    }
    
    $numGlyphs = $font->getData("maxp", "numGlyphs");
    if($numOfLongHorMetrics < $numGlyphs){
      $lastWidth = end($data);
      $data = array_pad($data, $numGlyphs, $lastWidth);
    }
    
    $this->data = $data;
  }
  
  /*protected function _encode() {
    $font = $this->getFont();
    $numOfLongHorMetrics = $font->getData("hhea", "numOfLongHorMetrics");
    
    $data = $this->data;
    $length = 0;
    for($gid = 0; $gid < $numOfLongHorMetrics; $gid++) {
      $length += $font->writeUInt16($data[$gid][0]);
      $length += $font->writeUInt16($data[$gid][1]);
    }
    
    return $length;
  }*/
  
  protected function _encode() {
    $font = $this->getFont();
    $subset = $font->getSubset();
    $data = $this->data;
    
    $length = 0;
    foreach($subset as $code => $gid) {
      $length += $font->writeUInt16($data[$gid][0]);
      $length += $font->writeUInt16($data[$gid][1]);
    }
    
    return $length;
  }
}