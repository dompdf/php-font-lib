<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_glyf extends Font_Table {
  protected function getGlyphData($loca, $gid){
    $font = $this->getFont();
    
    /*$entryStart = $this->entry->offset;
    $start = $entryStart + $loca[$gid];
    $font->seek($start);
    
    $data = $font->unpack(array(
      "numberOfContours" => self::int16,
      "xMin" => self::FWord,
      "yMin" => self::FWord,
      "xMax" => self::FWord,
      "yMax" => self::FWord,
    ));
    
    $data["outline"] = $font->read($loca[$gid+1] - $font->pos() - $entryStart);*/
    
    $font->seek($this->entry->offset + $loca[$gid]);
    return $font->read($loca[$gid+1] - $loca[$gid]);
  }
  
  protected function _parse(){
    $font = $this->getFont();
    $loca = $font->getData("loca");
    $real_loca = array_slice($loca, 0, -1); // Not the last dummy loca entry
    
    $data = array();
    
    foreach($real_loca as $gid => $location) {
      $data[$gid] = $this->getGlyphData($loca, $gid);
    }
    
    $this->data = $data;
  }
  
  protected function _encode(){
    $font = $this->getFont();
    $data = $this->data;
    $loca = array();
    $length = 0;
    
    foreach($data as $gid => $raw) {
      $loca[$gid] = $length;
      $length += $font->write($raw, strlen($raw));
    }
    
    $loca[] = $length; // dummy loca
    $font->getTableObject("loca")->data = $loca;
    
    return $length;
  }
}