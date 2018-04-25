<?php
//TODO: finish this
use Doctrine\Common\Inflector\Inflector;

class MY_Controller extends CI_Controller
{
    protected $objname;
    protected $objobj;
    protected $tpl;
    protected $o2m_def;
    protected $form;
    protected $custom_column;
    protected $custom_code;
    protected $outputstd;
    protected $user_role;
    protected $pass_method;

    public function __construct()
    {
        if (!$this->user_role) {
            $this->user_role = 'admin';
        }
        parent::__construct();
        $ctrl = $this->router->fetch_class();
        $act = $this->router->fetch_method();
        write_log("Calling controller $ctrl action $act");
        $this->custom_column = [];
        $this->o2m_def = [];
        $this->form = [];
        $pass= true;
        if ($this->session->uid) {
            $user = UserQuery::create()->findPk($this->session->uid);
            $uname = $user->getName();
            if ($this->user_role != $user->getRole()) {
                $pass = false;
                write_log(" false role");
            } else {
                if ($user->getRole() == "admin") {
                    //find access
                    $usergroup = $user->getUserGroups()[0]->getGroup();
                    $access = MenuGroupQuery::create()
        ->filterByGroupId($usergroup->getId())
        ->useMenuQuery()
        ->filterByController($ctrl)
        ->endUse()
        ->findOne();
                    if (!$access) {
                        write_log("user have no access");
                        $pass = false;
                    } else {
                        $write_act = ['create','write','delete'];
                        if (in_array($act, $write_act)) {
                            if ($access->getAccess()!= 'write') {
                                write_log("user have no write access");
                                $pass = false;
                            }
                        }
                    }
                }
            }
        } else {
            $pass = false;
        }
        if ($this->pass_method) {
            if (in_array($act, $this->pass_method)) {
                $pass = true;
            }
        }
        // echo in_array($this->router->fetch_method(),$this->pass_method);
        if (!$pass) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $vars = $this->tpl;
        $this->template->render("admin/$vars/index");
    }

    protected function set_objname($objname)
    {
        $this->objname = $objname;
        $colls = $this->schema->extract_fields($this->objname);
        $except = array('Id','CreatedAt','UpdatedAt');
        foreach ($colls as $coll) {
            # code...
            if (!in_array($coll['Name'], $except)) {
                if (!is_object($coll['Name'])) {
                    $this->form[$coll['Name']] = $coll['Name'];
                } else {
                    //  $this->form[$coll['LocalName']] = $coll['LocalName'];
                }
            }
        }
    }

    public function get_json()
    {
        $qobj = $this->objname."Query";
        $objs = $qobj::create();
        if ($this->objobj) {
            $objs = $this->objobj;
        }
        $maxPerPage = $this->input->get('length')>0?$this->input->get('length'):100;
        $colls = $this->schema->extract_fields($this->objname);
        $fields = json_decode($this->input->get('fields'));
        $except = array('image');
        $cond = [];
        if ($this->input->get('search[value]')) {
            foreach ($fields as $key => $value) {
                if (!in_array($value, $except)) {
                    if (isset($colls[$value])) {
                        $cond[] = $value;
                        $obj = $this->schema->find_table($this->objname);
                        $obj = $obj->attributes()->phpName;
                        $objs->condition($value, "$obj.$value LIKE ?", "%".$this->input->get('search[value]')."%");
                    }
                }
            }
            $objs->where($cond, 'or');
        }
        //handle domain
        foreach ($this->form as $k => $v) {
            if ($this->input->get($k)) {
                $flt = "filterBy$k";
                $objs->$flt($this->input->get($k));
                write_log("Apply filter $k");
            }
        }

        try {
            $order_index = $this->input->get('order[0][column]');
            if (!$order_index) {
                $order_index = 0;
            }
            $orderbycol = "orderBy".$fields[$order_index];
            $objs->$orderbycol($this->input->get('order[0][dir]'));
        } catch (Exception $e) {
        }
        if (method_exists($objs, 'filterByActive')) {
            $objs->filterByActive(true);
        }
        $offset = ($this->input->get('start')?$this->input->get('start'):0);
        $objs = $objs->paginate(($offset/10)+1, $maxPerPage);
        $o = [];
        $o['recordsTotal']=$objs->getNbResults();
        $o['recordsFiltered']=$objs->getNbResults();
        $o['draw']=$this->input->get('draw');
        $o['data']=[];
        $i=0;
        foreach ($objs as $obj) {
            foreach ($colls as $key => $coll) {
                # code...
                $f = "get".$coll['Name'];
                switch ($coll['type']) {
           case 'rel':
           if ($obj->$f()) {
               try {
                   $o['data'][$i][$key] = $obj->$f()->getName();
               } catch (Exception $e) {
                   $o['data'][$i][$key] =  $obj->$f()->getId();
               }
           } else {
               $o['data'][$i][$key] = "";
           }
             break;
           case 'DATE':
           $o['data'][$i][$key] = $obj->$f()?date_format($obj->$f(), 'd M Y'):'';
             break;
           default:
            $o['data'][$i][$key] = $obj->$f();
             break;
         }
            }
            eval($this->custom_code);
            foreach ($this->custom_column as $key => $value) {
                # code...
                if ($value) {
                    //extract all {variable} from input to array
                    $var_cols = extract_surround_text($value, '_{', '}_');
                }
                //create new variable and assign value by field
                foreach ($var_cols as $v) {
                    $fsv = "get".$v;
                    $$v = $obj->$fsv();
                    # code...
                }
                $value = str_replace('}_', '', $value);
                $value = str_replace('_{', '$', $value);
                eval("\$value = $value;");
                $o['data'][$i][$key] =  is_callable($value)?$value():$value;
                //apply by replacing {variable} to $variable
            }
            foreach ($this->o2m_def as $key => $value) {
                $f = "get".$value['rel'];
                $field = "get".$value['field'];
                $val = false;
                if ($value['single']) {
                    $val = count($obj->$f())>0?$obj->$f()[0]->$field():'';
                } else {
                    $val = $obj->$f();
                }
                $o['data'][$i][$value['index']] = $val;
            }
            $i++;
        }

        echo json_encode($o);
    }

    public function create()
    {
        $vars = $this->tpl;
        $this->template->render("admin/$vars/form", array(
     ));
    }

    public function detail($id, $render = "html")
    {
        $vars = $this->tpl;
        if (!$this->objobj) {
            $qobj = $this->objname."Query";
            $objs = $qobj::create();
        } else {
            $objs = $this->objobj;
        }
        $o = $this->outputstd?json_decode($objs->findPK($id)->toJSON()):$objs->findPk($id);
        if ($this->input->is_ajax_request()) {
            echo($this->outputstd?json_encode($o):$o->toJSON());
        } else {
            $render_path = "form";
            $render_func = "render";
            if ($render=="pdf") {
                $render_path = "pdf/template";
                $render_func = "render_pdf";
            }
            $this->template->$render_func(
         "admin/$vars/$render_path",
           array($this->objname=> $o
             )
           );
        }
    }

    public function write($id=null)
    {
        $pair = $this->form;
        $colls = $this->schema->extract_fields($this->objname);

        if ($id) {
            $qq = $this->objname."Query";
            $obj = $qq::create()->findPK($id);
        } else {
            $qq = $this->objname;
            $obj = new $qq;
        }

        foreach ($pair as $key => $value) {
            # code...
            $type = false;
            foreach ($colls as $ckey => $cvalue) {
                if ($cvalue['Name'] == $key) {
                    $type = $cvalue['type'];
                }
            }
            $func = "set$key";
            if ($value=="Image") {
                if (file_exists('.'.$obj->getImage())) {
                    write_log('Delete file');
                    unlink('.'.$obj->getImage());
                } else {
                    write_log('File not found');
                }
                if (strpos($this->input->post('Image'), 'base64')) {
                    $this->load->helper('base64toimage');
                    $obj->$func(base64_to_img($this->input->post($value)));
                }
            } else {
                $value = is_array($value)?$value['value']:$this->input->post($value);
                if ($type == 'BOOLEAN') {
                    $obj->$func($value?true:false);
                } else {
                    if ($value) {
                        $obj->$func($value);
                    }
                }
            }
        }
        if (method_exists($obj, 'setActive')) {
            $obj->setActive(true);
        }
        $obj->save();
        write_log("Writing data. . . . . . . ");
        write_log($obj);
        $this->loging->add_entry($this->objname,$obj->getId(),($id?'activity_modify':'activity_create'));
        return $obj;
    }

    public function delete($id)
    {
        $qobj = $this->objname."Query";
        $objs = $qobj::create()->findPk($id);
        if ($this->input->post('confirm')) {
            $this->loging->add_entry($this->objname,$objs->getId(),'activity_delete');
            $objs->delete();
        }
        return $objs;
    }


    public function set_status($status,$id,$with_email = null)
    {
        $qobj = $this->objname."Query";
        $data = $qobj::create()
         ->filterById($id)
         ->findOne();
         $this->loging->add_entry($this->objname,$data->getId(),'activity_accept');
        if ($data) {
            if($with_email){

                $this->load->helper('send_mail');
                queue_message($with_email);

            }
            $data->setStatus($status)
            ->save();
            if ($this->input->is_ajax_request()) {
              echo $data->toJSON();
            }else{
              redirect(str_replace("set_status/$status",'detail',uri_string()));
            }
        } else {
            echo json_encode(array('error','data not found'));
        }
    }
    public function import_data()
    {
        $config['upload_path']          = './public/upload/tmp/';
        $config['allowed_types']        = 'xls|xlsx|csv';

        $this->load->library('upload', $config);

        if (! $this->upload->do_upload('importfile')) {
            echo $this->upload->display_errors();
        } else {
            $data = array('upload_data' => $this->upload->data());
            $file = $this->upload->data();
            $this->load->library('import_spreadsheet');
            $excel = $this->import_spreadsheet->import($file['full_path'],$this->objname);
        }
        redirect(str_replace('import_data','',uri_string()));
    }
    public function import_template()
    {
        $this->load->library('import_spreadsheet');
        $excel = $this->import_spreadsheet->get_template($this->objname);
        header('Location: /'.$excel);
    }
}
