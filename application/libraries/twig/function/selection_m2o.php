<?php
function selection_m2o(){
  return  new Twig_Function('selection_m2o', function ($name,$model,$val=null,$domain=null,$id = null,$text=null) {
    if(!$id){
      $id=$name;
    }
    if($text){
      $text = explode('-',$text);
    }else{
      $text = ['Name'];
    }
    $objs = "{$model}Query";
    $data = $objs::create();
    if($domain){
      foreach ($domain as $key => $value) {
        # code...
        $filfunc = "filterBy$key";
        $data->$filfunc($value);
      }
    }
    $data->find();
    $o = "<select name=\"$name\" id=\"$id\" class=\"form-control form-select\"><option value=\"\">Select</option>";
    foreach ($data as $key => $value) {
      $id = $value->getId();
      $name = "";
      foreach ($text as $kt=>$t) {
        # code...
        $f = "get$t";
        if($kt > 0){
          $name .= " - ";
        }
        $name .= $value->$f();
      }
      $selected = "";
      if($val == $id){
        $selected = "selected=\"selected\"";
      }
      $datavalue = "";
      if(method_exists($value,'getValue')){
        $datavalue .= 'data-value="'.$value->getValue().'"';
      }
      $o .="<option $selected $datavalue value=\"$id\">$name</option>";
    }
    $o .= "</select>";
    return $o;
  });
}
