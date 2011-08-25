<?php
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * @link http://php-font-lib.googlecode.com/
 * @author Fabien Ménager
 */

/* $Id$ */

class Font_Table_Directory_Entry extends Font_Binary_Stream {
  /**
   * @var Font_TrueType
   */
  protected $font;
  
  /**
   * @var Font_Table
   */
  protected $font_table;
  
  public $entryLength = 4;
  
  var $tag;
  var $checksum;
  var $offset;
  var $length;
  
  protected $origF;
  
  static function computeChecksum($data){
    $len = strlen($data);
    $mod = $len % 4;
    
    if ($mod) { 
      $data = str_pad($data, $len + (4 - $mod), "\0");
    }
    
    $len = strlen($data);
    
    $hi = 0x0000;
    $lo = 0x0000;
    
    for ($i = 0; $i < $len; $i += 4) {
      $hi += (ord($data[$i]  ) << 8) + ord($data[$i+1]);
      $lo += (ord($data[$i+2]) << 8) + ord($data[$i+3]);
      $hi += $lo >> 16;
      $lo = $lo & 0xFFFF;
      $hi = $hi & 0xFFFF;
    }
    
    return ($hi << 8) + $lo;
  }
  
  function __construct(Font_TrueType $font) {
    $this->font = $font;
    $this->f = $font->f;
    $this->tag = $this->read(4);
  }
  
  function open($filename, $mode = self::modeRead) {
    // void
  }
  
  function setTable(Font_Table $font_table) {
    $this->font_table = $font_table;
  }
  
  function encode($entry_offset){
    Font::d("\n==== '$this->tag' ====");
    Font::d("Entry offset  = $entry_offset");
    
    $data = $this->font_table;
    $font = $this->font;
    
    $table_offset = $font->pos();
    $table_length = $data->encode();
    
    $font->seek($table_offset);
    $table_data = $font->read($table_length);
    
    $font->seek($entry_offset);
    
    $font->write($this->tag, 4);
    $font->writeUInt32(self::computeChecksum($table_data));
    $font->writeUInt32($table_offset);
    $font->writeUInt32($table_length);
    
    Font::d("Bytes written = $table_length");
    
    $font->seek($table_offset + $table_length);
  }
  
  /**
   * @return Font_TrueType
   */
  function getFont() {
    return $this->font;
  }
  
  function startRead() {
    $this->seek($this->offset);
  }
  
  function endRead() {
    //
  }
  
  function startWrite() {
    $this->seek($this->offset);
  }
  
  function endWrite() {
    //
  }
}

