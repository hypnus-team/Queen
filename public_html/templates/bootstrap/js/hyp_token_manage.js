function token_mana_edit(tokenId){
	document.getElementById(tokenId+"_remark").style.display="none";
	document.getElementById(tokenId+"_comment").style.display="block";
	document.getElementById(tokenId+"_edit").style.display="none";
	document.getElementById(tokenId+"_save").style.display="block";
	document.getElementById(tokenId+"_load").style.display="none";
}

function token_mana_save(tokenId){

    document.getElementById(tokenId+"_edit").style.display="none";
	document.getElementById(tokenId+"_save").style.display="none";
    document.getElementById(tokenId+"_load").style.display="block";
    
	var my_data  = "tid="+tokenId+"&comment=";
	    my_data += encodeURIComponent(document.getElementById(tokenId).value);
	$.ajax( {
		   type : "post",
		   url : "./myaccount.php?a=7",
		   data: my_data,
		   success : function(result) {
			   document.getElementById(tokenId+"_remark_comment").innerHTML = result;
			   token_mana_reset(tokenId);
		   },
		   error : function(){			   
			   document.getElementById(tokenId+"_remark").innerHTML='fail connect srv';
			   token_mana_reset(tokenId);
		   }
	});
}

function token_mana_reset(tokenId){
	document.getElementById(tokenId+"_remark").style.display="block";
	document.getElementById(tokenId+"_comment").style.display="none";
	document.getElementById(tokenId+"_edit").style.display="block";
	document.getElementById(tokenId+"_save").style.display="none";
	document.getElementById(tokenId+"_load").style.display="none";
}