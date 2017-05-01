/*sign_key为签到码关键信息（当前为后台生成的10位随机数） num为签到人数，page_id为当前网页ID
add_time为服务器时间与本地时间差（即本地毫秒级时间加add_time为服务器时间，取三次平均值，单位为毫秒）
四者均记录在cookie中，仅仅刷新网页信息还在（关闭浏览器清除）。*/
//main();
function main()//主程序入口
{
	/*if(window.screen.height>window.screen.width)//移动端访问
	{
		document.getElementById('boxDom').innerHTML='';
		document.getElementById('block').innerHTML='<br/>( ✘_✘ )访问被拒绝，请使用电脑端访问<br/><a href="http://webqr.dream.ren">http://webqr.dream.ren</a>';
		document.title = '( ✘_✘ )拒绝访问';
        alert('( ✘_✘ )访问被拒绝，请使用电脑端访问http://webqr.dream.ren');
    }
    else
    {*/
        if(!-[1,])//若为IE浏览器
        {
            document.getElementById('boxDom').innerHTML='';
            document.getElementById('block').innerHTML='<br/><br/>( ✘_✘ )真是抱歉，您的浏览器被歧视了！<br/>IE浏览器可能出现不兼容问题，请使用其他现代浏览器访问！';
            document.title = '( ✘_✘ )浏览器被歧视';
            alert("IE浏览器可能出现不兼容问题，请使用其他现代浏览器访问！谢谢配合");
        }
        sign_key=getCookie('sign_key')
    	num=getCookie('num')
	    add_time=getCookie('add_time')
	    fullScreen();
	    if (sign_key!=null && sign_key!=""&&add_time!=null && add_time!=""&&!isNaN(add_time))
	      {signqr()}
	    else 
	      {login()}
    //}
}
//http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?flag=Mg==&createstamp=1478693003&updatestamp=1478693164&group=%E4%B9%9D%E7%8F%AD&openid=b2JrSTF3ZjQyZHNzeEUweUVaRUZWbVI3aExuOA== 
/*无cookie扫码登录函数，其中发送登录消息需调用:
http://websocket.dream.ren:1996/?type=publish&content=消息内容(此处为sign_key)&to=登录的ID(此处为浏览器时间),
&to=xxx不写为向所有websocket连接发送消息，故此端口号需严格保密。*/
function login(){
	sendmessage('请登录');//发送弹幕
	document.title = '扫码登录';
    setbg();//设置背景
    page_id=getCookie('page_id');
    if(page_id==''||!page_id)
    {
        page_id=(new Date()).getTime()-0;
        setCookie('page_id',page_id);
    }
	//time=parseInt((new Date()).getTime()/1000)-0;
	addtime();//获取时间差
	//obj = document.getElementById('msg');
	//obj.innerHTML = obj.innerHTML+page_id;
	$('#qr').qrcode({correctLevel:1, text: 'http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?login='+page_id});//+time方便调试
	var socket = io('http://websocket.dream.ren:8081');
	socket.on('connect', function(){
		socket.emit('login', page_id);/*time当ID，用于标示当前网页，服务器推送消息时需要使用，
ID貌似需要为数字，好坑啊。sign_key若非纯数字则需要修改判断语句代码，可在二维码str后面加上类似于‘&to=ID’的标示*/
	});
	socket.on('new_msg', function(msg){
		if (msg!=null && msg!=""&&isNaN(msg))
		{
			//var msg="";
            //var msg='ZmxhZz0yJmNyZWF0ZXN0YW1wPTE0Nzg2OTMwMDMmZ3JvdXA9SlVVMEpVSTVKVGxFSlVVM0pUaEdKVUZFJm9wZW5pZD1iMkpyU1RGM1pqUXlaSE56ZUVVd2VVVmFSVVpXYlZJM2FFeHVPQT09DQo=';
			msg=Base64.decode(msg);
            //alert(msg);
			//arr=getvalue(msg);
			//msg='flag='+Base64.encode(arr['flag'])+'&createstamp='+arr['time']+'&group='+arr['group']
			setCookie('sign_key',msg);
			sign_key=msg;
			var login_info = document.getElementById('login_info');
			login_info.innerHTML = '登录成功';
			sendmessage('登录成功');
			socket.close();
			signqr();
		}
	});
}
function signqr()//登录后展示动态码的的主函数
{
    $("#login_box").css("height","500");//缩小二维码框高度
    obj = document.getElementById('msg');
	document.body.style.backgroundColor="#ccc";//#c1c8cd
	document.title = '动态防拍安全签到二维码';
    setbg();
	//if(sign_key==null||sign_key==""||isNaN(sign_key))
	sign_key=getCookie('sign_key');
    //alert(sign_key);
	page_id=getCookie('page_id');
	num=getCookie('num');
	if(num==null || num=="")
		{num=0;}
	time=parseInt((((new Date()).getTime()-0)+(getCookie('add_time')-0))/1000);//服务器时间
	obj.innerHTML = '<h4>以上二维码即为签到码</h4>当前共有'+num+'人通过webqr签到！<br/>';//+time方便调试
	//str=Base64.encode('k='+sign_key+'&t='+time);
	str=sign_key+'&updatestamp='+time+'&page_id='+page_id;
    //str="";
	$('#qr').empty();
	$('#qr').qrcode({correctLevel:1, text: 'http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?'+str});
	setInterval("changeqr()",2000);/*时间同步已经比较准确，此处时间不考虑用户体验的情况下可无限缩短（越短越好），后台可以将有效期
设置为2秒甚至1秒，若需考虑扫码时手机的反应时间和手机2G网络和微信服务器的延迟可将add_time加上手机反应和延迟的毫秒数或延长有效期*/
	socket = io('http://websocket.dream.ren:8081');
	socket.on('connect', function(){
		socket.emit('login', page_id);
	});
	socket.on('new_msg', function(msg){
		msg=msg.replace(/&lt;/g,"<");
		msg=msg.replace(/&gt;/g,">");
		if(msg!=null && msg!=""&&isNaN(msg))
		{
            if(getCookie('num')==0)
            {
                num++;
                sendmessage('组长自动签到成功！');
                document.getElementById('showman').innerHTML='当前签到<br/>组长<br/>';
            }
			sendmessage(msg+'签到成功！');
			document.getElementById('showman').innerHTML+=msg+'<br/>';
			num++;
			obj.innerHTML = '<h4>以上二维码即为签到码</h4>当前共有'+num+'人通过webqr签到！<br/>';
			setCookie('num',num);
		}
	});
}

function getvalue(str)//get式参数转数组
{
	var theRequest = new Object();
	if(str.indexOf("?")==-1)
	{
		str='?'+str;
	}
    var s = str.substr(1);
    strs = s.split("&");
    for(var i=0;i<strs.length;i++)
    {
        var sTemp = strs[i].split("=");
        theRequest[sTemp[0]]=(sTemp[1]);
    }
    return theRequest;
}

function changeqr()//修改二维码内容
{
    obj = document.getElementById('msg');
	$('#login_info').empty();
	$('#qr').empty();
	time=parseInt((((new Date()).getTime()-0)+(getCookie('add_time')-0))/1000);
	//str=Base64.encode("k="+sign_key+"&t="+time);
	//str="";
    str=sign_key+'&updatestamp='+time+'&page_id='+page_id;
    //alert(str);
	$('#qr').qrcode({correctLevel:1, text: 'http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?'+str});
	obj.innerHTML = '<h4>以上二维码即为签到码</h4>当前共有'+num+'人通过webqr签到！<br/>'
}


function setbg()//设置随机毛玻璃背景
{
    var m=parseInt((new Date()).getMilliseconds()%33+1);//根据时间随机取数
    var config="url('image/bg/"+m+".jpg')  center center no-repeat";
    //alert(config);
    $("#blur").css("background",config);
    $("#blur").css("background-size","100% 100%");
}


function addtime()//获取时间差的调用函数，获取公众号服务端和浏览器端时间差并记录在cookie中
{
    i=0;
    arr=new Array(3);
    total=0;
    get=setInterval("gettime()",500);
}
function gettime()//获取时间差
{
    var localtime=(new Date()).getTime();
    $.ajax({
    type:"get",
    //async: false,
    url:'http://zzc.dream.ren/wechat/signin/time.php?callback=?',//见目录time.php文件，必须放在公众号服务器端。
    dataType:"jsonp",
    jsonp:"jsoncallback",
    jsonpCallback:'json',
    success:function(jsonp){
        if(i>=0&&i<=2)
        {
            arr[i]=jsonp.time-localtime;
            total+=arr[i];
            temp=parseInt(total/(i+1));
            setCookie('add_time', temp);
            i++;
            if(i==3)
                {clearInterval(get);}
        }
        
    },
    error : function(){
        }
    });
}
function getCookie(c_name)//W3C demo，获取cookie
{
    if (document.cookie.length>0)
      {
      c_start=document.cookie.indexOf(c_name + "=")
      if (c_start!=-1)
        { 
        c_start=c_start + c_name.length+1 
        c_end=document.cookie.indexOf(";",c_start)
        if (c_end==-1) c_end=document.cookie.length
        return unescape(document.cookie.substring(c_start,c_end))
        } 
      }
    return ""
}
function setCookie(c_name,value,expiredays)//设置cookie
{
    var exdate=new Date()
    exdate.setDate(exdate.getDate()+expiredays)
    document.cookie=c_name+ "=" +escape(value)+
    ((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}


function sendmessage(text)//发送弹幕
{
	var pageW=parseInt($(document).width());
    var pageH=parseInt($(document).height());
    var boxDom=$("#boxDom");
    var creSpan=$("<span class='string'></span>");
    creSpan.text(text);
    Top=parseInt(pageH*(Math.random()));
    if(Top>pageH-90){
    	Top=pageH-90;
    }
    creSpan.css({"top":Top,"right":-300,"color":randomcolor()});//弹幕样式
    boxDom.append(creSpan);
    var spanDom=$("#boxDom>span:last-child");
    spanDom.stop().animate({"right":pageW+300},10000,"linear",function(){
    	$(this).remove();
    });
}
function getRandomColor()//随机rgb颜色，弹幕函数原来调用这个
{
	return '#' + (function(h){
		return new Array(7 - h.length).join("0") + h
	})((Math.random() * 0x1000000 << 0).toString(16));
}
function arrcolor()//随机自定义颜色
{
    var colorArr=["#cfaf12","#12af01","#981234","#adefsa","#db6be4","#f5264c","#d34a74"];
    return colorArr[Math.floor(Math.random()*colorArr.length)];
}
function randomcolor()//随机hsl颜色，随机rgb颜色的改进版，颜色更鲜艳。
{
    var h=Math.floor(Math.random()*360);
    return "hsl("+h+",100%,50%)";
}


function fullScreen()//全屏功能
{
	var viewFullScreen = document.getElementById("view-fullscreen");
	var cancelFullScreen = document.getElementById("cancel-fullscreen");
	viewFullScreen.innerHTML ='全屏展示';
    if (viewFullScreen) {
        viewFullScreen.addEventListener("click", function () {
            var docElm = document.documentElement;
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            }
            else if (docElm.msRequestFullscreen) {
                docElm.msRequestFullscreen();
            }
            else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            }
            else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            }
            viewFullScreen.innerHTML ='';
            cancelFullScreen.innerHTML ='<a href="#">退出全屏</a>';
            sendmessage('按"ESC"键可退出全屏！')
        }, false);
    }
    var cancelFullScreen = document.getElementById("cancel-fullscreen");
    if (cancelFullScreen) {
        cancelFullScreen.addEventListener("click", function () {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
            else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            }
            else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            }
            viewFullScreen.innerHTML ='全屏展示';
            cancelFullScreen.innerHTML ='';
            sendmessage('')
        }, false);
    }
}


//下面仅仅是base64拓展函数
(function(global) {
    'use strict';
    var _Base64 = global.Base64;
    var version = "2.1.9";
    var buffer;
    if (typeof module !== 'undefined' && module.exports) {
        try {
            buffer = require('buffer').Buffer;
        } catch (err) {}
    }
    var b64chars
        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    var b64tab = function(bin) {
        var t = {};
        for (var i = 0, l = bin.length; i < l; i++) t[bin.charAt(i)] = i;
        return t;
    }(b64chars);
    var fromCharCode = String.fromCharCode;
    var cb_utob = function(c) {
        if (c.length < 2) {
            var cc = c.charCodeAt(0);
            return cc < 0x80 ? c
                : cc < 0x800 ? (fromCharCode(0xc0 | (cc >>> 6))
                                + fromCharCode(0x80 | (cc & 0x3f)))
                : (fromCharCode(0xe0 | ((cc >>> 12) & 0x0f))
                   + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                   + fromCharCode(0x80 | ( cc         & 0x3f)));
        } else {
            var cc = 0x10000
                + (c.charCodeAt(0) - 0xD800) * 0x400
                + (c.charCodeAt(1) - 0xDC00);
            return (fromCharCode(0xf0 | ((cc >>> 18) & 0x07))
                    + fromCharCode(0x80 | ((cc >>> 12) & 0x3f))
                    + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                    + fromCharCode(0x80 | ( cc         & 0x3f)));
        }
    };
    var re_utob = /[\uD800-\uDBFF][\uDC00-\uDFFFF]|[^\x00-\x7F]/g;
    var utob = function(u) {
        return u.replace(re_utob, cb_utob);
    };
    var cb_encode = function(ccc) {
        var padlen = [0, 2, 1][ccc.length % 3],
        ord = ccc.charCodeAt(0) << 16
            | ((ccc.length > 1 ? ccc.charCodeAt(1) : 0) << 8)
            | ((ccc.length > 2 ? ccc.charCodeAt(2) : 0)),
        chars = [
            b64chars.charAt( ord >>> 18),
            b64chars.charAt((ord >>> 12) & 63),
            padlen >= 2 ? '=' : b64chars.charAt((ord >>> 6) & 63),
            padlen >= 1 ? '=' : b64chars.charAt(ord & 63)
        ];
        return chars.join('');
    };
    var btoa = global.btoa ? function(b) {
        return global.btoa(b);
    } : function(b) {
        return b.replace(/[\s\S]{1,3}/g, cb_encode);
    };
    var _encode = buffer ? function (u) {
        return (u.constructor === buffer.constructor ? u : new buffer(u))
        .toString('base64')
    }
    : function (u) { return btoa(utob(u)) }
    ;
    var encode = function(u, urisafe) {
        return !urisafe
            ? _encode(String(u))
            : _encode(String(u)).replace(/[+\/]/g, function(m0) {
                return m0 == '+' ? '-' : '_';
            }).replace(/=/g, '');
    };
    var encodeURI = function(u) { return encode(u, true) };
    var re_btou = new RegExp([
        '[\xC0-\xDF][\x80-\xBF]',
        '[\xE0-\xEF][\x80-\xBF]{2}',
        '[\xF0-\xF7][\x80-\xBF]{3}'
    ].join('|'), 'g');
    var cb_btou = function(cccc) {
        switch(cccc.length) {
        case 4:
            var cp = ((0x07 & cccc.charCodeAt(0)) << 18)
                |    ((0x3f & cccc.charCodeAt(1)) << 12)
                |    ((0x3f & cccc.charCodeAt(2)) <<  6)
                |     (0x3f & cccc.charCodeAt(3)),
            offset = cp - 0x10000;
            return (fromCharCode((offset  >>> 10) + 0xD800)
                    + fromCharCode((offset & 0x3FF) + 0xDC00));
        case 3:
            return fromCharCode(
                ((0x0f & cccc.charCodeAt(0)) << 12)
                    | ((0x3f & cccc.charCodeAt(1)) << 6)
                    |  (0x3f & cccc.charCodeAt(2))
            );
        default:
            return  fromCharCode(
                ((0x1f & cccc.charCodeAt(0)) << 6)
                    |  (0x3f & cccc.charCodeAt(1))
            );
        }
    };
    var btou = function(b) {
        return b.replace(re_btou, cb_btou);
    };
    var cb_decode = function(cccc) {
        var len = cccc.length,
        padlen = len % 4,
        n = (len > 0 ? b64tab[cccc.charAt(0)] << 18 : 0)
            | (len > 1 ? b64tab[cccc.charAt(1)] << 12 : 0)
            | (len > 2 ? b64tab[cccc.charAt(2)] <<  6 : 0)
            | (len > 3 ? b64tab[cccc.charAt(3)]       : 0),
        chars = [
            fromCharCode( n >>> 16),
            fromCharCode((n >>>  8) & 0xff),
            fromCharCode( n         & 0xff)
        ];
        chars.length -= [0, 0, 2, 1][padlen];
        return chars.join('');
    };
    var atob = global.atob ? function(a) {
        return global.atob(a);
    } : function(a){
        return a.replace(/[\s\S]{1,4}/g, cb_decode);
    };
    var _decode = buffer ? function(a) {
        return (a.constructor === buffer.constructor
                ? a : new buffer(a, 'base64')).toString();
    }
    : function(a) { return btou(atob(a)) };
    var decode = function(a){
        return _decode(
            String(a).replace(/[-_]/g, function(m0) { return m0 == '-' ? '+' : '/' })
                .replace(/[^A-Za-z0-9\+\/]/g, '')
        );
    };
    var noConflict = function() {
        var Base64 = global.Base64;
        global.Base64 = _Base64;
        return Base64;
    };
    global.Base64 = {
        VERSION: version,
        atob: atob,
        btoa: btoa,
        fromBase64: decode,
        toBase64: encode,
        utob: utob,
        encode: encode,
        encodeURI: encodeURI,
        btou: btou,
        decode: decode,
        noConflict: noConflict
    };
    if (typeof Object.defineProperty === 'function') {
        var noEnum = function(v){
            return {value:v,enumerable:false,writable:true,configurable:true};
        };
        global.Base64.extendString = function () {
            Object.defineProperty(
                String.prototype, 'fromBase64', noEnum(function () {
                    return decode(this)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64', noEnum(function (urisafe) {
                    return encode(this, urisafe)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64URI', noEnum(function () {
                    return encode(this, true)
                }));
        };
    }
    if (global['Meteor']) {
       Base64 = global.Base64; 
    }
})(this);