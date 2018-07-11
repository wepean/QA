function doSelect(command){
   var objForm = document.getElementsByName('choice[]');
   var objLen = objForm.length;
   if (command=="all") {
      for (var iCount = 0; iCount < objLen; iCount++) {
         if (objForm[iCount].type == "checkbox" && objForm[iCount].name == "choice[]") {
        	objForm[iCount].checked = true;
         }
      }
   } else if (command=="no"){
      for (var iCount = 0; iCount < objLen; iCount++) {
         if (objForm[iCount].type == "checkbox" && objForm[iCount].name == "choice[]") {
            objForm[iCount].checked = false;
         }
      }
   } else{
      for (var iCount = 0; iCount < objLen; iCount++) {
         if (objForm[iCount].type == "checkbox" && objForm[iCount].name == "choice[]") {
            objForm[iCount].checked = !objForm[iCount].checked;
         }
      }
   }
}

/******************************************************************************
	函数名称：			isSelected
	函数作用：			检索checkbox是否有被选择
	传入参数：
	 	obj:			form对象,checkbox对象,图层对象(或ID)均可
	返回值：			Boolean
	调用示列：			isSelected(objId, flag) 
	作者:				姚祖旺
	创建日期：			2007.01.19
	******************************************************************************/
	function isSelected() 
	{ 		
		var objForm = document.getElementsByName('choice[]');
  		var objLen = objForm.length;
   
		for (i = 0; i < objLen; i++) { 			//alert(objLen);
			if (objForm[i].type == "checkbox" && objForm[i].name == "choice[]") { 
				if(objForm[i].checked)
					return true; 
			}
			// End if
		}
		// End for		
		return false; 
	}
	// End function isSelected

function doCommand(command,roof){//alert(command);
	if(isSelected() == false && command!='exportexcel' && command!='lotexportlistens') {
			alert('请选择您要操作的记录！');
			return; 
		}
	else {
        if(command=='lotencryption'){
            if(confirm('确定要批量加密所选择的记录吗！')){
                document.form.action = roof+'/index.php?g=Admin&m=Audio&a=lotaudioencryption&isaudioencryption=1';
                document.form.submit();
            }
        }else if(command=='lotaudioinfo'){
            if(isSelected() != false){
                if(confirm('确定要批量设置音频文件的大小和时长选择的记录吗！')){
                    document.form.action = roof+'/index.php?g=Admin&m=Audio&a=lotaudioinfo&islotaudioinfo=1';
                    document.form.submit();
                }
            }
        }else if(command=='exportexcel'){
            if(isSelected() != false){
                if(confirm('确定要导出所选择的记录到EXCEL吗！')){
                    document.form.action = roof+'/index.php?g=Admin&m=Excelexport&a=audioexport';
                    document.form.submit();
                }
            }else{
                location.href=roof+'/index.php?g=Admin&m=Audio&a=lists&is_param=1&is_export=1';
            }
        }else if(command=='lotexportlistens'){
            if(isSelected() != false){
                if(confirm('确定要导出所选择的记录到EXCEL吗！')){
                    document.form.action = roof+'/index.php?g=Admin&m=Excelexport&a=listenexport';
                    document.form.submit();
                }
            }else{
                location.href=roof+'/index.php?g=Admin&m=Listen&a=lists&is_param=1&is_export=1';
            }
        }else if(command=='lotpublishlisten'){
            document.form.action = roof+'/index.php?g=Admin&m=Listen&a=setting';
            document.form.submit();
        }else if(command!='delall' || confirm('确定要批量删除所选择的记录吗！')){
            createInput(command);
            document.form.submit();
        }
	}
}

//创建表单项
function createInput(value)
{
    var input = document.createElement("input");
    input.type = 'hidden';
	input.name = 'deal';
    input.value = value;
    document.form.appendChild(input);
}