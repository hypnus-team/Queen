
function new_drone_alias(cid){
	var new_alias = document.getElementById('new_alias').value;
	if (!new_alias){
		new_alias = " ";
	}
	post_draw("./cpanel.php","multi_panel","act=client_panel&new_alias="+new_alias+"&cid="+cid);
}

function mod_manage(cid){
	JsLoader('00010000000000000000000000000000','default'); //custom Mod need upload 
    post_draw("./mod_manage.php","mod_panel","cid="+cid);
}

function mod_install(tid,cid,mid,os,dom){
	if (null == tid){	
		if (document.getElementById(dom)){
			document.getElementById(dom).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";
		}
		var getdata = "nb=1&mid=00010000000000000000000000000000&cid=0@"+cid;	
		//var getdata = "mid=00010000000000000000000000000000&cid="+cid;
		var data = "new_mid="+mid+"&os="+os;
		//alert (mid+" "+os+" "+cid+" "+dom);
	}else{
		var getdata = "nb=1&tid="+tid+"&mid=00010000000000000000000000000000&cid=0@"+cid;	
	    var data = "";
	}
	$.ajax( {
		   type : "post",
		   url : "./request.php?"+getdata,
		   data: data,
		   success : function(result) {
			   var content = "unkown fail";
			   var ret = decodeURIComponent(result);
			   var keepRequest = 0;
			   ret = eval('(' + ret + ')'); 
			   if (ret["drones"][0]){
                    keepRequest = ret["drones"][0]["keepRequest"];
                    content = ret["drones"][0]["content"];
					tid = ret["drones"][0]["tid"];

			   }
			   if (document.getElementById(dom)){
				   if (1 == keepRequest){
					   timeId = setTimeout(function(){mod_install(tid,cid,mid,os,dom);},3000);
				   }else{
					   document.getElementById(dom).innerHTML=content;
				   }
			   }
		   },
		   error : function(result){
			   if (document.getElementById(dom)){
				   document.getElementById(dom).innerHTML="fail to connect srv";
			   }
		   }
	});	 
}

function chkAll(){
	var c=document.getElementsByName("chkAll");
	var r=document.getElementsByName("opMachine"); 
	var params = "";
	for(var i=0;i<r.length;i++){
       if(r[i].checked){
           if (c[0].checked == false){
               r[i].checked = false;
		   }
       }else{
           if (c[0].checked == true){
               r[i].checked = true;			   
		   }
	   }
	   if (r[i].checked){
		   params += "&" + i + "=" + r[i].value;
	   }
    } 
	app_panel(0);
	client_panel(0);
	post_draw("./cpanel.php","group_panel","act=group_panel&"+params);
}

function mycheckbox(){
	destroy_mod_panel(null);
	var chkAll = true;
    var r=document.getElementsByName("opMachine"); 
	var params = "";
	for(var i=0;i<r.length;i++){
       if(r[i].checked){
             params += "&" + i + "=" + r[i].value;		
       }else{
		   chkAll = false;
	   }
    }      
	var c = document.getElementsByName("chkAll");
	if (chkAll == true){	
        c[0].checked = true;
	}else{
	    c[0].checked = false;
	}
	app_panel('0','0');
	client_panel('0','0');
	post_draw("./cpanel.php","group_panel","act=group_panel&"+params);
}


 function clients_list_load(){
	   post_draw("./cpanel.php","clients_list","act=clients_list");
   }     
   function group_list_load(){
	   post_draw("./cpanel.php","group_list","act=group_list");
   }   
   function show_panel(cid){
	   destroy_mod_panel(null);
	   document.getElementById("group_panel").innerHTML="";
       client_panel(cid);
	   app_panel(cid);
   }
   function client_panel(cid){
       if (cid == 0){
		   document.getElementById("multi_panel").innerHTML = "";
		   document.getElementById("multi_panel").style.display = "none";
       }else{   
		   document.getElementById("multi_panel").style.display = "block";
		   post_draw("./cpanel.php","multi_panel","act=client_panel"+"&cid="+cid);
	   }
   }   
   function app_panel(cid){
       if (cid == 0){
		   document.getElementById("app_panel").innerHTML="";
       }else{
		   post_draw("./app_panel.php","app_panel","cid="+cid);
	   }
   }      

  function destroy_mod_panel(instance){
	    if (null == instance) {
			document.getElementById("mod_panel").innerHTML="";
	    }else{
			var div = document.getElementById("MOD_"+instance);
			if (div){
				div.parentNode.removeChild(div);   //É¾³ý
			}
			InstanceRemove(instance);
		}		
   }

var JsLoadedObjArray = new Array();
function JsLoaderObj(obj,cid){
    var JsFlag = obj; 

	for (var s in JsLoadedObjArray) {
		if (JsLoadedObjArray[s]==JsFlag) {
			if (cid){
				show_multi_panel(cid);
			}
	        return;		
        }
	}
    JsLoadedObjArray.push(JsFlag);
	SyncJsLoader(obj,cid);
}
