<?php
function form_back_link(){
  return new Twig_Function('form_back_link',function () {
    $case = ['create','detail'];
    $o = '';
    foreach ($case as $key => $value) {
      if(strpos(uri_string(),$value)!== false){
        $o = explode($value,uri_string());
        $o = $o[0];
      }
    }
    return "/index.php/$o";
  });
}
