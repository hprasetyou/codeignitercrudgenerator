<?php
/**
 *
 */
class Task extends CI_Controller
{
  function run(){
    $tasks = SysTaskQuery::create();
    foreach ($this->get_one_time_task() as $key => $value) {
      # code...
      $task_name = $value->getName();
      task_run_logger("executing task $task_name . . . .");
      $value->setLastExecution(date("Y-m-d H:i:s"));
      try {
        $this->execute($value->getType(),$value->getContent());
        $value->setStatus('done');
      } catch (Exception $e) {
        $value->setStatus('fail');
        $value->setDescription($e->getMessage());
      }
      $value->save();


    }
    //find task by Scheduled Execution
    foreach ($this->get_day_repeat_task() as $key => $value) {
      # code...
      $task_name = $value->getName();
      task_run_logger("executing task $task_name . . . .");
      $this->execute($value->getType(),$value->getContent());
      $value->setLastExecution(date("Y-m-d H:i:s"))
      ->save();
    }
  }


  function execute($type,$content){
    switch ($type) {
      case 'email':
        $data = json_decode($content);
        $this->load->library('mailer');
        $this->mailer
        ->set_recipient($data->recipient)
        ->set_recipient_name($data->recipient_name)
        ->set_subject($data->subject)
        ->set_attachments($data->attachments)
        ->set_body($this->template->render($data->mail_tmpl,
        json_decode(json_encode($data->mail_tmpl_data),true),false))->send_email();
        # code...
        break;
      case 'call_func':
        $action = explode(":",$content);
        $lib = strtolower($action[0]);
        $func = $action[1];
        $this->load->library($lib);
        $this->$lib->$func();
        # code...
        break;

      default:
        # code...
        break;
    }
  }

  function get_day_repeat_task(){
    $tasks = SysTaskQuery::create()
    //check if this task must run today
    ->where('SysTask.DayRepeat like ?','%'.date('D').'%')
    //check wether we have run this task before or not today
    ->where('SysTask.LastExecution < CONCAT(date_format(NOW(),"%Y-%m-%d")," ",date_format(SysTask.TimeExecution,"%H:%i:%s")) ')
    ->find();
    return $tasks;
  }

  function get_one_time_task(){
    $tasks = SysTaskQuery::create()
    ->filterByDayRepeat(null)
    ->filterByScheduledExecution(
      array('max'=>date('Y-m-d H:i:s')))
    ->filterByStatus('wait')
    ->find();
    return $tasks;
  }
}
