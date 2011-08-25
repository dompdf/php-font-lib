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

require_once dirname(__FILE__)."/font_table_directory_entry.cls.php";

class Font_WOFF_Table_Directory_Entry extends Font_Table_Directory_Entry {
  function __construct(Font_WOFF $font) {
    parent::__construct($font);
    $this->offset = $this->readUInt32();
    $this->length = $this->readUInt32();
    $this->origLength = $this->readUInt32();
    $this->checksum = $this->readUInt32();
  }
  
  function startRead(){
    parent::startRead();
    
    if ($this->length == $this->origLength) {
      return true;
    }
    
    $font = $this->font;
    $font->fileOffset = $font->pos();
    
    $data = $font->read($this->length);
    
    $f = self::getTempFile();
    fwrite($f, gzuncompress($data));
    rewind($f);
    
    $font->origF = $font->f;
    $font->f = $f;
  }
  
  function endRead(){
    parent::endRead();
    
    $font = $this->font;
    
    if ($font->origF) {
      fclose($font->f);
      $font->f = $font->origF;
      $font->origF = null;
      $font->fileOffset = 0;
    }
  }
}
