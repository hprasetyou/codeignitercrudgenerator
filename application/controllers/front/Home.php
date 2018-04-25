<?php

class Home extends CI_Controller
{
    public function index()
    {
        $this->template->render('home');
    }
    public function applicant_register()
    {
        if($this->input->server('REQUEST_METHOD') == 'POST'){
          $applicant = new Applicant;
          $applicant->saveForm($this->input->post());
          $this->session->set_flashdata('success','Pendaftaran diterima, silahkan tunggu pendaftaran akun anda disetujui, anda akan diberitahu via email');

        }
        redirect('');
        $reg_help = ConfigurationQuery::create()->read_conf('registration_info_message');
        $this->template->render('front/register',array('registration_info_message'=>$reg_help));
    }
    public function tower_list(){
        $this->template->render('front/tower');
    }
}
