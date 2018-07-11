//使用这个函数，需要配合close_loading()
function show_loading()
{
	return  layer.load(0,{shade: [0.2,'#000']});
}

//提示信息并关闭
function close_loading(msgs,status)
{
	layer.msg(msgs, {icon: 1,time: 1000},function(){
		if(status == '1'){
			location.reload();
		}
		layer.closeAll();
	});
}
//提示信息
function show_messages(msg,types){
	var types = types ? types : 2;
	layer.msg(msg, {icon:types,time: 1000});
}