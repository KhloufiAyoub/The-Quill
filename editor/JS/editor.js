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
    hide("AddMsg");
    hide("AddObj");
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

function AddRoom(){
    var roomName = $("RoomValue").value;
    var url="PHP/AddRoom.php";
    var param="room="+encodeURIComponent(roomName);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);

}

function AddMessage(){
    var msgName = $("MsgValue").value;
    var url="PHP/AddMsg.php";
    var param="msg="+encodeURIComponent(msgName);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}

function AddObject(){
    var objName = $("ObjValue").value;
    var url="PHP/AddObj.php";
    var param="obj="+encodeURIComponent(objName);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
}

function EditWord(){
    var wordSize = $("wordSize").value;
    console.log(wordSize);
    var url="PHP/EditWord.php";
    var param="wordSize="+encodeURIComponent(wordSize);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
    location.reload();
}

function EditObj(){
    var maxObj = $("maxObj").value;
    console.log(maxObj);
    var url="PHP/EditObj.php";
    var param="maxObj="+encodeURIComponent(maxObj);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
    location.reload();
}

function EditSave(){
    var maxSave = $("maxSave").value;
    var url="PHP/EditSave.php";
    var param="maxSave="+encodeURIComponent(maxSave);
    var xmlHttpRequest= new XMLHttpRequest();
    xmlHttpRequest.open("POST",url,true);
    xmlHttpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xmlHttpRequest.send(param);
    location.reload();
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
    wordSize_p.textContent+=split[2];
    maxObj_p.textContent+=split[0];
    maxSave_p.textContent+=split[1];
}