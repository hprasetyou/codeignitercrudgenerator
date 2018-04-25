<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
v1.0
This is library for accessing twig, make sure you have intall twig via composer
and enable composer autoloading
*/
use Dompdf\Dompdf;

class Template {
  public function __construct()
  {
      $loader = new Twig_Loader_Filesystem('./application/views');
      $this->twig = new Twig_Environment($loader);
      $this->twig = new Twig_Environment($loader, array(
      'cache' => false,// '/application/cache'
      ));
      $this->CI =& get_instance();
      $this->apply_filter();
      $this->apply_func();
  }

  function apply_filter(){
    foreach (glob("application/libraries/twig/filter/*.php") as $filename)
  	{
  		include $filename;
  		$f = explode('.',basename($filename));
  		$func = $f[0];
      $myfunc = $func();
      $this->twig->addFilter($myfunc);
  	}
  }

  function apply_func(){
    $ci = $this->CI;
    $this->twig->addFunction(new Twig_Function('uri_segment',function($index) use ($ci){
      return $ci->uri->segment($index);
    }));
	foreach (glob("application/libraries/twig/function/*.php") as $filename)
	{
		include $filename;
		$f = explode('.',basename($filename));
		$func = $f[0];
    $myfunc = $func();
    $this->twig->addFunction($myfunc);
	}

  }

  private $twig;
  function render($tpl,$data=array(),$render = true){

        $this->CI->load->helper('url');
        $session = [];
        if($this->CI->session->userdata('logged_in')){
          $uaccess = MenuQuery::create()
          ->findById(unserialize($this->CI->session->userdata('access')));
          $access = [];
          foreach ($uaccess as $key => $value) {
            # code...
            $exist = false;
            $parent = false;
            foreach ($access as $parentmenu) {
              if($parentmenu->getId()==$value->getParent()->getId()){
                //exist
                $exist = true;
                $parentmenu->Child[] = $value;
              }
            }
            if(!$exist){
              $access[$key] = $value->getParent();
              $access[$key]->Child[] = $value;
            }
          }
          $session['access'] = $access;
          $session['uid'] = UserQuery::create()->findOneById($this->CI->session->userdata('uid'));
        }
        $pdata = array(
            'base_url'=>base_url(),
            'res'=>array(
                'string'=>include('application/language/id/default.php'),
            		'uri'=>uri_string(),
            		'query_params'=>$this->CI->input->get(),
                'session' => $session,
                'info' => $this->CI->session->flashdata()
            )
        );
        $out = array_merge($pdata,$data);
        if(!$render){
          return $this->twig->render($tpl.'.html',$out);
        }else{
          echo $this->twig->render($tpl.'.html',$out);
        }
    }


    public function render_pdf($tpl, $data = array() ,$config = array('docname'=>'document','header'=>'nota','render'=>true))
    {
        $dompdf = new Dompdf();
        $out = $data;
        // $out['header'] = 'nota';

        $dompdf->loadHtml($this->render($tpl, $out,false));
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        $docname = $config['docname'];
        if($config['render']){
          $dompdf->stream("$docname.pdf" , array( 'Attachment'=>0 ) );
        }else{
          $path = "public/tmp/";
          $filepath = "$path$docname.pdf";
          file_put_contents($filepath, $dompdf->output());
          return array(
            'name' => "$docname.pdf",
            'path' => $filepath);
        }
    }
}
