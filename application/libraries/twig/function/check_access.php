<?php
function check_access(){
	return new Twig_Function('check_access',function ($act='write',$ctrl=false) {
		$pass = true;
		$ci =& get_instance();
		if(!$ctrl){
			$ctrl = $ci->router->fetch_class();
		}
		write_log($ctrl);
		$user = UserQuery::create()->findPk($ci->session->userdata('uid'));

  	$usergroup = $user->getUserGroups()[0]->getGroup();
		$access = MenuGroupQuery::create()
			->filterByGroupId($usergroup->getId())
			->useMenuQuery()
			->filterByController($ctrl)
			->endUse()
			->findOne();
		if(!$access){
			$pass = false;
	    	}else{
	    		$write_act = ['create','write','delete'];
						if(in_array($act,$write_act)){
							if($access->getAccess()!= 'write'){
								$pass = false;
							}
						}
	    	}
				
		return $pass;
 	 });
}
