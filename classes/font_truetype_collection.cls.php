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

require_once dirname(__FILE__)."/font_binary_stream.cls.php";
require_once dirname(__FILE__)."/font_truetype.cls.php";

class Font_TrueType_Collection extends Font_Binary_Stream implements Iterator {
  private $position = 0;
  
  protected $collection = array();
  protected $version;
  protected $numFonts;
  
  function parse(){
    $tag = $this->read(4);
    
    $this->version = $this->readFixed();
    $this->numFonts = $this->readUInt32();
    
    for($i = 0; $i < $this->numFonts; $i++) {
      $this->collection[] = $this->readUInt32();
    }
  }
  
  /**
   * @param int $fontId
   * @return Font_TrueType
   */
  function getFont($fontId) {
    if (empty($this->collection)) {
      $this->parse();
    }
    
    $font = new Font_TrueType();
    $font->f = $this->f;
    $font->setTableOffset($this->collection[$fontId]);
    
    return $font;
  }
  
  function current() {
    return $this->collection[$this->position];
  }
  
  function key() {
    return $this->position;
  }
  
  function next() {
    return ++$this->position;
  }
  
  function rewind() {
    $this->position = 0;
  }
  
  function valid() {
    return isset($this->collection[$this->position]);
  }
}
