<?php
function get_letterhead(){
  return new Twig_Function('get_letterhead',function(){

      $conf = ConfigurationQuery::create();
      $o = $conf->findOneByName('letterhead');
      return $o->getDataValue();
  });
}
