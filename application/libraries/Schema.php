<?php

/**
 *
 */
class Schema
{

  private $file_path;

  function __construct()
  {
    # code...
    $this->file_path = "./schema.xml";

  }

  function find_table($tb_name,$ref='phpName'){
		$f = fopen($this->file_path, "r");
		$contents = fread($f, filesize($this->file_path));
		$xml= new \SimpleXMLElement($contents);
		$o = False;
		foreach ($xml->table as $table) {
			if($table->attributes()->$ref == $tb_name){
				$o = $table;
			}else{
        foreach ($table->column as $col) {
          # code...
          foreach ($col->inheritance as $inheritance) {
            # code...
            if($inheritance->attributes()->class == $tb_name){
              $o = $table;
            }
          }
        }
      }
		}
		return $o;
	}

  function extract_fields($tb_name){
    $tb = $this->find_table($tb_name);
    $o = [];
    foreach ($tb->column as $key => $value) {
      # code...
      if($value->attributes()->name && $value->attributes()->phpName){
        $o[$value->attributes()->name.""] = array(
          'type'=>$value->attributes()->type."",
          'Name'=>$value->attributes()->phpName.""
        );
      }
    }
    foreach ($tb->{'foreign-key'} as $key => $value) {
      # code...
      $relname = $value->attributes()->foreignTable;
      if($value->attributes()->phpName){
        $relname = $value->attributes()->phpName;
      }else{
        $relmodel = $this->find_table($relname."",'name');
        $relname = $relmodel->attributes()->phpName;

      }
      // echo $relmodel;
      $o[$value->reference[0]->attributes()->local.""] = array(
        'type'=>'rel',
        'Name'=> $relname,
        'LocalName'=>$value->attributes()->phpName
      );
    }
    return $o;
  }

}
