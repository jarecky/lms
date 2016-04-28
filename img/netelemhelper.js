/*
 * Funkcje xajax do netelement add
 */

function change_producer(elem,type)
            {
                //var model = document.getElementById('model');
                //model.style.display = 'none';
                xajax_select_producer(elem.value, type.value);
            }
function getProducerByType(type){}
function update_models(data)
            {

                var i, len;
                var models_select = document.getElementById('model_sel');

                models_select.options.length = 0;
                for (i = 0, len = data.length; i < len; i++) {
                    models_select.options[models_select.options.length] = new Option(data[i].name, data[i].id);
                }
                if (len > 0) {
                    var models_row = document.getElementById('model');
                    models_row.style.display = '';
                }

                resize_frame();
            }
function change_models(elem)
            {
                //alert(elem.value);
                xajax_select_model(elem.value);
            }
function update_ports(data){
                var port_types='<option value=0>port0</option>';//smarty rulez
                var connectors='<option value=0>conn0</option>';//smarty rulez
                var tr,slabel,tr,i,x,t=0,a;
                var dst=document.getElementById('porttable');
                dst.innerHTML='';
                for(i=0; i<data.length; i++){
                    for(x=1; x<= data[i].portcount; x++){
                        tr=document.createElement('tr');
                        slabel=data[i].label.replace("#",x+t);
                        tr.innerHTML='<td>'+index+']{trans("Label:")}'+
        '<input type=text name="netelem['+index+'][label]" value=""> typ:<select name="netelem['+index+'][typ]"><option selected>typ_portu</option>'+port_types+'</select>'+
        ' {trans("connector")}:<select name="netelem['+index+'][connector]"><option selected>connector1</option>'+connectors+'</select>'+
        '<IMG src="img/add.gif" alt="" title="{trans("Clone")}" onclick="clone(this);">&nbsp;'+
        '<IMG src="img/delete.gif" alt="" title="{trans("Delete")}" onclick="remports(this);">'+
        '</td>';
                        dst.appendChild(tr);
                        a=x;
                    }
                    //t=a;
                    if(data[i].continous=='0')t=0; else t=a;
                }
            }
var medium_types; //typy mediow zmieniane przy wyborze typu urzadzenia

function addports(){
  var port_types=medium_types;//smarty rulez
  var connectors='<option value=0>conn0</option>';//smarty rulez
  var node=document.getElementById('porttable');
  var row=document.createElement('tr');
  var index=document.getElementById('porttable').childNodes.length+1;
  row.innerHTML='<td>'+index+']{trans("Label:")}'+
        '<input type=text name="netelem['+index+'][label]" value=""> typ:<select name="netelem['+index+'][typ]"><option selected>typ_portu</option>'+port_types+'</select>'+
        ' {trans("connector")}:<select name="netelem['+index+'][connector]"><option selected>connector1</option>'+connectors+'</select>'+
        '<IMG src="img/add.gif" alt="" title="{trans("Clone")}" onclick="clone(this);">&nbsp;'+
        '<IMG src="img/delete.gif" alt="" title="{trans("Delete")}" onclick="remports(this);">'+
        '</td>';
  node.appendChild(row);
}

function clone(src){
  var ports=document.getElementById('porttable');
  var nrow=document.createElement('tr');
  var nindex=ports.childNodes.length+1;
  nrow.innerHTML=src.parentNode.parentNode.innerHTML.replace(/[0-9]+\]/g,nindex+']');
  ports.insertBefore(nrow,src.parentNode.parentNode.nextSibling);
  for(x=0; x<ports.childNodes.length; x++){
    ports.childNodes[x].innerHTML=ports.childNodes[x].innerHTML.replace(/\d+\]/g,x+']');
  }
  ports.insertBefore(nrow,src.parentNode.parentNode.nextSibling);

}
function remports(row){
  row.parentNode.parentNode.parentNode.removeChild(row.parentNode.parentNode);
}

function netelemmodelchoosewin(type, producer, model, porttable){
    popup('?m=choosemodel&type='+type,1,100,380);
    autoiframe_setsize('autoiframe', 300, 300);
}
