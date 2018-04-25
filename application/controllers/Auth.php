<?php

/**
 *
 */
class Auth extends CI_Controller
{

  public function index(){
    $this->login();
  }

  function change_password(){
    if($this->session->userdata('uid')){

      $this->template->render('admin/admin/users/change_password');
      if($this->input->post('NewPassword')){
        $string = include('application/language/id/activity_message.php');
        $this->session->set_flashdata('success',$string['activity_change_password']);
        $user = UserQuery::create()->findPk($this->session->userdata('uid'));
        $user->setPassword($this->input->post('NewPassword'));
        $user->save();
        redirect('');
      }
    }
  }

  public function login(){
    $string = include('application/language/id/activity_message.php');
    if($this->input->server('REQUEST_METHOD') == 'POST'){
      $login = UserQuery::create()
      ->findOneByName($this->input->post('Username'));
      if($login){
        if($login->checkPassword($this->input->post('Password'))){
          $this->handle_role($login);
        }
      }
      $this->session->set_flashdata('danger',$string['activity_login_failed']);


    }
    $greetings = ConfigurationQuery::create()->read_conf('greeting_message');
    $this->template->render('front/login',array('greetings'=>$greetings));
  }

  public function do_logout(){
     $newdata = array(
        'uid'  => False,
        'access' => False,
        'logged_in' => False
    );
    $this->session->set_userdata($newdata);
    redirect('auth');
  }


  private function handle_role($userdata){
    $access = array(1);
    $redir = 'back/applicant/manage_towerrecsubmissions';
    if($userdata->getRole()=='admin'){
      $redir = 'auth';
      $group = UserGroupQuery::create()->findOneByUser($userdata);
      $menus = MenuGroupQuery::create()
        ->findByGroupId($group->getGroupId());
        $access = [];
        foreach ($menus as $key => $menu) {
          # code...
          if($key == 0){
            $redir = $menu->getMenu()->getUrl();
          }
          $access[] = $menu->getMenuId();
        }

    }
    $newdata = array(
        'uid'  => $userdata->getId(),
        'access' => serialize($access),
        'logged_in' => TRUE
    );

    $dt = new DateTime();
    $now =  $dt->format('Y-m-d H:i:s');
    $userdata->setLastLogin($now)
    ->save();
    $this->session->set_userdata($newdata);
    redirect($redir);

  }


}
