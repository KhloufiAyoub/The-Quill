init();

function init(){
    $("RoomButton").onclick = ShowRoom;
    $("MsgButton").onclick = ShowMessage;
    $("ObjButton").onclick = ShowObject;
    $("RoomSubmit").onclick = AddRoom;
    $("MsgSubmit").onclick = AddMessage;
    $("ObjSubmit").onclick = AddObject;
    $("submitWord").onclick = EditWord;
    $("submitObj").onclick = EditObj;
    $("submitSave").onclick = EditSave;
    GetMaxValue();
}

function ShowRoom(){
    hide("AddMsg");
    hide("AddObj");
    show("AddRoom");
}

function ShowMessage(){
    hide("AddRoom");
    hide("AddObj");
    show("AddMsg");
}

function ShowObject(){
    hide("AddRoom");
    hide("AddMsg");
    show("AddObj");
}
function Add(url,param){
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}


function AddRoom(){
    var roomName = $("RoomValue").value;
    var url="PHP/AddRoom.php";
    var param="room="+encodeURIComponent(roomName);
    Add(url,param,roomName);
}

function AddMessage(){
    var msgName = $("MsgValue").value;
    var url="PHP/AddMsg.php";
    var param="msg="+encodeURIComponent(msgName);
    Add(url,param,msgName);
}

function AddObject(){
    var objName = $("ObjValue").value;
    var url="PHP/AddObj.php";
    var param="obj="+encodeURIComponent(objName);
    Add(url,param,objName);
}

function Edit(url,param){
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.onreadystatechange = function(){
        if (xmlHttpRequest.readyState === 4 && xmlHttpRequest.status === 200){
            GetMaxValue();
        }
    }
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}

function EditWord(){
    var wordSize = $("wordSize").value;
    console.log(wordSize);
    var url="PHP/EditWord.php";
    var param="wordSize="+encodeURIComponent(wordSize);
    Edit(url,param);
}

function EditObj(){
    var maxObj = $("maxObj").value;
    console.log(maxObj);
    var url="PHP/EditObj.php";
    var param="maxObj="+encodeURIComponent(maxObj);
    Edit(url,param);
}

function EditSave(){
    var maxSave = $("maxSave").value;
    var url="PHP/EditSave.php";
    var param="maxSave="+encodeURIComponent(maxSave);
    Edit(url,param);
}

function GetMaxValue(){
    var url="PHP/GetMaxValue.php";
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.onreadystatechange = function(){
        if (xmlHttpRequest.readyState === 4 && xmlHttpRequest.status === 200){
            SplitValue(xmlHttpRequest.responseText);
        }
    }
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.send();
}

function SplitValue(response){
    var split = response.split(",");
    var wordSize_p = $("wordSize_p");
    var maxObj_p = $("maxObj_p");
    var maxSave_p = $("maxSave_p");
    maxObj_p.textContent+=split[0];
    maxSave_p.textContent+=split[1];
    wordSize_p.textContent+=split[2];
}