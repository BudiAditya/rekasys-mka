<?php
class UseraclController extends AppController {
    private $userProjectId;
    protected function Initialize() {
        require_once(MODEL . "master/user_admin.php");
        require_once(MODEL . "master/user_acl.php");
        $this->userProjectId = $this->persistence->LoadState("project_id");
    }

    public function add($uid,$pri = 0) {
        require_once(MODEL . "master/project.php");
        $loader = null;
        $skema = null;
        $userlist = null;
        // find user data
        $log = new UserAdmin();
        $userdata = new UserAdmin();
        $userdata = $userdata->FindById($uid);
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $proid = $this->GetPostValue("ProjectId");
            $skema = $this->GetPostValue("hakakses");
            $prevResId = null;
            $hak = null;
            $pri = $proid;
            if ($proid == null || $proid == 0 || $proid == ''){
                $userAcl = new UserAcl();
                $userAcl->Delete($uid,0);
                $this->persistence->SaveState("info", sprintf("Data Hak Akses User: '%s' telah berhasil dihapus..", $userdata->UserId));
                redirect_url("master.useradmin");
            }else {
                $userAcl = new UserAcl();
                $userAcl->Delete($uid, $proid);
                foreach ($skema As $aturan) {
                    $tokens = explode("|", $aturan);
                    $resid = $tokens[0];
                    $hak = $tokens[1];
                    if ($prevResId != $resid) {
                        if ($userAcl->Rights != "") {
                            $userAcl->Insert();
                        }
                        $prevResId = $resid;
                        $userAcl = new UserAcl();
                        $userAcl->ResourceId = $resid;
                        $userAcl->UserUid = $uid;
                        $userAcl->ProjectId = $proid;
                        $userAcl->Rights = "";
                    }
                    $userAcl->Rights .= $hak;
                }
                if ($userAcl->Rights != "") {
                    $userAcl->Insert();
                    $log = $log->UserActivityWriter($this->userProjectId,'master.useracl','Setting User ACL -> User: '.$userdata->UserId.' - '.$userdata->UserName,'-','Success');
                }
                $this->persistence->SaveState("info", sprintf("Data Hak Akses User: '%s' telah berhasil disimpan.", $userdata->UserId));
                redirect_url("master.useradmin");
            }
        } else {
            $userAcl = new UserAcl();
            $hak = $userAcl->LoadAcl($uid,$pri);
            $loader = new UserAcl();
            $userPro = $loader->LoadUserProAcl($uid);
        }
        // load resource data
        $loader = new UserAcl();
        $resources = $loader->LoadAllResources();
        $loader = new Project();
        $projects = $loader->LoadAll();
        $loader = new UserAcl();
        $userlist = $loader->GetListUserProAcl();
        $this->Set("resources", $resources);
        $this->Set("userdata", $userdata);
        $this->Set("userlist", $userlist);
        $this->Set("hak", $hak);
        $this->Set("userPro", $userPro);
        $this->Set("projects", $projects);
        $this->Set("uproId", $pri);
    }

    public function view($uid = 0) {
        //load acl
        if ($uid == 0){
            $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        }
        $userId = null;
        $userdata = new UserAdmin();
        $userdata = $userdata->FindById($uid);
        $userId = $userdata->UserId.' ['.$userdata->UserName.']';
        $userAcl = new UserAcl();
        $aclists = $userAcl->GetUserAclList($uid);
        $this->Set("userId", $userId);
        $this->Set("aclists", $aclists);
    }


    public function copy($uid = null) {
        $srcUid = null;
        $pri = 0;
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $cdata = $this->GetPostValue("copyFrom");
            $pri = $this->GetPostValue("tProjectId");
            $cdata = explode("|",$cdata);
            $srcUid = $cdata[0];
            $srcCbi = $cdata[1];
            $userAcl = new UserAcl();
            $userAcl->Delete($uid,$pri);
            $userAcl->Copy($srcUid,$srcCbi,$uid,$pri);
            $this->persistence->SaveState("info", sprintf("Data Hak Akses telah berhasil disalin.."));
            Dispatcher::RedirectUrl("master.useracl/add/".$uid."/".$pri);
        } else {
            $userAcl = new UserAcl();
            $hak = $userAcl->LoadAcl($uid,$pri);
            Dispatcher::RedirectUrl("master.useracl/add/".$uid."/".$pri);
        }
    }


}
