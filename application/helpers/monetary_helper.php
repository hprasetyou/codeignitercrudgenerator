<?php

/**
 *
 */
 function format_currency($val,$cur_code = 'USD'){
   $curr = CurrencyQuery::create()->findOneByCode($cur_code);
   $val = number_format($val, 2, ',', '.');
   return $curr->getPlacement()=='before'?$curr->getSymbol()." ".$val:$val." ".$curr->getSymbol();
 }

 function exchange_rate($val,$target,$src="USD"){
   $target_data = CurrencyQuery::create()->findOneByCode($target);
   $rate = ExchangeRateQuery::create()
   ->filterByBase($src)
   ->filterByTarget($target)
   ->orderByCreatedAt('desc')
   ->findOne();
   if($target_data->getRounding()>0){
     $round = $target_data->getRounding();
     return round(round(($val*(($target==$src)?1:$rate->getRate()))/$round)*$round,2);
   }
   return round($val*(($target==$src)?1:$rate->getRate()),2);
 }
