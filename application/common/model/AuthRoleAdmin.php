<?phpnamespace app\common\model;use think\model;class AuthRoleAdmin extends model{ 	public function setRoleAdminByRole($role,$admin){        $temp = [];        foreach ($role as $key => $val) {            $temp[$key]['admin_id'] = $admin;            $temp[$key]['role_id'] = $val;        }        return $this->saveAll($temp);    }}