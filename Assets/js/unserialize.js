/* phpserializer.js - JavaScript to PHP serialize / unserialize class.
 *
 * This class is designed to convert php variables to javascript
 * and javascript variables to php with a php serialize unserialize
 * compatible way.
 *
 * Copyright (C) 2006 Ma Bingyao <andot@ujn.edu.cn>
 * Version: 3.0c
 * LastModified: Jun 2, 2006
 * This library is free.  You can redistribute it and/or modify it.
 * http://www.coolcode.cn/?p=171
 *
 *2011-01-05 likefei edit without encoding
 *ser_string()
 *unser_string()
 *
 *gbk encoding
 *不同的编码中文的长度不同，需要调整一下函数
 *chkLength(strTemp)
 */

function serialize(o) {
  var p = 0,
    sb = [],
    ht = [],
    hv = 1;
  function classname(o) {
    if (typeof o == "undefined" || typeof o.constructor == "undefined")
      return "";
    var c = o.constructor.toString();
    c = utf16to8(
      c.substr(0, c.indexOf("(")).replace(/(^\s*function\s*)|(\s*$)/gi, "")
    );
    return c == "" ? "Object" : c;
  }
  function is_int(n) {
    var s = n.toString(),
      l = s.length;
    if (l > 11) return false;
    for (var i = s.charAt(0) == "-" ? 1 : 0; i < l; i++) {
      switch (s.charAt(i)) {
        case "0":
        case "1":
        case "2":
        case "3":
        case "4":
        case "5":
        case "6":
        case "7":
        case "8":
        case "9":
          break;
        default:
          return false;
      }
    }
    return !(n < -2147483648 || n > 2147483647);
  }
  function in_ht(o) {
    for (k in ht) if (ht[k] === o) return k;
    return false;
  }
  function ser_null() {
    sb[p++] = "N;";
  }
  function ser_boolean(b) {
    sb[p++] = b ? "b:1;" : "b:0;";
  }
  function ser_integer(i) {
    sb[p++] = "i:" + i + ";";
  }
  function ser_double(d) {
    if (d == Number.POSITIVE_INFINITY) d = "INF";
    else if (d == Number.NEGATIVE_INFINITY) d = "-INF";
    sb[p++] = "d:" + d + ";";
  }
  function ser_string(s) {
    //var utf8 = utf16to8(s);
    var utf8 = s; //当判断是中文时不进行编码转换
    sb[p++] = "s:" + chkLength(utf8) + ':"';
    sb[p++] = utf8;
    sb[p++] = '";';
  }
  function ser_array(a) {
    sb[p++] = "a:";
    var lp = p;
    sb[p++] = 0;
    sb[p++] = ":{";
    for (var k in a) {
      if (typeof a[k] != "function") {
        is_int(k) ? ser_integer(k) : ser_string(k);
        __serialize(a[k]);
        sb[lp]++;
      }
    }
    sb[p++] = "}";
  }
  function ser_object(o) {
    var cn = classname(o);
    if (cn == "") ser_null();
    else if (typeof o.serialize != "function") {
      sb[p++] = "O:" + cn.length + ':"';
      sb[p++] = cn;
      sb[p++] = '":';
      var lp = p;
      sb[p++] = 0;
      sb[p++] = ":{";
      if (typeof o.__sleep == "function") {
        var a = o.__sleep();
        for (var kk in a) {
          ser_string(a[kk]);
          __serialize(o[a[kk]]);
          sb[lp]++;
        }
      } else {
        for (var k in o) {
          if (typeof o[k] != "function") {
            ser_string(k);
            __serialize(o[k]);
            sb[lp]++;
          }
        }
      }
      sb[p++] = "}";
    } else {
      var cs = o.serialize();
      sb[p++] = "C:" + cn.length + ':"';
      sb[p++] = cn;
      sb[p++] = '":' + cs.length + ":{";
      sb[p++] = cs;
      sb[p++] = "}";
    }
  }
  function ser_pointref(R) {
    sb[p++] = "R:" + R + ";";
  }
  function ser_ref(r) {
    sb[p++] = "r:" + r + ";";
  }
  function __serialize(o) {
    if (o == null || o.constructor == Function) {
      hv++;
      ser_null();
    } else
      switch (o.constructor) {
        case Boolean: {
          hv++;
          ser_boolean(o);
          break;
        }
        case Number: {
          hv++;
          is_int(o) ? ser_integer(o) : ser_double(o);
          break;
        }
        case String: {
          hv++;
          ser_string(o);
          break;
        }
        case Array: {
          var r = in_ht(o);
          if (r) {
            ser_pointref(r);
          } else {
            ht[hv++] = o;
            ser_array(o);
          }
          break;
        }
        default: {
          var r = in_ht(o);
          if (r) {
            hv++;
            ser_ref(r);
          } else {
            ht[hv++] = o;
            ser_object(o);
          }
          break;
        }
      }
  }
  __serialize(o);
  return sb.join("");
}

function unserialize(ss) {
  var p = 0,
    ht = [],
    hv = 1;
  r = null;
  function unser_null() {
    p++;
    return null;
  }
  function unser_boolean() {
    p++;
    var b = ss.charAt(p++) == "1";
    p++;
    return b;
  }
  function unser_integer() {
    p++;
    var i = parseInt(ss.substring(p, (p = ss.indexOf(";", p))));
    p++;
    return i;
  }
  function unser_double() {
    p++;
    var d = ss.substring(p, (p = ss.indexOf(";", p)));
    switch (d) {
      case "INF":
        d = Number.POSITIVE_INFINITY;
        break;
      case "-INF":
        d = Number.NEGATIVE_INFINITY;
        break;
      default:
        d = parseFloat(d);
    }
    p++;
    return d;
  }
  function unser_string() {
    p++;
    var l = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    //var s = utf8to16(ss.substring(p, p += l));
    //var s = ss.substring(p, p += l);
    var s = subChnStr(ss, l, p);
    p += s.length;
    p += 2;
    return s;
  }
  function unser_array() {
    p++;
    var n = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    var a = [];
    ht[hv++] = a;
    for (var i = 0; i < n; i++) {
      var k;
      switch (ss.charAt(p++)) {
        case "i":
          k = unser_integer();
          break;
        case "s":
          k = unser_string();
          break;
        case "U":
          k = unser_unicode_string();
          break;
        default:
          return false;
      }
      a[k] = __unserialize();
    }
    p++;
    return a;
  }
  function unser_object() {
    p++;
    var l = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    var cn = utf8to16(ss.substring(p, (p += l)));
    p += 2;
    var n = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    if (eval(["typeof(", cn, ') == "undefined"'].join(""))) {
      eval(["function ", cn, "(){}"].join(""));
    }
    var o = eval(["new ", cn, "()"].join(""));
    ht[hv++] = o;
    for (var i = 0; i < n; i++) {
      var k;
      switch (ss.charAt(p++)) {
        case "s":
          k = unser_string();
          break;
        case "U":
          k = unser_unicode_string();
          break;
        default:
          return false;
      }
      if (k.charAt(0) == "\0") {
        k = k.substring(k.indexOf("\0", 1) + 1, k.length);
      }
      o[k] = __unserialize();
    }
    p++;
    if (typeof o.__wakeup == "function") o.__wakeup();
    return o;
  }
  function unser_custom_object() {
    p++;
    var l = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    var cn = utf8to16(ss.substring(p, (p += l)));
    p += 2;
    var n = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    if (eval(["typeof(", cn, ') == "undefined"'].join(""))) {
      eval(["function ", cn, "(){}"].join(""));
    }
    var o = eval(["new ", cn, "()"].join(""));
    ht[hv++] = o;
    if (typeof o.unserialize != "function") p += n;
    else o.unserialize(ss.substring(p, (p += n)));
    p++;
    return o;
  }
  function unser_unicode_string() {
    p++;
    var l = parseInt(ss.substring(p, (p = ss.indexOf(":", p))));
    p += 2;
    var sb = [];
    for (i = 0; i < l; i++) {
      if ((sb[i] = ss.charAt(p++)) == "\\") {
        sb[i] = String.fromCharCode(parseInt(ss.substring(p, (p += 4)), 16));
      }
    }
    p += 2;
    return sb.join("");
  }
  function unser_ref() {
    p++;
    var r = parseInt(ss.substring(p, (p = ss.indexOf(";", p))));
    p++;
    return ht;
  }
  function __unserialize() {
    switch (ss.charAt(p++)) {
      case "N":
        return (ht[hv++] = unser_null());
      case "b":
        return (ht[hv++] = unser_boolean());
      case "i":
        return (ht[hv++] = unser_integer());
      case "d":
        return (ht[hv++] = unser_double());
      case "s":
        return (ht[hv++] = unser_string());
      case "U":
        return (ht[hv++] = unser_unicode_string());
      case "r":
        return (ht[hv++] = unser_ref());
      case "a":
        return unser_array();
      case "O":
        return unser_object();
      case "C":
        return unser_custom_object();
      case "R":
        return unser_ref();
      default:
        return false;
    }
  }
  return __unserialize();
}

//gbk encoding下的中文字符长度
function chkLength(strTemp) {
  var i, sum;
  sum = 0;
  for (i = 0; i < strTemp.length; i++) {
    if (strTemp.charCodeAt(i) >= 0 && strTemp.charCodeAt(i) <= 255)
      sum = sum + 1;
    else sum = sum + 2;
  }
  return sum;
}
//gbk encoding中文字符截取（中文占两个字符）
//utf8 encoding中文字符截取（中文占三个字符）
//做的改动：增加截取字符串的开始位置
function subChnStr(str, len, start, hasDot) {
  var newLength = 0;
  var newStr = "";
  var chineseRegex = /[^\x00-\xff]/g;
  var singleChar = "";
  var strLength = str.replace(chineseRegex, "**").length;

  for (var i = start; i < strLength; i++) {
    singleChar = str.charAt(i).toString();
    if (singleChar.match(chineseRegex) != null) {
      newLength += 2;
    } else {
      newLength++;
    }
    if (newLength > len) {
      break;
    }
    newStr += singleChar;
  }

  if (hasDot && strLength > len) {
    newStr += "...";
  }
  return newStr;
}
