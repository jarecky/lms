<?php
$layout['pagetitle'] = trans('New Device <i>example</i>');


$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");
$SMARTY->assign('NNnodes',$netnodes);


$SMARTY->display("netelements/netelementadd.html");
?>
