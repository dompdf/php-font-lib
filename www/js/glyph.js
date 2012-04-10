var Glyph = {
  ratio: null, 
  head:  null, 
  os2:   null, 
  hmtx:  null, 
  
  midValue: function (a, b){
    return a + (b - a)/2;
  },
  
  splitPath: function(path) {
  	return path.match(/([a-z])|(-?\d+(?:\.\d+)?)/ig);
  },
  
  drawSVGContours: function(ctx, path) {
    var p = Glyph.splitPath(path);
    
    if (!p) {
      return;
    }
    
    var l = p.length;
    var i = 0;
    
    while(i < l) {
      var v = p[i];
      
      switch(v) {
        case "M": 
          ctx.beginPath();
          ctx.moveTo(p[++i], p[++i]); 
          break;
          
        case "L": 
          ctx.lineTo(p[++i], p[++i]); 
          break;
          
        case "Q": 
          ctx.quadraticCurveTo(p[++i], p[++i], p[++i], p[++i]);
          break;
          
        case "z": 
          ctx.closePath(); 
          ctx.fill();
          i++;
          break;
        
        default: 
          i++;
      }
    }
  },
  
  drawHorizLine: function(ctx, y, color) {
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.moveTo(0, y);
    ctx.lineTo(Glyph.width * Glyph.ratio, y);
    ctx.closePath();
    ctx.stroke();
  },
  
  draw: function (canvas, shape, gid) {
    var element  = canvas[0];
    var ctx      = element.getContext("2d");
    var ratio    = Glyph.ratio;
    Glyph.width  = element.width;
    Glyph.height = element.height;
    
    ctx.lineWidth = ratio;
    
    // Invert axis
    ctx.translate(0, Glyph.height);
    ctx.scale(1/ratio, -(1/ratio));
    
    ctx.translate(0, -Glyph.head.yMin);
    
    // baseline
    Glyph.drawHorizLine(ctx, 0, "rgba(0,255,0,0.2)");
    
    // ascender
    Glyph.drawHorizLine(ctx, Glyph.os2.typoAscender, "rgba(255,0,0,0.2)");
    
    // descender
    Glyph.drawHorizLine(ctx, -Math.abs(Glyph.os2.typoDescender), "rgba(255,0,0,0.2)");
    
    ctx.translate(-Glyph.head.xMin, 0);
    
    ctx.save();
      var s = ratio*3;
      
      ctx.strokeStyle = "rgba(0,0,0,0.5)";
      ctx.lineWidth = ratio * 1.5;
      
      // origin
      ctx.beginPath();
      ctx.moveTo(-s, -s);
      ctx.lineTo(+s, +s);
      ctx.moveTo(+s, -s);
      ctx.lineTo(-s, +s);
      ctx.closePath();
      ctx.stroke();
      
      // horizontal advance
      var advance = Glyph.hmtx[gid][0];
      ctx.beginPath();
      ctx.moveTo(-s+advance, -s);
      ctx.lineTo(+s+advance, +s);
      ctx.moveTo(+s+advance, -s);
      ctx.lineTo(-s+advance, +s);
      ctx.closePath();
      ctx.stroke();
    ctx.restore();
    
    if (!shape) {
      return;
    }
    
    // glyph bounding box
    ctx.beginPath();
    ctx.strokeStyle = "rgba(0,0,0,0.3)";
    ctx.rect(0, 0, shape.xMin + shape.xMax, shape.yMin + shape.yMax);
    ctx.closePath();
    ctx.stroke();
    
    ctx.strokeStyle = "black";
    ctx.globalCompositeOperation = "xor";
    
    Glyph.drawSVGContours(ctx, shape.SVGContours);
  }
};