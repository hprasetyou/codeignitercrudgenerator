<?php

/**
 *
 */
class Exchange_rate
{

  function get_update(){

    $currlist = CurrencyQuery::create()->find();
    foreach ($currlist as $base_curr) {
      # code...
      $base = $base_curr->getCode();
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,"https://api.fixer.io/latest?base=$base");
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
      $output=curl_exec($ch);
      curl_close($ch);
      $output = json_decode($output);
      foreach ($currlist as $targetcurr) {
        $target = $targetcurr->getCode();
        write_log($target);
        $rate = ExchangeRateQuery::create()
        ->filterByBase($base)
        ->filterByTarget($target)
        ->filterByCreatedAt(
          array('min'=>date('Y-m-d 00:00:00')));
        if($rate->count()<1){
          if(property_exists($output->rates,$target)){
          $rate = new ExchangeRate();
          $rate->setBase($base)
          ->setName("$base $target")
          ->setTarget($target)
          ->setRate($output->rates->$target)
          ->save();
        }
        }
      }
    }

    return true;
  }
}
