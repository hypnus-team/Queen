
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

   function show_shortcut(uniqu,cid,mid,placeholder,save){
	   var hd = document.getElementById("WIDGET_"+uniqu);	   
	   if (hd){
		   var contents = "<input id=\"shortcut_name\" type=\"text\" value=\"\" placeholder=\""+placeholder+"\">&nbsp;<a href=\"javascript:save_as_shortcut('"+uniqu+"','"+cid+"','"+mid+"');\">"+save+"</a><a href=\"./readme.php?article=45\" target=\"_blank\"><i class=\"icon-question-sign\"></i></a>";	   
		   if (hd.innerHTML == contents){
		       hd.innerHTML = "";
		   }else{
			   hd.innerHTML = contents;
		   }
	   }      
   }

   function save_as_shortcut(uniqu,cid,mid){ 
	   var hd = document.getElementById("SCC_"+uniqu);
	   if (hd){
		   var shortcut_name = document.getElementById("shortcut_name").value;		      
	       var scc = document.getElementById("SCC_"+uniqu).value;
		   post_draw("./shortcut.php?name="+shortcut_name+"&cid="+cid+"&mid="+mid,"WIDGET_"+uniqu,scc);   
	   }
   }

   function mod_request(cid,mid,uniqu,data,callback,callback_params){
       do_request(cid,mid,null,data,null,uniqu,callback,callback_params,null);
   }

   function mod_request_sentinel(tid,cid,mid,uniqu){
	   document.getElementById("TID_"+uniqu).innerHTML = "<a id=\"Tid_"+tid+"\"></a>";	   
	   do_request(cid,mid,null,null,tid,uniqu,null,null,null);
   }

                         
   function do_request(cid,mid,sid,data,tid,uniqu,callback,callback_params,remain_unit){
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
         
          //check group effects 
		  cid = get_effect_clients(cid,mid);

		  getdata += "&cid="+cid

		  if (document.getElementById("TITLE_"+uniqu).innerHTML == ""){
              getdata += "&reqtitle=1";			  
		  }

		  var explodeCid = cid.split(";");
		  for(i=0; i<explodeCid.length; i++){
			  if (explodeCid[i] > 0){
				  document.getElementById("STATU_"+explodeCid[i]+"_"+mid).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";		  
				  document.getElementById("WIDGET_"+explodeCid[i]+"_"+mid).innerHTML="";
				  if (!callback){
					  document.getElementById("SCC_"+explodeCid[i]+"_"+mid).value=ShortCutsContents;
				  }
			  }
		  }

	   }else{
          if (null == document.getElementById("Tid_"+tid)){	
			  //alert ("Tid_"+tid+" is null,exit");
			  return;
          }
	      getdata = "tid="+tid+"&cid="+cid+"&mid="+mid;
	   }

	   if ("block" == document.getElementById("multiPanel").style.display){
		   getdata += "&multi=1";
	   }
	   
	   $.ajax( {
		   type : "post",
		   url : "./request.php?"+getdata,
		   data: data,
		   success : function(result) {
			   show_do_request(result,tid,mid,callback,callback_params);
		   },
		   error : function(result){
			   try{                 
				 document.getElementById("STATU_"+uniqu).innerHTML='fail to connect srv';
			   }catch(e){
			     alert("js runtime fail(func do_request.error): "+e.description);
			   }
		   }

	   });	  
	 }catch(e){
	     alert("js runtime fail(func do_request): "+e.description);
	 }
   }

function get_effect_clients(cid,mid){
	if (document.getElementById('group_effects_'+mid)){
		var c = document.getElementById('group_effects_'+mid).innerHTML;
		if (c.indexOf (";"+cid+";") >= 0){
			cid = document.getElementById('group_effects_'+mid).innerHTML;
		}
	}
	return cid;
}

function show_do_request(result,tid,mid,callback,callback_params){
   var c_tid;
   var remain_cid = "";
   try{	
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
			 
			 if (document.getElementById("STATU_"+c_uniqu)){					 					
				 if (c_response.keepRequest){
					 document.getElementById("STATU_"+c_uniqu).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";
					 remain_cid += c_response.cid+";";
					 keepAlive = true;
					 mid = c_response.mid;
					 c_tid = c_response.tid;
				 }else{
					 if (c_response.fail){
						 document.getElementById("STATU_"+c_uniqu).innerHTML="<img src=\"./templates/bootstrap/img/warning-mini.png\">";
					 }else{
						 document.getElementById("STATU_"+c_uniqu).innerHTML="";
					 }
					 if (!callback){
						 if (callback_params){
							 if (document.getElementById(callback_params)){
							     document.getElementById(callback_params).innerHTML=c_response.content;
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
			 timeId = setTimeout(function(){do_request(remain_cid,mid,null,null,c_tid,c_uniqu,callback,callback_params,null);},1);
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
