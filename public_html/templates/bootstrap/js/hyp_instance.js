
// ./readme/Ç°¶ËÊµÀý.txt

InstanceArray = new Array(null);
InstanceReserveArray = new Array();


function InstanceCheck(InstanceID,cid,mid){
    if ((InstanceArray[InstanceID]) && (InstanceArray[InstanceID] == cid+"_"+mid)){
		return true;
    }
	return false;
}

function InstanceToCid(InstanceID){
	if ((InstanceArray[InstanceID]) && (null != InstanceArray[InstanceID])){
        var tmp = InstanceArray[InstanceID].split("_",2);
		return InstanceID+"@"+tmp[0];
	}
	return "";
}

function InstanceRemove(InstanceID){
    InstanceArray[InstanceID] = null;
    InstanceReserveArray.push(InstanceID);
}

function InstanceGet(uniqu,alwaysCreate){
	if (false == alwaysCreate){
        for(var ele in InstanceArray){
			if (uniqu == InstanceArray[ele]){
				return ele;
			}
		}
	}
	//not exists,create new
	var InstanceID = InstanceReserveArray.pop();
	if (!InstanceID){
		InstanceID = InstanceArray.push(uniqu);
		InstanceID -- ;
	}else{
		InstanceArray[InstanceID] = uniqu;
	}
	return InstanceID;
}

function InstanceRead(InstanceID){
	if ((InstanceArray[InstanceID]) && (null != InstanceArray[InstanceID])){
		return InstanceArray[InstanceID];
	}else{
	    return null;
	}

}

function InstanceShow(){
	var tmp = "";
    for(var ele in InstanceArray){
	    tmp +="\r\n"+ele+":"+InstanceArray[ele];
	}
	tmp += "\r\n Reserve:\r\n";
	for(var ele in InstanceReserveArray){
	    tmp +="\r\n"+ele+":"+InstanceReserveArray[ele];
	}
}