<?php
function cb_m2o(){
    return new Twig_Function('cb_m2o', function ($name,$model,$val= null,$domain=null,$id = null,$text=null) {
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
      $o = "";
      foreach ($data as $key => $value) {
        $id = $value->getId();
        $label = $value->getName();
        $selected = "";
        if($val == $id){
          $selected = "checked=\"checked\"";
        }

        $o .="<label><input type=\"checkbox\" $selected name=\"{$name}[{$id}]\">$label</label>";
      }
      return $o;
    });

}
