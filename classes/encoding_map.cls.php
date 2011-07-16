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

/* $Id: adobe_font_metrics.cls.php 19 2011-07-16 13:21:12Z fabien.menager $ */

class Encoding_Map {
  private $f;
  
  function __construct($file) {
    $this->f = fopen($file, "r");
  }
  
  function parse(){
    $map = array();
    
    while($line = fgets($this->f)) {
      if (preg_match("/^[\!\=]([0-9A-F]{2,})\s+U\+([0-9A-F]{2})([0-9A-F]{2})\s+([^\s]+)/", $line, $matches)) {
        $unicode = (hexdec($matches[2]) << 8) + hexdec($matches[3]);
        $map[hexdec($matches[1])] = array($unicode, $matches[4]);
      }
    }
    
    ksort($map);
    
    return $map;
  }
}
