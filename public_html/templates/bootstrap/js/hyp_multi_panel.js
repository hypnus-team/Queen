function multi_show_panel(cid){
	document.getElementById("multi_panel").style.display = "block";
    post_draw("./app_panel.php","multi_panel","cid="+cid);
}

function multi_clients_list_load(show){
   if ((!show) && ("block" == document.getElementById("multi_clients_list").style.display)){
	   document.getElementById("multi_clients_list").style.display = "none";
   }else{
	   document.getElementById("multi_clients_list").style.display = "block";
	   post_draw("./cpanel.php","multi_clients_list","act=clients_list");
   }
}     
function multi_group_list_load(show){
   if ((!show) && ("block" == document.getElementById("multi_clients_list").style.display)){
	   document.getElementById("multi_clients_list").style.display = "none";
   }else{
	   document.getElementById("multi_clients_list").style.display = "block";
	   post_draw("./cpanel.php","multi_clients_list","act=group_list");
   }
}

function multi_app_panel_switch(act){
   if ("hide" == act){
	   document.getElementById("app_panel_hide").style.display="block";
	   document.getElementById("app_panel_show").style.display="none";
	   document.getElementById("multi_app_panel").className = "span1";	   
   }else{
	   document.getElementById("app_panel_hide").style.display="none";
	   document.getElementById("app_panel_show").style.display="block";
	   document.getElementById("multi_app_panel").className = "span5 offset1";
   }
}

//act 0 :switch
//    1 :push
//   -1 :pull
function toolkit_switcher(name,act){
    var Cts = document.getElementById(name).className;
	if ((Cts.indexOf(" active") >= 0) && (1 != act)){
		Cts = Cts.replace(/ active/, "");	 
		toolkit_switcher_do(name,0);
		document.getElementById(name).className = Cts;
	}else if (-1 != act){
		Cts += " active";
		toolkit_switcher_do(name,1);
		document.getElementById(name).className = Cts;
	}		
}


function toolkit_switcher_do(name,status){
	switch (name){
	    case "switcher_clist":
			if (status){
			    toolkit_switcher('switcher_glist',-1);
			}
			multi_clients_list_load(status);
			break;
        case "switcher_glist":
			if (status){
			    toolkit_switcher('switcher_clist',-1);
			}
			multi_group_list_load(status);			
	        break;
        case "switcher_show":
			var a=document.getElementById("multi_app_container").getElementsByTagName("div");
		    for(i=0;i<a.length;i++){
				var flag = a[i].id.substring(0,4);
				if ("MOD_" == flag){
					hide_show_panel_do(a[i].id.substring(4),status);
				}
			}			
			break;
        case "switcher_ban":
			var a=document.getElementById("multi_app_container").getElementsByTagName("div");
		    for(i=0;i<a.length;i++){
				var flag = a[i].id.substring(0,4);
				if ("MOD_" == flag){
					var instance = a[i].id.substring(4);
                    var uniqu = InstanceRead(instance);
					if (null != uniqu){
						var tmp = uniqu.split("_",2);
						switch_group_effects_do(tmp[0],tmp[1],instance,status);					
					}
				}
			}			
			break;
	}
	
}


function hide_show_panel_do(instance,act){
	var n = document.getElementById("CONTENT_"+instance);
	if (n){	
		var tip_hidden = "<a href=\"javascript:hide_show_panel('"+instance+"');\"><i class=\"icon-eye-close\" title=\"show\"></i></a>";
		if (act){
			if ("none" != n.style.display){
		        n.style.display = "none";
				document.getElementById("TIPS_"+instance).innerHTML += tip_hidden;
				document.getElementById("MOD_"+instance).className = "span3";
			}
		}else{
			if ("none" == n.style.display){
				n.style.display = "block";
				var str = document.getElementById("TIPS_"+instance).innerHTML;
				document.getElementById("TIPS_"+instance).innerHTML = str.replace(tip_hidden,"");
				var width = document.getElementById("WIDTH_"+instance).value;
				do_adjust_panel_width(instance,width);			
			}
		}
	}
}

function hide_show_panel(instance){
    var n = document.getElementById("CONTENT_"+instance);
	if (n){		
        if ("none" == n.style.display){			
			if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
			    toolkit_switcher_do("switcher_show",1);
			}
			hide_show_panel_do(instance,0);
        }else{
			hide_show_panel_do(instance,1);		    
		}
	}
}

function switch_group_effects_do(cid,mid,instance,act){
    var tip_group_effects = "<a href=\"javascript:switch_group_effects('"+cid+"','"+mid+"','"+instance+"');\"><i class=\"icon-pause\" title=\"on\"></i></a>";
    var str = document.getElementById("group_effects_"+mid).innerHTML;
	if (act){
		if (str.indexOf (";"+instance+"@"+cid+";") >= 0){
			if (document.getElementById("TIPS_"+instance).innerHTML.indexOf(tip_group_effects) < 0){
				document.getElementById("TIPS_"+instance).innerHTML += tip_group_effects;
			}
			sub_effects_group(cid,instance,mid);	
		}
	}else{
		if (str.indexOf (";"+instance+"@"+cid+";") < 0){
			document.getElementById("TIPS_"+instance).innerHTML = document.getElementById("TIPS_"+instance).innerHTML.replace(tip_group_effects,"");
			add_effects_group(cid,instance,mid);
			//if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
		    //    document.getElementById("switcher_ban").className = document.getElementById("switcher_ban").className.replace(/ active/,"");
			//}
		}		
	}
}

function switch_group_effects(cid,mid,instance){
    var n = document.getElementById("group_effects_"+mid);
	if (n){		
		var str = document.getElementById("group_effects_"+mid).innerHTML;
		if (str.indexOf (";"+instance+"@"+cid+";") >= 0){
			switch_group_effects_do(cid,mid,instance,1);
		}else{
			switch_group_effects_do(cid,mid,instance,0);
        }
	}
}


function do_adjust_panel_width(instance,width){
	if (!width){
		document.getElementById('WIDGET_'+instance).innerHTML = "";
	}else{
		document.getElementById('MOD_'+instance).className = "span"+width+" offset1";
		document.getElementById('WIDTH_'+instance).value   = width;
	}
}

function adjust_panel_width(instance){	
    document.getElementById("WIDGET_"+instance).innerHTML = " \
		<div class=\"btn-toolbar\" style=\"margin: 0;\">\
		<div class=\"btn-group\">\
		<button onclick=\"do_adjust_panel_width('"+instance+"',0);\" class=\"btn\"><i class=\"icon-remove\"></i></button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',2);\" class=\"btn\">2</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',3);\" class=\"btn\">3</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',4);\" class=\"btn\">4</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',5);\" class=\"btn\">5</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',6);\" class=\"btn\">6</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',7);\" class=\"btn\">7</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',8);\" class=\"btn\">8</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',9);\" class=\"btn\">9</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',10);\" class=\"btn\">10</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',11);\" class=\"btn\">11</button>\
		<button onclick=\"do_adjust_panel_width('"+instance+"',12);\" class=\"btn\">12</button>\
	  </div>\
    </div>";
}

function up_multi_panel(instance){	
	var el = document.getElementById("MOD_"+instance);
    var t_el = $(el).clone(true);
	$("#multi_app_container").children(":first").before(t_el);
	$(el).remove();
	location.hash = "MOD_"+instance;
    document.getElementById("TOP_MOD").value = instance;
}

function show_multi_panel(cid){
    destroy_simple_panel();
    document.getElementById("multiPanel").style.display="block";
    document.getElementById("simplePanel").style.display="none";
	post_draw("./app_panel.php","multi_app_panel","multi=1&cid="+cid);
}

function add_effects_group(cid,instance,mid){
	if (document.getElementById('group_effects')){
	    if (null == document.getElementById('group_effects_'+mid)){
		    document.getElementById('group_effects').innerHTML += "<div id=\"group_effects_"+mid+"\">;</div>";			
		}		
		if (document.getElementById("group_effects_"+mid).innerHTML.indexOf (";"+instance+"@"+cid+";") < 0){
			document.getElementById('group_effects_'+mid).innerHTML += instance+"@"+cid+";";
		}
 	    reset_effects_group_span (mid,null);
	}	
}

function sub_effects_group(cid,instance,mid){
    if (document.getElementById('group_effects')){	
	    if (document.getElementById('group_effects_'+mid)){
		    var c = document.getElementById('group_effects_'+mid).innerHTML;
			c = c.replace(";"+instance+"@"+cid+";",";");
			document.getElementById('group_effects_'+mid).innerHTML = c;
		}
		reset_effects_group_span (mid,instance);
	}	
}

function reset_effects_group_span(mid,instance){
    if (document.getElementById('group_effects_'+mid)){
        var reg = new RegExp("(<span class=\"badge badge-important\">)(\\d+)</span>","gmi");
        if (instance){
			if (document.getElementById("TIPS_"+instance)){
				var c = document.getElementById("TIPS_"+instance).innerHTML;
				document.getElementById("TIPS_"+instance).innerHTML =c.replace(reg,"");
			}					
		}

		var c_eg_contents = document.getElementById('group_effects_'+mid).innerHTML;
		var explodeCid = c_eg_contents.split(";");
		var explodeNum = 0;

		for(i=0; i<explodeCid.length; i++){
			  var tmp = explodeCid[i].split("@",2);
			  if (tmp[0] > 0){
				  explodeNum++;			
			  }
		 }
		 var effectNum = explodeNum - 1;
		 for(i=0; i<explodeCid.length; i++){
			  var tmp = explodeCid[i].split("@",2);
			  if (tmp[0] > 0){
				  if (document.getElementById("TIPS_"+tmp[0])){
					  document.getElementById("TIPS_"+tmp[0]).innerHTML = document.getElementById("TIPS_"+tmp[0]).innerHTML.replace(reg,"");					  
					  if (effectNum > 0){
						  document.getElementById("TIPS_"+tmp[0]).innerHTML += '<span class="badge badge-important">'+effectNum+'</span>';			  
					  }
				  }
			  }
		 }

		 //alert(msg+" No."+explodeNum); 		
	}
}

function open_mod_multi(cid,mid){
    //根据按钮 是否总是新建 实例 
	if (document.getElementById("switcher_instance").className.indexOf(" active") >= 0){
		toolkit_switcher('switcher_instance',0);
	    var instance = InstanceGet(cid+"_"+mid,true);
	}else{
		var instance = InstanceGet(cid+"_"+mid,false);	
	}

	if (null != document.getElementById("MOD_"+instance)){
        if (instance != document.getElementById("TOP_MOD").value){
		    up_multi_panel(instance);
			return;
		}		
	}
	
	if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
		toolkit_switcher_do("switcher_show",1);
	}
	create_mod_panel_multi(instance);
	add_effects_group(cid,instance,mid);		
	up_multi_panel(instance);	
	if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
		switch_group_effects_do(cid,mid,instance,1);
	}
	do_request(cid,mid,null,'',null,instance,null,null,null);

}

function create_mod_panel_multi(instance){
    if (null == document.getElementById("TITLE_"+instance)){
		document.getElementById("multi_app_container").innerHTML += "<div class=\"span5 offset1\" id=\"MOD_"+instance+"\">\
                                                                	 <input type=\"hidden\" value=\"5\" id=\"WIDTH_"+instance+"\">\
                                                                	 <input type=\"hidden\" value=\"\" id=\"SCC_"+instance+"\">\
		                                                             <a name=\"TOP_"+instance+"\"></a>\
		                                                             <div id=\"TID_"+instance+"\"></div>\
		                                                             <hr><div style=\"float: right;\" id=\"STATU_"+instance+"\"></div>\
																		 <div style=\"float: left;position: relative;z-index: 10;\" id=\"TIPS_"+instance+"\"></div>\
																		 <div id=\"TITLE_"+instance+"\"></div>\
																		 <div id=\"WIDGET_"+instance+"\"></div>\
																		 <div id=\"CONTENT_"+instance+"\"></div></div>";
	}else{
		//alert("mode_panel_multi already exists,give up create");
	}	   
}


function multi_exec_shortcut(sid,cid,mid){
    //根据按钮 是否总是新建 实例 
	if (document.getElementById("switcher_instance").className.indexOf(" active") >= 0){
		toolkit_switcher('switcher_instance',0);
	    var instance = InstanceGet(cid+"_"+mid,true);
	}else{
		var instance = InstanceGet(cid+"_"+mid,false);	
	}
	if (null != document.getElementById("MOD_"+instance)){
		up_multi_panel(instance);
	}

	if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
		toolkit_switcher_do("switcher_show",1);
	}
	create_mod_panel_multi(instance);
	add_effects_group(cid,instance,mid);		
	up_multi_panel(instance);		

	if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
	    switch_group_effects_do(cid,mid,instance,1);
	}
    do_request(cid,mid,sid,'',null,instance,null,null,null);
}

function destroy_simple_panel(){
    app_panel('0','0');
	client_panel('0','0');
	destroy_mod_panel(null);
}