{{ $tb_name{{res.string.edit_data}}\t\t<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Doctrine\Common\Inflector\Inflector;

class Scaffold extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function translation($key_trans,$trans)
	{
		$langlst = ['id','en'];
		foreach ($langlst as $l) {
			# code...
			$words = include("application/language/$l/default.php");
			$lang_file = fopen("./application/language/$l/default.php", "w") or die("Unable to open file!");
			fwrite($lang_file,"<?php \n return array(\n");
			foreach ($words as $key => $word) {
				if($key != $key_trans){
					fwrite($lang_file,"'$key' => '$word',\n");
				}

			}
			fwrite($lang_file,"'$key_trans' => '$trans'\n);");
			fclose($lang_file);
		}
	}

  public function generate($tb_name, $menu=false, $fk=null){
	    $var = Inflector::pluralize(strtolower($tb_name));
			if($menu=="yes"){
				$menu = new Menu();
				$menu->setName($var);
				$menu->setUrl("manage_$var");
				$menu->setIcon("fa fa-cube");
				$menu->setController("manage_$var");
				$menu->setParentId(3);
				$menu->save();
			}
		$table = $this->schema->find_table($tb_name);
		if($table){
			$fklist = [];
			if($fk){
				$f = explode("-",$fk);
				$i=0;
				foreach ($f as $key => $value) {
					$fkdata = explode(".",$value);
					$fklist[$i]['field'] = $fkdata[0];
					$fklist[$i]['model'] = $this->find_table($fkdata[1]);
					$fklist[$i]['display'] = "id";
					$fklist[$i]['id'] = "id";
					foreach ($fklist[$i]['model'] as $key => $value) {
						if (preg_match('/nama|name/',$value->attributes()->name)){
							$fklist[$i]['display'] = $value->attributes()->phpName;
						}
						if ($value->attributes()->primaryKey){
							$fklist[$i]['id'] = $value->attributes()->name;
						}
					}
					$i++;
				}
			}

			$plural_var = Inflector::pluralize(strtolower($table->attributes()->phpName));
			$singular_var = Inflector::singularize(strtolower($table->attributes()->phpName));
			//read table data
			$controllers_file = fopen('./application/controllers/Manage_'.strtolower($plural_var).'.php', "w") or die("Unable to open file!");
			fwrite($controllers_file, $this->_controller_template($table->attributes()->phpName,$table->column,$fklist));
			fclose($controllers_file);
			//create view folder
			if(!is_dir('./application/views/admin/')){
				mkdir('./application/views/admin',0777);
				mkdir('./application/views/admin/'.$plural_var,0777);
			}else{
				if(!is_dir('./application/views/admin/'.strtolower($plural_var))){
					mkdir('./application/views/admin/'.strtolower($plural_var),0777);
				}
			}

			$this->_generate_view($tb_name,$table->column,$fklist);
			print_r($fklist);
		}else{
			echo "table not found";
		}

  }

	private function find_table($tb_name){
		$file_path = "./schema.xml";
		$f = fopen($file_path, "r");
		$contents = fread($f, filesize($file_path));
		$xml= new \SimpleXMLElement($contents);
		$o = False;
		foreach ($xml->table as $table) {
			if($table->attributes()->phpName == $tb_name){
				$o = $table;
			}
		}
		return $o;
	}

  private function _generate_view($table,$fields,$fk){
		//index
		$plural_table = Inflector::pluralize(strtolower($table));
		$view_file = fopen('./application/views/admin/'.strtolower($plural_table).'/index.html', "w") or die("Unable to open file!");
		fwrite($view_file, $this->_view_index_template($table,$fields));
		fclose($view_file);
		//form
		$view_file = fopen('./application/views/admin/'.strtolower($plural_table).'/form.html', "w") or die("Unable to open file!");
		fwrite($view_file, $this->_view_form_template($table,$fields,$fk));
		fclose($view_file);
		//modal
		$view_file = fopen('./application/views/admin/'.strtolower($plural_table).'/_modal.html', "w") or die("Unable to open file!");
		fwrite($view_file, $this->_view_modal_template($table,$fields,$fk));
		fclose($view_file);
  }

	private function _view_index_template($tb_name,$fields){
		$humanize_tb_name = $this->split_camel(Inflector::singularize($tb_name));
		$tb_name_lower = strtolower(Inflector::pluralize($tb_name));
		$singular_tb_name = strtolower($tb_name);
		$table =  "\t<table data-table data-controller=\"manage_$tb_name_lower\" class=\"table table-striped table-hover \" id=\"table_$tb_name\">\n";
		$table .= "\t\t<thead>\n";
		$table .= "\t\t\t<tr>\n";
		$dtcolumn ="[";
		$langlst = ['id','en'];
		foreach ($fields as $key => $field) {
			$field_name = $field->attributes()->name;
			if($this->check_field($field_name)){
				$label = strtolower(rtrim($this->split_camel($field_name)));
				$labelword = ucfirst(rtrim($this->split_snake($this->split_camel($field_name))));
				if($this->check_field($field_name)){
					foreach ($langlst as $l) {
						# code...
						$words = include("application/language/$l/default.php");
						ksort($words);
						if(!isset($words[$label])){
							$lang_file = fopen("./application/language/$l/default.php", "w") or die("Unable to open file!");
							fwrite($lang_file,"<?php \n return array(\n");
							foreach ($words as $key => $word) {
								fwrite($lang_file,"'$key' => '$word',\n");
							}
							fwrite($lang_file,"'$label' => '$labelword'\n);");
							fclose($lang_file);
						}
					}

					$dtcolumn .="{\"data\":\"$field_name\"},\n";
					$table .= "\t\t\t\t<th data-fieldname=\"$field_name\">{{res.string.$label}}</th>\n";
				}
			}
		}
		$dtcolumn .= "{\"data\":\"id\",
\t\t\t\"render\": function(data, type, row, meta){
\t\t\tvar o = '<a class=\"btn btn-sm btn-default pull-right\" href=\"/index.php/manage_$tb_name_lower/detail/' +
\t\t\tdata + '\"><i class=\"fa fa-search\"></i> Detail </a>';
\t\t\treturn o;
\t\t}}";
		$table .= "\t\t\t\t<th></th>\n";
		$table .= "\t\t\t</tr>\n";
		$table .= "\t\t</thead>\n";


		$dtcolumn .="]";

		$table .= "</table>";
		return "
{% extends \"dashboard.html\" %}
{% set title=\"$humanize_tb_name\" %}

{% set breadcrumbs = [
	{'link':'#','text':res.string.$singular_tb_name  }]
%}
{% block content %}
\t<div class=\"\">
\t\t<div class=\"clearfix\"></div>
\t\t<div class=\"row\">
\t\t\t<div class=\"col-md-12 col-sm-12 col-xs-12\">
\t\t\t\t<div class=\"box\">
\t\t\t\t\t<div class=\"box-header\">
\t\t\t\t\t\t<h3 class=\"box-title\">{{res.string.$tb_name_lower}}</h3>
\t\t\t\t\t\t<a href=\"/index.php/manage_$tb_name_lower/create\" class=\"btn btn-primary pull-right\">{{res.string.new_data}}</a>
\t\t\t\t\t\t<div class=\"clearfix\"></div>
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"box-body\">
\t\t\t\t\t\t<p class=\"text-muted font-13 m-b-30\"></p>
\t\t\t\t\t\t$table
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t</div>

{% endblock %}
{% block bottom %}
{% endblock %}
";
	}

	private function _view_form_template($tb_name,$fields,$fk){
		$tb_name_lower = strtolower(Inflector::pluralize($tb_name));
		$humanize_tb_name = $this->split_camel(Inflector::singularize($tb_name));
		$tb_name_singular = Inflector::singularize($tb_name_lower);
		$form_field = "";
		$modals = "";
		$header = false;
		foreach ($fields as $field) {
			$required = "";
			if($field->attributes()->required){
				$required = "required=\"required\"";
			}

			$field_name = $field->attributes()->phpName;
			$field_type = $field->attributes()->type;
			$field_name_lower = strtolower($field_name);
			if(!$header){
				if(preg_match('/Nama|nama|name|kode/',$field_name)){
					$header = "$field_name";
				}
			}
			$label = strtolower(ltrim(rtrim($this->split_camel($field->attributes()->name))));
			$labelword = ucfirst(ltrim(rtrim($this->split_snake($this->split_camel($field_name)))));

			if($this->check_field($field_name)){
			$form_field .= "
\t\t\t\t\t<div class=\"form-group\">
\t\t\t\t\t\t<label class=\"control-label col-md-3 col-sm-3 col-xs-12\" for=\"$field_name\">{{res.string.$label}}</label>
\t\t\t\t\t\t<div class=\"col-md-6 col-sm-6 col-xs-12\">";
			$i=0;
			$display_val = "$tb_name_lower.$field_name";
			foreach ($fk as $foreign) {
				$ourfield = $foreign['field'];
				$theirmodel = $foreign['model']->attributes()->name;
				$theirphpmodel = $foreign['model']->attributes()->phpName;
				$displaytoour = $foreign['display'];
				$theirmodellower = Inflector::pluralize(strtolower($theirmodel));
				$theirmodellower_var = Inflector::singularize(strtolower($theirmodel));
				if(strtolower($ourfield)==strtolower($field->attributes()->name)){
					$display_val = "$tb_name_lower.$theirphpmodel.$displaytoour";
					$form_field .= "
\t\t\t\t\t\t<div {% if $tb_name %}style=\"display:none\"{% endif %} class=\"input-wrap input-group\">
\t\t\t\t\t\t\t<input type=\"hidden\" type=\"text\" id=\"$field_name\" value=\"{{ $tb_name.$field_name }}\" name=\"$field_name\"  >
\t\t\t\t\t\t\t<input type=\"text\" $required name=\"display$field_name\" id=\"display$field_name\"  placeholder=\"{{ res.string.$label }}\" value=\"{{ $tb_name.$theirphpmodel }}\" class=\"form-control\" />
\t\t\t\t\t\t\t<span class=\"input-group-btn\">
\t\t\t\t\t\t\t\t<button class=\"btn btnModal btn-default\" id=\"btn$field_name\"  data-target=\"Modal$theirphpmodel\" type=\"button\">{{res.string.search}}</button>
\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t</div>";
				$modals .= "{% include \"admin/$theirmodellower/_modal.html\" %}\n";
				}else{
					$i++;
				}
			}
			if(preg_match('/ENUM/',$field->attributes()->sqlType)){
				$enum_data = preg_replace("/ENUM|\(|\)|\'|/",'',$field->attributes()->sqlType);
				$enums = explode(',',$enum_data);
				// $field_name_plural = Inflector::pluralize($field_name);
				// $field_name_singular = Inflector::singularize($field_name);
				$form_field .= "
\t\t\t\t\t\t<div {% if $tb_name_lower %}style=\"display:none\"{% endif %} class=\"input-wrap\">
\t\t\t\t\t\t\t<select  id=\"$field_name\" value=\"{{ $tb_name.$field_name }}\" name=\"$field_name\" $required class=\"form-control form-select col-md-7 col-xs-12\">\n";
				foreach ($enums as $enum) {
				$form_field .="\t\t\t\t\t\t\t\t<option  {% if $tb_name_lower.$field_name == $enum %}selected=\"selected\"{% endif %} value=\"$enum\">$enum</option>\n";
				}

				$form_field .= "\t\t\t\t\t\t\t</select>
\t\t\t\t\t\t</div>";
				$i = $i-1;
			}


			$value_dateformat = "";
			if($i>=count($fk)){
				$ex_attr = "";
				$field_dateformat = "";
				$input_type = "text";

				switch ($field_type) {
					case 'DATE':
						$ex_attr = "input-date";
						$field_dateformat = " | date('Y/m/d') ";
						$value_dateformat = " | date('d M Y') ";
						break;
					case 'TIME':
						$ex_attr = "input-datetime";
						$field_dateformat = " | date('Y/m/d h:i:s') ";
						$value_dateformat = " | date('d M Y h:i:s') ";
						break;
					case 'DATETIME':
						$ex_attr = "input-datetime";
						$field_dateformat = " | date('Y/m/d h:i:s') ";
						$value_dateformat = " | date('d M Y h:i:s') ";
						break;
					case 'TIMESTAMP':
						$ex_attr = "input-datetime";
						$field_dateformat = " | date('Y/m/d h:i:s') ";
						$value_dateformat = " | date('d M Y h:i:s') ";
						break;
					case 'BOOLEAN':
						$input_type = "checkbox";
						break;
						# code...
					case 'FLOAT':
						$input_type = "number";
						break;
						# code...
					case 'INTEGER':
						$input_type = "number";
						# code...
						break;

					default:
						$ex_attr = "";
						break;
				}

				$form_field .= "
\t\t\t\t\t\t<div {% if $tb_name_lower %}style=\"display:none\"{% endif %} class=\"input-wrap\">
\t\t\t\t\t\t\t<input type=\"$input_type\" placeholder=\"{{ res.string.$label }}\" id=\"$field_name\" value=\"{{ $tb_name.$field_name $field_dateformat}}\"
\t\t\t\t\t\t\tname=\"$field_name\" $required class=\"form-control $ex_attr col-md-7 col-xs-12\">
\t\t\t\t\t\t</div>";
			}

			$form_field .= "
\t\t\t\t\t\t\t\t{% if $tb_name_lower %}
\t\t\t\t\t\t\t\t\t<p class=\"control-value\">{{ $display_val $value_dateformat }}</p>
\t\t\t\t\t\t\t\t{% endif %}
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>\n";
			}
		}

		if(!$header){
			$header = "id ";
		}
		return "
{% extends \"dashboard.html\" %}
{% set title=\"$humanize_tb_name\" %}
{% if $tb_name_lower %}
	 {% set bc_text = $tb_name_lower.$header %}
{% else %}
	 {% set bc_text = res.string.new %}
{% endif %}
{% set breadcrumbs = [
	{'link': base_url~'index.php/manage_$tb_name_lower','text': res.string.$tb_name_singular },
	{'link':'#','text':bc_text  }]
%}
{% block content %}
<div class=\"row\">
\t<div class=\"col-md-12 col-sm-12 col-xs-12\">
\t\t<form id=\"form_$tb_name\" method=\"post\" action=\"/index.php/manage_$tb_name_lower/write/{{ $tb_name.id }}\" data-parsley-validate class=\"form-horizontal form-label-left\">
\t\t\t<div class=\"box box-info\">
\t\t\t\t<div class=\"box-header with-border\">
\t\t\t\t{% include 'common/_form_header.html'
\t\t\t\twith {'back_url':'/index.php/manage_$tb_name_lower'~back_link,
\t\t\t\t'title': res.string.$tb_name_singular,
\t\t\t\t'delete_url':'/index.php/manage_$tb_name_lower/delete/' ~ $tb_name_lower.Id,
\t\t\t\t'object':$tb_name_lower} %}
\t\t\t\t</div>
\t\t\t\t<div class=\"box-body\">
\t\t\t\t\t$form_field
\t\t\t\t</div>
\t\t</form>
\t</div>
</div>
<!-- Modal -->
$modals
{% endblock %}
{% block bottom%}

{% endblock %}
";
	}

private function _view_modal_template($tb_name,$fields){
	$humanize_tb_name = $this->split_camel(Inflector::singularize($tb_name));
	$tb_name_lower = strtolower(Inflector::pluralize($tb_name));
	$table =  "\t<table data-display=\"2\" data-controller=\"manage_$tb_name_lower\" class=\"table table-striped table-hover \" id=\"table_$tb_name\">\n";
	$table .= "\t\t<thead>\n";
	$table .= "\t\t\t<tr>\n";
	$table .= "\t\t\t\t<th></th>\n";

	foreach ($fields as $key => $field) {
		$field_name = $field->attributes()->name;
		if($this->check_field($field_name)){
			$label = strtolower($this->split_camel($field_name));
			$labelword = ucfirst($this->split_camel($field_name));

			if($this->check_field($field_name)){
				$table .= "\t\t\t\t<th data-fieldname=\"$field_name\">{{res.string.$label}}</th>\n";
			}
		}
	}
	$table .= "\t\t\t</tr>\n";
	$table .= "\t\t</thead>\n";
	$table .= "\t</table>\n";


	return "
	<div class=\"modal fade\" id=\"Modal$tb_name\" data-init=\"0\" data-controller=\"manage_$tb_name_lower\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\">
		<div class=\"modal-dialog modal-lg\" role=\"document\">
			<div class=\"modal-content\">
				<div class=\"modal-header\">
					<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
					<h4 class=\"modal-title\" id=\"myModalLabel\">$tb_name</h4>
				</div>
				<div class=\"modal-body\">
				$table
				</div>
			</div>
		</div>
	</div>
	";
}


  private function _controller_template($tb_name,$fields,$fk){
    $var = Inflector::pluralize(strtolower($tb_name));
		$singular_var = Inflector::singularize(strtolower($tb_name));
    $queryclass = ucfirst($tb_name).'Query';
		$ucfvar = ucfirst($tb_name);
    $dash = "-";
		$m2odef = "";
		$m2opass = "";
		$create_extra_arr = "";
		foreach ($fk as $foreign) {
			# code...
			$ourfield = $foreign['field'];
			$theirmodel = $foreign['model']->attributes()->name;
			$theirvar = Inflector::pluralize(strtolower($theirmodel));
			$theirmodelquery = ucfirst($this->camelize($theirmodel)."Query");
			$displaytoour = $foreign['display'];
			$m2odef .= "
		\$$theirvar = $theirmodelquery::create()->find();
			";
			$m2opass .= "
		'$theirvar'=> \$$theirvar,
			";
		}
		$get_json_field_list = "";
		$get_json_filter = "";
		$get_json_filter_cond = "";
		$a=1;
		$fls = "array(\n";
		foreach ($fields as $field) {
			$i = 0;
			$field_name = $field->attributes()->phpName;
			if(!in_array($field->attributes()->phpName,
			['Id','CreatedAt','UpdatedAt'])){
			$fls .= " '$field_name' => '$field_name', \n";
			}
			// $fls[$field->attributes()->phpName]=$field->attributes()->phpName;
			foreach ($fk as $foreign) {
				$ourfield = $foreign['field'];
				$theirmodel = $foreign['model']->attributes()->name;
				$theirphpmodel = $foreign['model']->attributes()->phpName;
				$displaytoour = $foreign['display'];
				$theirmodellower = Inflector::pluralize(strtolower($theirmodel));
				$theirmodellower_var = Inflector::singularize(strtolower($theirmodel));
				if(strtolower($ourfield)==strtolower($field->attributes()->name)){
					$get_json_field_list .= "\t\t\t\t\$o['data'][\$i]['$field_name'] = \$$singular_var$dash>get".ucfirst($this->camelize($theirmodel))."()->get$displaytoour();\n";
				}else{
					$i++;
				}
			}
			if($this->check_field($field_name) && ($i>=count($fk))){
				if($field->attributes()->type =='DATE'){
					$get_json_field_list .= "\t\t\t\t\$o['data'][\$i]['$field_name'] = \$$singular_var$dash>get".ucfirst($this->camelize($field_name))."()?date_format(\$$singular_var$dash>get".ucfirst($this->camelize($field_name))."(),'d M Y'):\"\";\n";
				}else	if(in_array($field->attributes()->type,array('TIME','TIMESTAMP'))){
					$get_json_field_list .= "\t\t\t\t\$o['data'][\$i]['$field_name'] = \$$singular_var$dash>get".ucfirst($this->camelize($field_name))."()?date_format(\$$singular_var$dash>get".ucfirst($this->camelize($field_name))."(),'d M Y h:i:s'):\"\";\n";
				}else{
					$get_json_field_list .= "\t\t\t\t\$o['data'][\$i]['$field_name'] = \$$singular_var$dash>get".ucfirst($this->camelize($field_name))."();\n";
				}

				if($field->attributes()->type == "CHAR" || $field->attributes()->type == "VARCHAR"){
						$get_json_filter .= "\t\t\t\$$var$dash>condition('cond$a' ,'$ucfvar.$field_name LIKE ?', \"%\".\$this->input->get('search[value]').\"%\");\n";
						$get_json_filter_cond .= "'cond$a',";
						$a++;
				}

			}
		}
		$fls .=")";
    $template = "<?php

class Manage_$var extends MY_Controller{


  function __construct(){
    parent::__construct();
		\$this->set_objname('$tb_name');
		\$this->tpl = '$var';

    \$this->authorization->check_authorization('manage_$var');
  }


	function write(\$id=null){
		\$data = parent::write(\$id);
    if(\$this->input->is_ajax_request()){
			echo \$data->toJSON();
		}else{
			redirect('manage_$var/detail/'.\$data->getId());
		}
	}

  function delete(\$id){
		\$data = parent::delete(\$id);
		redirect('manage_$var');
  }

}
    ";
    return $template;
  }

	private function split_camel($ccWord){
		$a = preg_split('/(?=[A-Z])/',$ccWord);
		$count = count($a);
		$o = "";
		for ($i = 0; $i < $count; ++$i) {
		    $o .= $a[$i]." ";
		}
		return $o;
	}

	private function check_field($field_name){
		$exc = array('id','created_at','createdat','create_at','createdtime','updatetime','createat','update_at','updateat','updated_at','updatedat');
		if(!in_array(strtolower($field_name),$exc)){
			return true;
		}else{
			return false;
		}
	}

	private function camelize($input, $separator = '_')
	{
	    return str_replace($separator, '', ucwords($input, $separator));
	}
	private function split_snake($input, $separator = '_')
	{
	    return str_replace($separator, ' ', ucwords($input, $separator));
	}



}
