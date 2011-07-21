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
  
  var $tag;
  var $checksum;
  var $offset;
  var $length;
  
  protected $origF;
  
  function __construct(Font_TrueType $font) {
    $this->font = $font;
    $this->f = $font->f;
    $this->tag = $this->read(4);
  }
  
  function load($filename) {
    // void
  }
  
  /**
   * @return Font_TrueType
   */
  function getFont() {
    return $this->font;
  }
  
  function start() {
    $this->seek($this->offset);
  }
  
  function end() {
    //
  }
}

