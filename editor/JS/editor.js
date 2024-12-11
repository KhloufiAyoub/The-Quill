init();

function init(){
    $("RoomButton").onclick = ShowRoom;
    $("MsgButton").onclick = ShowMessage;
    $("ObjButton").onclick = ShowObject;
    $("MoveButton").onclick = ShowMove;
    $("VocabButton").onclick = ShowVocab;


    $("RoomSubmit").onclick = AddRoom;
    $("RoomCancel").onclick = function () {hide("AddRoom");};
    $("MsgSubmit").onclick = AddMessage;
    $("MsgCancel").onclick = function () {hide("AddMsg");};
    $("ObjSubmit").onclick = AddObject;
    $("ObjCancel").onclick = function () {hide("AddObj");};
    $("VocabSubmit").onclick = AddVocab;
    $("VocabCancel").onclick = function () {hide("AddVocab");};
    $("MoveSubmit").onclick = AddMove;
    $("MoveCancel").onclick = function () {hide("AddMove");};

    $("submitWord").onclick = EditWord; // reverif
    $("submitObj").onclick = EditObj;
    $("submitSave").onclick = EditSave;

    $("GetRoom").onclick = GetRoom;
    $("GetMessage").onclick = GetMessage;
    $("GetSmsg").onclick = GetSmsg;
    $("GetMove").onclick = GetMove;
    $("GetObject").onclick = GetObject;
    $("GetVocab").onclick = GetVocab;

    GetMaxValue();
}

function ShowRoom(){
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    show("AddRoom");
}

function ShowMessage(){
    hide("AddRoom");
    hide("AddObj");
    hide("AddVocab");
    hide("AddMove");
    show("AddMsg");
}

function ShowObject(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddVocab");
    hide("AddMove");
    show("AddObj");
}

function ShowVocab(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddMove");
    show("AddVocab");
}

function ShowMove(){
    hide("AddRoom");
    hide("AddMsg");
    hide("AddObj");
    hide("AddVocab");
    show("AddMove");

    var maDiv = $("MoveSelect");
    maDiv.innerHTML = "";
    Promise.all([
        GetValueArray("PHP/GetRoom.php"),
        GetValueArray("PHP/GetVocab.php"),
        GetValueArray("PHP/GetRoom.php")
    ]).then(function(arrays) {
        var label = document.createElement("LABEL");
        label.textContent = "Déplacement : ";
        label.htmlFor = "rid";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[0], "roomdesc", "rid", false, [], "rid");
        maDiv.appendChild(document.createElement("BR"))
        label = document.createElement("LABEL");
        label.textContent = "Mot : ";
        label.htmlFor = "wid";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[1],"word", "wid",  false, [],"wid");
        maDiv.appendChild(document.createElement("BR"))
        label = document.createElement("LABEL");
        label.textContent = "Nouvelle pièce : ";
        label.htmlFor = "newroom";
        maDiv.appendChild(label);
        InsertSelect(maDiv, arrays[2],"roomdesc", "rid", false, [],"newroom");
        maDiv.appendChild(document.createElement("BR"))
    });
}

function Add(url,param, element){
    element.value="";
    var xhr= new XMLHttpRequest();
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function AddRoom(){
    var roomName = $("RoomValue");
    var url="PHP/AddRoom.php";
    var param="room="+encodeURIComponent(roomName.value);
    Add(url,param,roomName);
}

function AddMessage(){
    var msgName = $("MsgValue");
    var url="PHP/AddMsg.php";
    var param="msg="+encodeURIComponent(msgName.value);
    Add(url,param,msgName);
}

function AddObject(){
    var objName = $("ObjValue");
    var url="PHP/AddObj.php";
    var param="obj="+encodeURIComponent(objName.value);
    Add(url,param,objName);
}

function AddVocab(){
    var vocabNum = $("VocabNum");
    var vocabValue = $("VocabValue");
    var url="PHP/AddVocab.php";
    var param="wid="+encodeURIComponent(vocabNum.value)+ "&word=" + encodeURIComponent(vocabValue.value);
    Add(url,param,vocabNum);
}

function AddMove(){
    var rid = $("rid");
    var wid = $("wid");
    var newroom = $("newroom");
    var url="PHP/AddMove.php";
    var param="rid="+encodeURIComponent(rid.value)+ "&wid=" + encodeURIComponent(wid.value) + "&newroom=" + encodeURIComponent(newroom.value);
    Add(url,param,rid);
}

function Edit(url,param){
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            GetMaxValue();
        }
    }
    xhr.open("POST",url,true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send(param);
}

function EditWord(){
    var wordSize = $("wordSize").value;
    var url="PHP/EditWord.php";
    var param="wordSize="+encodeURIComponent(wordSize);
    Edit(url,param);
}

function EditObj(){
    var maxObj = $("maxObj").value;
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
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            SplitValue(xhr.responseText);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function SplitValue(response){
    var split = response.split(",");
    var wordSize_p = $("wordSize_p");
    var maxObj_p = $("maxObj_p");
    var maxSave_p = $("maxSave_p");
    wordSize_p.innerHTML="";
    maxObj_p.innerHTML="";
    maxSave_p.innerHTML="";
    wordSize_p.append(document.createTextNode("Longueur maximum d'un mot : " + split[2]));
    maxObj_p.append(document.createTextNode("Nombre maximum d'objets dans l'inventaire : " + split[0]));
    maxSave_p.append(document.createTextNode("Nombre maximum de sauvegarde : " + split[1]));
    $("wordSize").value= split[2];
    $("maxObj").value= split[0];
    $("maxSave").value=split[1];
}

function Get(url, elements, promptvalue= null, isDeletable=true, isEditable=true, UseInnerHTML=false){
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequest(xhr.responseText, elements, promptvalue, isDeletable, isEditable, UseInnerHTML);
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function GetRoom(){
    var url="PHP/GetRoom.php";
    Get(url,["rid","roomdesc", "Piece"],"Nouvelle description de la pièce :" ,true,true,true);
}

function GetMessage(){
    var url="PHP/GetMessage.php";
    Get(url,["mid","message", "Message"], "Nouveau message :",true,true,true);
}

function GetSmsg(){
    var url="PHP/GetSmsg.php";
    Get(url,["smid","message", "SMessage"], null,false, false, true)
}

function GetMove(){
    var url="PHP/GetMove.php";
    Get(url,["rid","word", "newroom", "Deplacement"],null , true,false)
}

function GetVocab(){
    var url="PHP/GetVocab.php";
    Get(url,["wid","word", "Vocab"], null, true,false)
}

function ProcessRequest(str,elements, promptvalue, isDeletable, isEditable, UseInnerHTML){
    var result = JSON.parse(str);
    var el=document.getElementById("maDiv");
    var i,j,tbl,tr;
    el.innerHTML="";
    tbl=document.createElement("TABLE");
    //Entête du tableau
    tr = document.createElement("TR");
    for (j=0;j<elements.length-1;j++){
        InsertColumn(tr, elements[j]);
    }
    tbl.appendChild(tr);
    //Corps du tableau
    for(i=0;i<result.length;i++) {
        tr=document.createElement("TR");
        //Insertion des colonnes
        for (j=0;j<elements.length-1;j++) {
            InsertColumn(tr, result[i][elements[j]],UseInnerHTML);
        }
        //Insertion des images de suppression
        if (isDeletable) {
            if(elements[elements.length-1] === "Deplacement") {
                InsertDeleteImg(tr, result[i][elements[0]],  elements[elements.length-1], result[i][elements[1]])
            }else{
                InsertDeleteImg(tr, result[i][elements[0]], elements[elements.length-1]);
            }
        }
        //Insertion des images d'édition
        if (isEditable) {
            InsertEditImg(tr, result[i][elements[0]], elements[elements.length-1], promptvalue);
        }
        tbl.appendChild(tr)
    }
    el.appendChild(tbl);
}

function GetObject(){
    var url="PHP/GetObj.php";
    var xhr= new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4 && xhr.status === 200){
            ProcessRequestObj(xhr.responseText,["objid","objdesc", "Objet"], ["startloc", "wid"], "Nouveau nom de l'objet :");
        }
    }
    xhr.open("POST",url,true);
    xhr.send();
}

function ProcessRequestObj(str, elements, elements2, promptvalue) {
    var j;
    var result = JSON.parse(str);
    var el = document.getElementById("maDiv");
    el.innerHTML = "";
    var tbl = document.createElement("TABLE");
    var tr = document.createElement("TR");
    for (j = 0; j < elements.length-1; j++) {
        InsertColumn(tr, elements[j]);
    }
    for (j = 0; j < elements2.length; j++) {
        InsertColumn(tr, elements2[j]);
    }
    tbl.appendChild(tr);

    result.forEach(function(item) {
        var tr = document.createElement("TR");
        var td = document.createElement("TD");
        for (j=0;j<elements.length-1;j++) {
            InsertColumn(tr, item[elements[j]]);
        }

        Promise.all([
            GetValueArray("PHP/GetRoom.php"),
            GetValueArray("PHP/GetVocab.php")
        ]).then(function(arrays) {
            InsertSelect(td, arrays[0], "roomdesc", "rid", true, ["Non créé","Transporté", "Porté"]);
            tr.appendChild(td);
            td = document.createElement("TD");
            InsertSelect(td, arrays[1], "word", "wid", true, ["-----"]);
            tr.appendChild(td);
            InsertDeleteImg(tr, item[elements[0]], elements[elements.length-1]);
            InsertEditImg(tr, item[elements[0]], elements[elements.length-1], promptvalue);
            tbl.appendChild(tr);
        });
    });

    el.appendChild(tbl);
} // a revoir

function GetValueArray(url) {
    return new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    var result = JSON.parse(xhr.responseText);
                    resolve(result);
                } else {
                    reject("Error: " + xhr.status);
                }
            }
        };
        xhr.open("POST", url, true)
        xhr.send();
    });
}

function InsertEditImg(tr,id, table, promptvalue){
    var img = document.createElement("IMG");
    var td = document.createElement("TD");
    img.src="../img/icone-edit.png";
    img.onclick = InsertEditHelper(id, table, promptvalue)
    td.appendChild(img);
    tr.appendChild(td);
}

function InsertEditHelper(id, table, promptvalue) {
    return function() {
        var newValue = prompt(promptvalue);
        if (newValue !== null) {
            var url = "PHP/Edit.php";
            var param = "id1=" + encodeURIComponent(id) + "&table=" + encodeURIComponent(table) + "&newValue=" + encodeURIComponent(newValue);
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    Refresh(xhr.response)
                }
            };
            xhr.open("POST", url, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(param);
        }
    };
}

function InsertDeleteImg(tr, id1, table, id2=null){
    var img = document.createElement("IMG");
    var td = document.createElement("TD");
    img.src="../img/icone-delete.png";
    img.onclick =  InsertDeleteHelper(id1, id2, table)
    td.appendChild(img);
    tr.appendChild(td);
}

function InsertDeleteHelper(id1, id2, table){
    return function(){
        var url="PHP/Delete.php";
        var param="id1="+encodeURIComponent(id1)
        param = param + "&id2=" + encodeURIComponent(id2)
        param = param + "&table=" + encodeURIComponent(table)
        var xhr= new XMLHttpRequest();
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4 && xhr.status === 200){
                Refresh(xhr.response)
            }
        }
        xhr.open("POST",url,true);
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        xhr.send(param);
    }
}

function Refresh (str){
    switch(str){
        case "piece":
            GetRoom();
            break;
        case "message":
            GetMessage();
            break;
        case "deplacement":
            GetMove();
            break;
        case "vocab":
            GetVocab();
            break;
        case "objet":
            GetObject();
            break;
    }
}

//TODO : Pas de InnerHTML

function InsertColumn(tr, item, UseInnerHTML){
    var td = document.createElement("TD");
    item = String(item).trunc(80, " ", true);
    if (UseInnerHTML){
        td.innerHTML=item;
    }else {
        var tn = document.createTextNode(item);
        td.appendChild(tn);
    }
    tr.appendChild(td);
}

//TODO : select réagit avec l'id de la ligne dans une closure, avec avec .onchanged = function(ev){target = ev.target}


function InsertSelect(td, array, text, value, onChanged, moreOption=[], id=null){

    var select = document.createElement("SELECT");
    if (onChanged){

    }else{
        select.id = id;
    }

    var i;
    var option = document.createElement("OPTION");
    for (i=0; i<moreOption.length; i++){
        option = document.createElement("OPTION");
        option.value = moreOption[i];
        option.text = moreOption[i];
        select.appendChild(option);
    }
    for (i = 0; i < array.length; i++) {
        option = document.createElement("OPTION");
        option.value = array[i][value];
        option.text = String(array[i][text]).trunc(30, " ", true);
        select.appendChild(option);
    }
    td.appendChild(select);
}







