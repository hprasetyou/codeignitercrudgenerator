<?php

function queue_message($config){
  $task = new SysTask();
  $now = date('Y/m/d H:i:s ', time());
  $string = include('application/language/en/activity_message.php');

  $task->setName("send email ".$now)
  ->setPriority(1)
  ->setContent(json_encode($config))
  ->setType('email')
  ->save();
  $CI =& get_instance();
  $CI->session->set_flashdata('info',$string['email_sent']);
}
