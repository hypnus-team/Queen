
   function post_draw(my_url,my_position,my_data){
	 try{
       document.getElementById(my_position).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";
	   $.ajax( {
		   type : "post",
		   url : my_url,
		   data: my_data,
		   success : function(result) {
			   try{
				 document.getElementById(my_position).innerHTML=result;
			   }catch(e){
	               alert("js runtime fail(func post_draw.success): "+e.description);
			   }
		   },
		   error : function(){			   
			   try{
				 document.getElementById(my_position).innerHTML='fail to connect srv';
			   }catch(e){
	               alert("js runtime fail(func post_draw.error): "+e.description);
			   }
		   }

	   });
	 }catch(e){
	     alert("js runtime fail(func post_draw): "+e.description);
	 }
   }

   function show_shortcut(instance,cid,mid,placeholder,save){
	   var hd = document.getElementById("WIDGET_"+instance);	   
	   if (hd){
		   var contents = "<input id=\"shortcut_name\" type=\"text\" value=\"\" placeholder=\""+placeholder+"\">&nbsp;<a href=\"javascript:save_as_shortcut('"+instance+"','"+cid+"','"+mid+"');\">"+save+"</a><a href=\"./readme.php?article=45\" target=\"_blank\"><i class=\"icon-question-sign\"></i></a>";	   
		   if (hd.innerHTML == contents){
		       hd.innerHTML = "";
		   }else{
			   hd.innerHTML = contents;
		   }
	   }      
   }

   function save_as_shortcut(instance,cid,mid){ 
	   var hd = document.getElementById("SCC_"+instance);
	   if (hd){
		   var shortcut_name = document.getElementById("shortcut_name").value;		      
	       var scc = document.getElementById("SCC_"+instance).value;
		   post_draw("./shortcut.php?name="+shortcut_name+"&cid="+cid+"&mid="+mid,"WIDGET_"+instance,scc);   
	   }
   }

   function mod_request(cid,mid,instance,data,callback,callback_params){
       do_request(mid,null,data,null,instance,callback,callback_params,false);
   }

   function mod_request_sentinel(tid,cid,mid,instance){
	   document.getElementById("TID_"+instance).innerHTML = "<a id=\"Tid_"+tid+"\"></a>";
	   do_request(mid,null,null,tid,cid,null,null,false);
   }

                         
   function do_request(mid,sid,data,tid,instance,callback,callback_params,InstTid){

	 try{
	   if (!tid){          
		  var ShortCutsContents = "";
	      if (!sid){
              getdata = "mid="+mid;
			  ShortCutsContents = data;
	      }else{
		      getdata = "sid="+sid;
			  ShortCutsContents = "sid:"+sid;
		  }
          	  
		  if (document.getElementById("TITLE_"+instance).innerHTML == ""){
              getdata += "&reqtitle=1";			  
		  }		  
          //check group effects 		  		  
		  var instance = get_effect_clients(instance,mid);
		  var explodeInstance = instance.split(";");

		  for(i=0; i<explodeInstance.length; i++){
			  var tmp = explodeInstance[i].split("@",2);
			  if (tmp[0] > 0){		
				  if (document.getElementById("STATU_"+tmp[0])){
					  document.getElementById("STATU_"+tmp[0]).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";		  
					  document.getElementById("WIDGET_"+tmp[0]).innerHTML="";
					  if (!callback){
						  document.getElementById("SCC_"+tmp[0]).value=ShortCutsContents;
					  }					  
				  }				  
			  }
		  }

		  getdata += "&cid="+instance;

	   }else{
          if (null == document.getElementById("Tid_"+tid)){	
			  //alert ("Tid_"+tid+" is null,exit");
			  return;
          }
	      getdata = "tid="+tid+"&cid="+instance+"&mid="+mid;
	   }
       
	   //初始化 instance <-> tid
   	   if (false == InstTid){ 
			 InstTid = InstanceTidNew();
			 var explodeInstance = instance.split(";");
			 for(i=0; i<explodeInstance.length; i++){
				  var tmp = explodeInstance[i].split("@",2);
				  if (tmp[0]){
					  InstanceTidSet(tmp[0],InstTid);
				  }
			 }
	   }

	   if ("block" == document.getElementById("multiPanel").style.display){
		   getdata += "&multi=1";
	   }
	   
	   $.ajax( {
		   type : "post",
		   url : "./request.php?"+getdata,
		   data: data,
		   success : function(result) {
			   show_do_request(instance,result,tid,mid,callback,callback_params,InstTid);
		   },
		   error : function(result){
			   show_fail_do_request(InstTid,mid,instance,'fail to connect srv');			   
		   }

	   });	
	 }catch(e){
	     alert("js runtime fail(func do_request): "+e.description);
	 }
   }

function show_fail_do_request(tid,mid,instance,Message){

    var explodeInstance = instance.split(";");

    for(i=0; i<explodeInstance.length; i++){
		var tmp = explodeInstance[i].split("@",2);
		if (tmp[0] > 0){		
			if (document.getElementById("STATU_"+tmp[0])){
				if (!InstanceCheck(tmp[0],tmp[1],mid)){
					//alert ("InstanceCheck " + tmp[0] + ":" + tmp[1] + ":" + mid);
					continue;
				}
				if (!InstanceTidCheck(tmp[0],tid)){
					//alert ("InstanceTidCheck " + tmp[0] + ":" + tid);
					continue;
				}
				//alert ("STATU_"+tmp[0]);
				document.getElementById("STATU_"+tmp[0]).innerHTML=Message;
			}		  
		}
	}



}

function get_effect_clients(instance,mid){	
	instance = ";"+InstanceToCid(instance)+";";
	if (document.getElementById('group_effects_'+mid)){
		var c = document.getElementById('group_effects_'+mid).innerHTML;
		if (c.indexOf (instance) >= 0){
			instance = document.getElementById('group_effects_'+mid).innerHTML;
		}
	}
	return instance;
}

function show_do_request(instance,result,tid,mid,callback,callback_params,InstTid){
   var c_tid;
   var remain_cid = "";
   try{
	   if (!result){
		   show_fail_do_request(InstTid,mid,instance,"empty response");
		   return;
	   }
	 var ret = decodeURIComponent(result);				     
	 ret = eval('(' + ret + ')');
	 var keepAlive = false;
	 var i = 0;
	 if (ret["tips"]){
		 document.getElementById("header_warning").innerHTML = ret["tips"];
	 }
	 if (ret["drones"]){				 
		 ret = ret["drones"];
		 for(i=0; i<ret.length; i++){
			 var c_response = ret[i];

			 if (c_response.uniqu){
				 c_uniqu = c_response.uniqu;
			 }
			 if (!InstanceCheck(c_uniqu,c_response.cid,mid)){ // 识别 instance 仍然有效且未被更改
				 continue;
			 }
			 if (!InstanceTidCheck(c_uniqu,InstTid)){ // InstTid 已不是当前 Tid
				 //alert ("InstTid expired... " + InstTid + " != now: " + InstanceTidArray[c_uniqu]);
                 continue;
			 }
			 if (document.getElementById("STATU_"+c_uniqu)){					 					
				 if (c_response.keepRequest){
					 document.getElementById("STATU_"+c_uniqu).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";
					 remain_cid += c_uniqu+"@"+c_response.cid+";";
					 keepAlive = true;
					 c_tid = c_response.tid;
				 }else{
					 if (c_response.fail){
						 document.getElementById("STATU_"+c_uniqu).innerHTML="<img src=\"./templates/bootstrap/img/warning-mini.png\">";
					 }else{
						 document.getElementById("STATU_"+c_uniqu).innerHTML="";
					 }
					 if (!callback){						 
						 if (callback_params){
							 if (document.getElementById(callback_params+c_uniqu)){								 
							     document.getElementById(callback_params+c_uniqu).innerHTML=c_response.content;
							 }else{
							     callback_params = 0;
							 }
						 }
						 if (!callback_params){
							 document.getElementById("CONTENT_"+c_uniqu).innerHTML=c_response.content;
						 }
					 }else{
						 alert("call callback() start [not support now!]");
						 //callback();
					 }			         
				 } 

                 if (c_response.title){
				     document.getElementById("TITLE_"+c_uniqu).innerHTML=c_response.title;
				 }

				 if (!tid){					 
			         JsLoader(mid,"default");
					 document.getElementById("TID_"+c_uniqu).innerHTML = "<a id=\"Tid_"+c_response.tid+"\"></a>";					 
				 }
			 }
		 }
		 if (keepAlive == true){
			 timeId = setTimeout(function(){do_request(mid,null,null,c_tid,remain_cid,callback,callback_params,InstTid);},1);
			 //timeId = setTimeout(function(){do_request(cid,mid,sid,null,response.tid,c_uniqu,callback,callback_params,remain_unit);},1);
		 }
	 }else{
	     //alert ("? no any legal drone contents return,why");
	 }	 
   }catch(e){
	 alert("js runtime fail(func show_do_request): "+e.description);
   }
}


var JsLoadedArray= new Array();
function JsLoader(mid,JsName){
    var JsFlag = mid+JsName; 

	for (var s in JsLoadedArray) {
		if (JsLoadedArray[s]==JsFlag) {
	        return;		
        }
	}
    JsLoadedArray.push(JsFlag);
	SyncJsLoader("./mod_agent.php?obj=js"+"&mid="+mid+"&JsName="+JsName,null);
}

function SyncJsLoader(src,cid){
	//$.getScript(src);
	//return;
	$.ajax({
	  dataType: "script",
	  url: src,
	  async: false,
	  cache: true,
      //cache: false,
	  error : function(result){
	      alert("Js Loader Fail: "+src);
	  },
	  success : function(){
		  if (cid){
			  show_multi_panel(cid);
		  }	      
	  }
	});  
}
