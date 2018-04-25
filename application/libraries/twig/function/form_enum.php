<?php
function form_enum(){
  return new Twig_Function('form_enum',function($name,$val=null){
      $this->CI->load->library('Schema');
      $m = explode('.',$name);
      $o = "<select class=\"form-select\" name=\"".$m[1]."\" id=\"".$m[1]."\" value=\"$val\">";
      foreach ($this->CI->schema->find_table($m[0])->column as $value) {
        # code...
        if($value->attributes()->phpName == $m[1]){
          $rd = preg_replace("/ENUM|'|\(|\)/","",$value->attributes()->sqlType);
          $rd = explode(',',$rd);
          foreach ($rd as $opt) {
            # code...
            $selected = $opt==$val?"selected":"";
            $o .= "<option $selected value=\"$opt\">$opt</option>";
          }
          // return $rd;
        }
      }
      $o .= "</select>";
      return $o;
  });
}
