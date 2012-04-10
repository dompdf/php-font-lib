var Glyph = {
  ratio: null, 
  head:  null, 
  os2:   null, 
  hmtx:  null, 
  
  midValue: function (a, b){
    return a + (b - a)/2;
  },
  
  addContourToPath: function(ctx, points, startIndex, count) {
    ctx.beginPath();
    
    var offset = 0;
    
    while(offset < count) {
      var point_m1 = points[ (offset == 0) ? (startIndex+count-1) : startIndex+(offset-1)%count ];
      var point    = points[ startIndex + offset%count ];
      var point_p1 = points[ startIndex + (offset+1)%count ];
      var point_p2 = points[ startIndex + (offset+2)%count ];
      
      if(offset == 0) {
        ctx.moveTo(point.x, point.y);
      }
      
      if (point.onCurve && point_p1.onCurve) {
        ctx.lineTo( point_p1.x, point_p1.y );
        offset++;
      } 
      else if (point.onCurve && !point_p1.onCurve && point_p2.onCurve){
        ctx.quadraticCurveTo(point_p1.x, point_p1.y, point_p2.x, point_p2.y);
        offset += 2;
      } 
      else if (point.onCurve && !point_p1.onCurve && !point_p2.onCurve){
        ctx.quadraticCurveTo(point_p1.x, point_p1.y, Glyph.midValue(point_p1.x, point_p2.x), Glyph.midValue(point_p1.y, point_p2.y));
        offset += 2;
      } 
      else if (!point.onCurve && !point_p1.onCurve) {
        ctx.quadraticCurveTo(point.x, point.y, Glyph.midValue(point.x, point_p1.x), Glyph.midValue(point.y, point_p1.y));
        offset++;
      } 
      else if (!point.onCurve && point_p1.onCurve) {
        ctx.quadraticCurveTo(point.x, point.y, point_p1.x, point_p1.y);
        offset++;
      } 
      else {
        console.error("error");
        break;
      }
    }
    
    ctx.closePath();
    ctx.fill();
  },
  
  draw: function (canvas, shape, gid) {
    var element = canvas[0];
    var ctx    = element.getContext("2d");
    var width  = element.width;
    var height = element.height;
    var ratio  = Glyph.ratio;
    
    ctx.lineWidth = ratio;
    
    // Invert axis
    ctx.translate(0, height);
    ctx.scale(1/ratio, -(1/ratio));
    
    ctx.translate(0, -Glyph.head.yMin);
    
    // baseline
    ctx.beginPath();
    ctx.strokeStyle = "rgba(0,255,0,0.2)";
    ctx.moveTo(0,             0);
    ctx.lineTo(width * ratio, 0);
    ctx.closePath();
    ctx.stroke();
    
    // ascender
    ctx.beginPath();
    ctx.strokeStyle = "rgba(255,0,0,0.2)";
    ctx.moveTo(0,             Glyph.os2.typoAscender);
    ctx.lineTo(width * ratio, Glyph.os2.typoAscender);
    ctx.closePath();
    ctx.stroke();
    
    // descender
    ctx.beginPath();
    ctx.strokeStyle = "rgba(255,0,0,0.2)";
    ctx.moveTo(0,             -Math.abs(Glyph.os2.typoDescender));
    ctx.lineTo(width * ratio, -Math.abs(Glyph.os2.typoDescender));
    ctx.closePath();
    ctx.stroke();
    
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
    
    // glyph bounding box
    ctx.beginPath();
    ctx.strokeStyle = "rgba(0,0,0,0.3)";
    ctx.rect(0, 0, shape.xMin + shape.xMax, shape.yMin + shape.yMax);
    ctx.closePath();
    ctx.stroke();
    
    ctx.strokeStyle = "black";
    ctx.globalCompositeOperation = "xor";
    
    var points = shape.points;
    var length = points.length;
    var firstIndex = 0;
    var count      = 0;
    
    for (var i = 0; i < length; i++) {
      count++;
      
      if (points[i].endOfContour) {
        Glyph.addContourToPath(ctx, points, firstIndex, count);
        firstIndex = i + 1;
        count = 0;
      }
    }
  }
};