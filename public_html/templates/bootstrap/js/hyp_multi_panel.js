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
			var uniqu = "";
			var a=document.getElementById("multi_app_container").getElementsByTagName("div");
		    for(i=0;i<a.length;i++){
				var flag = a[i].id.substring(0,4);
				if ("MOD_" == flag){
                    var uniqu = a[i].id.substring(4);
					hide_show_panel_do(uniqu,status);
				}
			}			
			break;
        case "switcher_ban":
			var uniqu = "";
			var a=document.getElementById("multi_app_container").getElementsByTagName("div");
		    for(i=0;i<a.length;i++){
				var flag = a[i].id.substring(0,4);
				if ("MOD_" == flag){
                    var uniqu = a[i].id.substring(4);
					var tmp = uniqu.split("_",2);
					switch_group_effects_do(tmp[0],tmp[1],uniqu,status);
				}
			}			
			break;
	}
	
}


function hide_show_panel_do(uniqu,act){
	var n = document.getElementById("CONTENT_"+uniqu);
	if (n){	
		var tip_hidden = "<a href=\"javascript:hide_show_panel('"+uniqu+"');\"><i class=\"icon-eye-close\" title=\"show\"></i></a>";
		if (act){
			if ("none" != n.style.display){
		        n.style.display = "none";
				document.getElementById("TIPS_"+uniqu).innerHTML += tip_hidden;
				document.getElementById("MOD_"+uniqu).className = "span3";
			}
		}else{
			if ("none" == n.style.display){
				n.style.display = "block";
				var str = document.getElementById("TIPS_"+uniqu).innerHTML;
				document.getElementById("TIPS_"+uniqu).innerHTML = str.replace(tip_hidden,"");
				var width = document.getElementById("WIDTH_"+uniqu).value;
				do_adjust_panel_width(uniqu,width);			
			}
		}
	}
}

function hide_show_panel(uniqu){
    var n = document.getElementById("CONTENT_"+uniqu);
	if (n){		
        if ("none" == n.style.display){			
			if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
			    toolkit_switcher_do("switcher_show",1);
			}
			hide_show_panel_do(uniqu,0);
        }else{
			hide_show_panel_do(uniqu,1);		    
		}
	}
}

function switch_group_effects_do(cid,mid,uniqu,act){
    var tip_group_effects = "<a href=\"javascript:switch_group_effects('"+cid+"','"+mid+"','"+uniqu+"');\"><i class=\"icon-pause\" title=\"on\"></i></a>";
    var str = document.getElementById("group_effects_"+mid).innerHTML;
	if (act){
		if (str.indexOf (";"+cid+";") >= 0){
			if (document.getElementById("TIPS_"+uniqu).innerHTML.indexOf(tip_group_effects) < 0){
				document.getElementById("TIPS_"+uniqu).innerHTML += tip_group_effects;
			}
			sub_effects_group(cid,mid);	
		}
	}else{
		if (str.indexOf (";"+cid+";") < 0){
			document.getElementById("TIPS_"+uniqu).innerHTML = document.getElementById("TIPS_"+uniqu).innerHTML.replace(tip_group_effects,"");
			add_effects_group(cid,mid);
			if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
		        document.getElementById("switcher_ban").className = document.getElementById("switcher_ban").className.replace(/ active/,"");
			}
		}		
	}
}

function switch_group_effects(cid,mid,uniqu){
    var n = document.getElementById("group_effects_"+mid);
	if (n){		
		var str = document.getElementById("group_effects_"+mid).innerHTML;
		if (str.indexOf (";"+cid+";") >= 0){
			switch_group_effects_do(cid,mid,uniqu,1);
		}else{
			switch_group_effects_do(cid,mid,uniqu,0);
        }
	}
}


function do_adjust_panel_width(uniqu,width){
	if (!width){
		document.getElementById('WIDGET_'+uniqu).innerHTML = "";
	}else{
		document.getElementById('MOD_'+uniqu).className = "span"+width+" offset1";
		document.getElementById('WIDTH_'+uniqu).value   = width;
	}
}

function adjust_panel_width(uniqu){	
    document.getElementById("WIDGET_"+uniqu).innerHTML = " \
		<div class=\"btn-toolbar\" style=\"margin: 0;\">\
		<div class=\"btn-group\">\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',0);\" class=\"btn\"><i class=\"icon-remove\"></i></button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',2);\" class=\"btn\">2</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',3);\" class=\"btn\">3</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',4);\" class=\"btn\">4</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',5);\" class=\"btn\">5</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',6);\" class=\"btn\">6</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',7);\" class=\"btn\">7</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',8);\" class=\"btn\">8</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',9);\" class=\"btn\">9</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',10);\" class=\"btn\">10</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',11);\" class=\"btn\">11</button>\
		<button onclick=\"do_adjust_panel_width('"+uniqu+"',12);\" class=\"btn\">12</button>\
	  </div>\
    </div>";
}

function up_multi_panel(uniqu){	
	var el = document.getElementById("MOD_"+uniqu);
    var t_el = $(el).clone(true);
	$("#multi_app_container").children(":first").before(t_el);
	$(el).remove();
	location.hash = "MOD_"+uniqu;
    document.getElementById("TOP_MOD").value = uniqu;
}

function show_multi_panel(cid){
    destroy_simple_panel();
    document.getElementById("multiPanel").style.display="block";
    document.getElementById("simplePanel").style.display="none";
	post_draw("./app_panel.php","multi_app_panel","multi=1&cid="+cid);
}

function add_effects_group(cid,mid){
	if (document.getElementById('group_effects')){	
	    if (null == document.getElementById('group_effects_'+mid)){
		    document.getElementById('group_effects').innerHTML += "<div id=\"group_effects_"+mid+"\">;</div>";			
		}		
		if (document.getElementById("group_effects_"+mid).innerHTML.indexOf (";"+cid+";") < 0){
			document.getElementById('group_effects_'+mid).innerHTML += cid+";";
		}
	}	
}

function sub_effects_group(cid,mid){
    if (document.getElementById('group_effects')){	
	    if (document.getElementById('group_effects_'+mid)){
		    var c = document.getElementById('group_effects_'+mid).innerHTML;
			c = c.replace(";"+cid+";",";");
			document.getElementById('group_effects_'+mid).innerHTML = c;
		}
	}	
}

function open_mod_multi(cid,mid){
    var uniqu = cid+"_"+mid;
	if (null != document.getElementById("MOD_"+uniqu)){
        if (uniqu != document.getElementById("TOP_MOD").value){
		    up_multi_panel(uniqu);
			return;
		}		
	}
	if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
		toolkit_switcher_do("switcher_show",1);
	}
	add_effects_group(cid,mid);		
	create_mod_panel_multi(uniqu);
	up_multi_panel(uniqu);
	do_request(cid,mid,null,'',null,uniqu,null,null,null);	
	if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
		switch_group_effects_do(cid,mid,uniqu,1);
	}

}

function create_mod_panel_multi(uniqu){
    if (null == document.getElementById("TITLE_"+uniqu)){
		document.getElementById("multi_app_container").innerHTML += "<div class=\"span5 offset1\" id=\"MOD_"+uniqu+"\">\
                                                                	 <input type=\"hidden\" value=\"5\" id=\"WIDTH_"+uniqu+"\">\
                                                                	 <input type=\"hidden\" value=\"\" id=\"SCC_"+uniqu+"\">\
		                                                             <a name=\"TOP_"+uniqu+"\"></a>\
		                                                             <div id=\"TID_"+uniqu+"\"></div>\
		                                                             <hr><div style=\"float: right;\" id=\"STATU_"+uniqu+"\"></div>\
																		 <div style=\"float: left;position: relative;z-index: 10;\" id=\"TIPS_"+uniqu+"\"></div>\
																		 <div id=\"TITLE_"+uniqu+"\"></div>\
																		 <div id=\"WIDGET_"+uniqu+"\"></div>\
																		 <div id=\"CONTENT_"+uniqu+"\"></div></div>";
	}else{
		//alert("mode_panel_multi already exists,give up create");
	}	   
}


function multi_exec_shortcut(sid,cid,mid){
    var uniqu = cid+"_"+mid;
	if (null != document.getElementById("MOD_"+uniqu)){
		up_multi_panel(uniqu);
	}else{
		if (document.getElementById("switcher_show").className.indexOf(" active") >= 0){
		    toolkit_switcher_do("switcher_show",1);
		}
		add_effects_group(cid,mid);
		create_mod_panel_multi(uniqu);
		up_multi_panel(uniqu);		
	}
	if (document.getElementById("switcher_ban").className.indexOf(" active") >= 0){		
	    switch_group_effects_do(cid,mid,uniqu,1);
	}
    do_request(cid,mid,sid,'',null,cid+"_"+mid,null,null,null);
}

function destroy_simple_panel(){
    app_panel('0','0');
	client_panel('0','0');
	destroy_mod_panel(null);
}