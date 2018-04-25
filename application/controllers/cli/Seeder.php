<?php

class Seeder extends CI_Controller
{


    function __construct()
    {
      # code...
      parent::__construct();
      if(!$this->input->is_cli_request())
      {
        echo 'Not allowed';
        exit();
      }

    }

    private function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    function seed(){
      $objs = [
        'SysTask',
        'Group',
        'Partner',
        'User',
        'UserGroup',
        'Menu',
        'MenuGroup',
        'SysTask',
        'UnitOfMeasureCategory',
        'UnitOfMeasure'
      ];
      foreach ($objs as $obj) {
        # code...
        $path = "application/data/$obj.json";
        if(file_exists($path)){
          echo "seeding $obj ";
          $data = json_decode(file_get_contents($path));
          $succ = 0;
          $skip = 0;
          foreach ($data as $object) {
            $model = "{$obj}Query";
            $m = $model::create();
            if(property_exists($object,'id')){
              $m = $m->findPk($object->id);
            }else{
              foreach ($object as $prop => $val) {
                # code...
                $attr = $this->camelize($prop);
                $f = "filterBy$attr";
                $m->$f($val);
              }
              $m->findOne();
            }
            if(!$m){
              $m = new $obj;
              foreach ($object as $prop => $val) {
                # code...
                $attr = $this->camelize($prop);
                if($prop != 'id'){
                  $f = "set$attr";
                  $m->$f($val);
                }
              }
              $m->save();
              if(property_exists($object,'id')){
                $m = $m->setId($object->id);
                $m->save();
              }
              $succ++;
            }else{
              $skip++;
            }
            echo ".";

          }
          echo "\n\n Insert $succ data, skip $skip data \n\n";
        }
      }
    }
}
