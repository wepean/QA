function InitAjax()
{
　var ajax=false;
　try {
　　ajax = new ActiveXObject("Msxml2.XMLHTTP");
　} catch (e) {
　　try {
　　　ajax = new ActiveXObject("Microsoft.XMLHTTP");
　　} catch (E) {
　　　ajax = false;
　　}
　}
　if (!ajax && typeof XMLHttpRequest!='undefined') {
　　ajax = new XMLHttpRequest();
　}
　return ajax;
}

function getinfo(id,url,flag)
{
　var show = document.getElementById(id);
　var ajax = InitAjax();

　ajax.open("GET", url, true); 

　ajax.onreadystatechange = function() { 

    
　　if (ajax.readyState == 4 && ajax.status == 200) {
　　　if(ajax.responseText == 'ref') location.reload();
      else if(flag == 1) return 1;
      else if(flag == 2)　alert(ajax.responseText);
      else if(flag == 3)　{
            if(ajax.responseText == -1) location =window.location.href;//location.reload();
            else if(ajax.responseText >= 1){
                alert('该听书还未录入音频，不能发布！');
                show = document.getElementById('is_pub_'+ajax.responseText);
                show.checked = false;
            }else
                return false;

        }
      else show.innerHTML = ajax.responseText;

　　} 
	//else{
    //        show.innerHTML = " connection error!"; 
    //    }
　}
　　ajax.send(null); 
}


/*******************************************************************************
 函数名: 				getDatas
 函数作用: 				加载数据
 传入参数: 
		obj:			绑定数据的对象或对象ID
		url:			请求地址(不含参数)
		param:			提交的参数
		method:			提交方法
 返回值: 				无
 调用示例: 				getDatas(obj, url, param, method) 
*******************************************************************************/
function getDatas(obj, url, param, method) 
{ 
	method 			= (typeof method == 'undefined') ? 'get' : method; 
	url 			= (typeof url == 'undefined') ? 'ingress/index.php' : url;
	var myAjax		= new Ajax.Updater(obj, url, {method: method, parameters: param, evalScripts: true}); 
	myAjax			= null; 
}
// End function getDatas 


function getContents(obj, action, param, isFront, module, url,method) 
{ 
	param			=  (typeof param == 'undefined') ? '' : param;
	isFront			=  (typeof isFront== 'undefined') ? '1' : isFront;
	module			=  (typeof module == 'undefined') ? 'front' : module;
	param			=  "module="+module+"&action="+action+param+"&isFront="+isFront;
	getDatas(obj, url, param, method) ;
}