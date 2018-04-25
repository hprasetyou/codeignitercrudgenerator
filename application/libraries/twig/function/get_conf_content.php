<?php
function get_conf_content(){
  return new Twig_Function('get_conf_content',function($key){

      $conf = ConfigurationQuery::create();
      $o = $conf->findOneByName($key);
      return $o->getDataValue();
  });
}
