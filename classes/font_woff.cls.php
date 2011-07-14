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

require_once dirname(__FILE__)."/font_truetype.cls.php";
require_once dirname(__FILE__)."/font_woff_table_directory_entry.cls.php";

class Font_WOFF extends Font_TrueType {
  private $origF;
  private $fileOffset = 0;
  
  function parseHeader(){
		if (isset($this->sfntVersion)) {
      return;
		}
		
    $this->seek(0);
    $this->sfntVersion   = $this->readFixed();
    
    $this->seek(0);
    $this->sfntVersionText   = $this->read(4);
    
    $this->flavor     = $this->readUInt32();
    $this->length     = $this->readUInt32();
    $this->numTables     = $this->readUInt16();
    $this->readUInt16();
    $this->totalSfntSize     = $this->readUInt32();
    $this->majorVersion     = $this->readUInt16();
    $this->minorVersion     = $this->readUInt16();
    $this->metaOffset     = $this->readUInt32();
    $this->metaLength     = $this->readUInt32();
    $this->metaOrigLength     = $this->readUInt32();
    $this->privOffset     = $this->readUInt32();
    $this->privLength     = $this->readUInt32();
  }
  
  function seekTag($tag) {
    if (!parent::seekTag($tag)) {
      return false;
    }
    
    $tableEntry = $this->table[$tag];
    
    if ($tableEntry->length == $tableEntry->origLength) {
      return;
    }
    
    $this->fileOffset = ftell($this->f);
    $data = $this->read($tableEntry->length);
    
    // PHP 5.1+
    $f = @fopen("php://temp", "rb+");
    
    if (!$f) {
      $f = fopen(tempnam(sys_get_temp_dir(), "fnt"), "rb+");
    }
    
    fwrite($f, gzuncompress($data));
    rewind($f);
    
    $this->origF = $this->f;
    $this->f = $f;
  }
  
  public function seek($offset) {
    return fseek($this->f, $offset - $this->fileOffset, SEEK_SET) == 0;
  }
  
  function quitTag(){
    if ($this->origF) {
      fclose($this->f);
      $this->f = $this->origF;
      $this->origF = null;
      $this->fileOffset = 0;
    }
  }
  
  function parseTableEntries(){
    $this->parseHeader();
    
    if (!empty($this->table)) {
      return;
    }
    
    for($i = 0; $i < $this->numTables; $i++) {
      $str = $this->read(Font_WOFF_Table_Directory_Entry::$entrySize);
      $entry = new Font_WOFF_Table_Directory_Entry($str);
      $this->table[$entry->tag] = $entry;
    }
  }
}
