<?php
function numbering(){
    return new Twig_Function('numbering', function ($setting) {
		  $ci =& get_instance();
      $ci->load->helper('good_numbering');
      return create_number($setting);
    });

}
