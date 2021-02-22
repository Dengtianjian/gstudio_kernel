function unserialize(ss) {
  var p = 0, ht = [], hv = 1; r = null;
  function unser_null() {
      p++;
      return null;
  }
  function unser_boolean() {
      p++;
      var b = (ss.charAt(p++) == '1');
      p++;
      return b;
  }
  function unser_integer() {
      p++;
      var i = parseInt(ss.substring(p, p = ss.indexOf(';', p)));
      p++;
      return i;
  }
  function unser_double() {
      p++;
      var d = ss.substring(p, p = ss.indexOf(';', p));
      switch (d) {
          case 'INF': d = Number.POSITIVE_INFINITY; break;
          case '-INF': d = Number.NEGATIVE_INFINITY; break;
          default: d = parseFloat(d);
      }
      p++;
      return d;
  }
  function unser_string() {
      p++;
      var l = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      //var s = utf8to16(ss.substring(p, p += l));
      //var s = ss.substring(p, p += l);
      var s = subChnStr(ss,l,p);
      p += s.length;
      p += 2;
      return s;
  }
  function unser_array() {
      p++;
      var n = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      var a = [];
      ht[hv++] = a;
      for (var i = 0; i < n; i++) {
          var k;
          switch (ss.charAt(p++)) {
              case 'i': k = unser_integer(); break;
              case 's': k = unser_string(); break;
              case 'U': k = unser_unicode_string(); break;
              default: return false;
          }
          a[k] = __unserialize();
      }
      p++;
      return a;
  }
  function unser_object() {
      p++;
      var l = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      var cn = utf8to16(ss.substring(p, p += l));
      p += 2;
      var n = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      if (eval(['typeof(', cn, ') == "undefined"'].join(''))) {
          eval(['function ', cn, '(){}'].join(''));
      }
      var o = eval(['new ', cn, '()'].join(''));
      ht[hv++] = o;
      for (var i = 0; i < n; i++) {
          var k;
          switch (ss.charAt(p++)) {
              case 's': k = unser_string(); break;
              case 'U': k = unser_unicode_string(); break;
              default: return false;
          }
          if (k.charAt(0) == '\0') {
              k = k.substring(k.indexOf('\0', 1) + 1, k.length);
          }
          o[k] = __unserialize();
      }
      p++;
      if (typeof(o.__wakeup) == 'function') o.__wakeup();
      return o;
  }
  function unser_custom_object() {
      p++;
      var l = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      var cn = utf8to16(ss.substring(p, p += l));
      p += 2;
      var n = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      if (eval(['typeof(', cn, ') == "undefined"'].join(''))) {
          eval(['function ', cn, '(){}'].join(''));
      }
      var o = eval(['new ', cn, '()'].join(''));
      ht[hv++] = o;
      if (typeof(o.unserialize) != 'function') p += n;
      else o.unserialize(ss.substring(p, p += n));
      p++;
      return o;
  }
  function unser_unicode_string() {
      p++;
      var l = parseInt(ss.substring(p, p = ss.indexOf(':', p)));
      p += 2;
      var sb = [];
      for (i = 0; i < l; i++) {
          if ((sb[i] = ss.charAt(p++)) == '\\') {
              sb[i] = String.fromCharCode(parseInt(ss.substring(p, p += 4), 16));
          }
      }
      p += 2;
      return sb.join('');
  }
  function unser_ref() {
      p++;
      var r = parseInt(ss.substring(p, p = ss.indexOf(';', p)));
      p++;
      return ht
­;
  }
  function __unserialize() {
      switch (ss.charAt(p++)) {
          case 'N': return ht[hv++] = unser_null();
          case 'b': return ht[hv++] = unser_boolean();
          case 'i': return ht[hv++] = unser_integer();
          case 'd': return ht[hv++] = unser_double();
          case 's': return ht[hv++] = unser_string();
          case 'U': return ht[hv++] = unser_unicode_string();
          case 'r': return ht[hv++] = unser_ref();
          case 'a': return unser_array();
          case 'O': return unser_object();
          case 'C': return unser_custom_object();
          case 'R': return unser_ref();
          default: return false;
      }
  }
  return __unserialize();
}

//gbk encoding下的中文字符长度
function chkLength(strTemp)
{
  var i,sum;
  sum=0;
  for(i=0;i<strTemp.length;i++)
  {
      if ((strTemp.charCodeAt(i)>=0) && (strTemp.charCodeAt(i)<=255))
          sum=sum+1;
      else
          sum=sum+2;
  }
  return sum;
}
//gbk encoding中文字符截取（中文占两个字符）
//utf8 encoding中文字符截取（中文占三个字符）
//做的改动：增加截取字符串的开始位置
function subChnStr(str, len, start, hasDot)
{
  var newLength = 0;
  var newStr = "";
  var chineseRegex = /[^\x00-\xff]/g;
  var singleChar = "";
  var strLength = str.replace(chineseRegex,"**").length;

  for(var i = start;i < strLength;i++)
  {
      singleChar = str.charAt(i).toString();
      if(singleChar.match(chineseRegex) != null)
      {
          newLength += 2;
      }
      else
      {
          newLength++;
      }
      if(newLength > len)
      {
          break;
      }
      newStr += singleChar;
  }

  if(hasDot && strLength > len)
  {
      newStr += "...";
  }
  return newStr;
}