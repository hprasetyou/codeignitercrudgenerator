<?php
function button(){
  return new Twig_Function('button', function ($conf) {
    $url = isset($conf['url'])?$conf['url']:'#';
    $icon = isset($conf['icon'])?"fa fa-".$conf['icon']:'';
    $type = isset($conf['type'])?$conf['type']:"default";
    $id = isset($conf['id'])?'id="'.$conf['id'].'"':"";
    $right = isset($conf['right'])?'pull-right':"";
    $text = $conf['text'];

    return "<a $id href=\"$url\" class=\"btn $right btn-$type\"><i class=\"$icon\"></i>$text</a>";
  });
}
