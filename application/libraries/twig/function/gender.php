<?php
function gender(){
  return new Twig_Function('gender',function($code){
      $o = ($code == 'f')?'Wanita':'Pria';
      return $o;
  });
}
