<?php
use Propel\Runtime\Propel;


function create_number($setting = array('separator'=>'-','number_len'=>3)){
  /*
  i => incremental, like 001,002
  d => day
  m => month
  y => year

  */

  $separator = isset($setting['separator'])?$setting['separator']:'-';
  $format = $setting['format'];
  $number_len = isset($setting['number_len'])?$setting['number_len']:3;
  $tb_name = $setting['tb_name'];
  $tb_field = $setting['tb_field'];

  $ftpls = explode($separator,$format);
  $o = "";
  $code_len_before_num = 0;
  $code_len = 0;
  $whereclause= " where ";
  $acode = [];
  foreach ($ftpls as $key => $value) {
    # code...
    switch ($value) {
      case 'i':
        # code...
        $o .= "{number}";
        $code_len_before_num= $code_len;
        $code_len += $number_len;
        break;
      case 'd':
        # code...
        $o .= date('d');
        $whereclause .= "substring(`$tb_field`,($code_len+1),2)='".date('d')."' and ";
        $code_len += 2;
        break;
      case 'm':
        # code...
        $o .= date('m');
        $whereclause .= "substring(`$tb_field`,($code_len+1),2)='".date('m')."' and ";
        $code_len += 2;
      break;
      case 'y':
        # code...
        $o .= date('y');
        $clb = $code_len+1;
        $whereclause .= "substring(`$tb_field`,$clb,2)='".date('y')."' and ";
        $code_len += 2;
      break;
      default:
        # code...
        array_push($acode,$value);
        $o .= $value;
        $code_len += strlen($value);
        break;
    }
    if($key+1<count($ftpls)){
      $o .= $separator;
      $code_len += 1;
    }

  }
  $tcode = implode($separator,$acode);
  $tcodelen = strlen($tcode);
  $whereclause .= "substring(`$tb_field`,1,$tcodelen)='$tcode' and ";
  $whereclause .= "1";

  $code_len_before_num = $code_len_before_num+1;
  $sql = "SELECT max(substring(`$tb_field`, $code_len_before_num,$number_len))
   as max_$tb_field FROM `$tb_name` $whereclause";
      $con = Propel::getConnection();
      $stmt = $con->prepare($sql);
      $stmt->execute();
  foreach ($stmt->fetch() as $a) {
    $n = $a+1;
  }
  $zero = '';
  for ($i=0; $i < ($number_len - strlen($n)); $i++) {
    $zero .= '0';
  }
  $nbr = $zero.$n;

  // echo $o;

  return str_replace('{number}',$nbr,$o);


}
