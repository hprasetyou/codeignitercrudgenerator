<?php
function get_attachment(){
    return new Twig_Function('get_attachment', function ($model,$objid) {
      return    AttachmentQuery::create()
            ->filterByModel($model)
            ->filterByObjectId($objid)
            ->find();
    });

}
