/*
 * Funkcje pomocnicze interfejsu projektów inwestycyjnych
 */

function setNNProject() {
  var s = document.getElementById('NNproject');
  var n = document.getElementById('NNprojectname');
  if (s && s.value == '-1') {
    n.style.display = 'inline-block';
  } else {
    n.style.display = 'none';
  }
 }

 function setNNOwner() {
  var s = document.getElementById('NNownership');
  var n = document.getElementById('NNcoowner');

  if (s && (s.value == '1' || s.value == '2')) {
    n.style.display = 'inline-block';
  } else {
    n.style.display = 'none';
  }

 }


function changeObjectType() {
  var type     = document.getElementById('type');
  var serial   = document.getElementById('serial');
  var producer = document.getElementById('producer');
  var model    = document.getElementById('model');
  var reserve  = document.getElementById('reserve');
  var closure  = document.getElementById('closure');
  var box      = document.getElementById('box');
  var splitter = document.getElementById('splitter');

  if (type.value == '0') {
    serial.style.display = 'none';
    producer.style.display = 'none';
    model.style.display = 'none';
    reserve.style.display = 'table-row';
    closure.style.display = 'none';
    box.style.display = 'none';
    splitter.style.display = 'none';
  } else if (type.value == '1') {
    serial.style.display = 'table-row';
    producer.style.display = 'table-row';
    model.style.display = 'table-row';
    reserve.style.display = 'none';
    closure.style.display = 'table-row';
    box.style.display = 'none';
    splitter.style.display = 'none';
  } else if (type.value == '2') {
    serial.style.display = 'table-row';
    producer.style.display = 'table-row';
    model.style.display = 'table-row';
    reserve.style.display = 'none';
    closure.style.display = 'none';
    box.style.display = 'table-row';
    splitter.style.display = 'none';
  } else if (type.value == '3') {
    serial.style.display = 'table-row';
    producer.style.display = 'table-row';
    model.style.display = 'table-row';
    reserve.style.display = 'none';
    closure.style.display = 'none';
    box.style.display = 'none';
    splitter.style.display = 'table-row';
  }
}

