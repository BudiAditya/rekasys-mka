<?php

class HomeController extends AppController {

	protected function Initialize() { }

	public function index() {
		redirect_url("home/login");
	}

	//membuat form tambah data dan proses cek data login
	public function login() {
		require_once(MODEL . "master/user_admin.php");
        require_once(MODEL . "master/project.php");
		$this->Set("title", "Login"); //set title form
        $pwdchcnt = 0;
		// Cek apakah user mengirimkan data username dan password atau tidak
		if (count($this->postData) > 0) {
			// User mengirim data username dan password melalui form login
			$projectid = trim($this->GetPostValue("project_id"));
            $username = trim($this->GetPostValue("user_id"));
			$password = md5($this->GetPostValue("user_pwd"));
            $captcha = trim($this->GetPostValue("user_captcha"));
			//cek captcha
            if ($this->persistence->LoadState("user_captcha") == $captcha){
                //jika login berhasil
                if ($this->doLogin($username, $password, $projectid)) {
                    $acl = AclManager::GetInstance(); //load class acl untuk session user id
                    $uid = $acl->CurrentUser->Id;
                    $router = Router::GetInstance();
                    $userAdmin = new UserAdmin();
                    $userAdmin->FindById($uid);
                    $usercomp = null;
                    $userpro = null;
                    $oke = false;
                    if ($userAdmin != null) {
                        // periksa status user aktif atau tidak
                        if ($userAdmin->IsAktif == 1) {
                            // update table sys_users dengan info login
                            // project2 apa saja yg boleh diakses
                            $usrprojects = $userAdmin->AProjectId;
                            $pwdchcnt = $userAdmin->PwdchangeCnt;
                            if (strstr($userAdmin->AProjectId,$projectid)) {
                                $oke = true;
                            }
                            if (!$oke && $userAdmin->UserLvl < 4) {
                                $this->Set("error", "Maaf, Anda tidak boleh mengakses project ini!"); //tampilkan pesan error
                                $log = $userAdmin->LoginActivityWriter($projectid, $username, 'Akses Project ditolak');
                            } else {
                                // update table sys_users dengan info login
                                $userAdmin->Status = "6";
                                $userAdmin->LoginTime = date('Y-m-d H:i:s');
                                $userAdmin->LoginFrom = $router->IpAddress;
                                $userAdmin->SessionId = $this->persistence->GetPersistenceId();
                                $userAdmin->LoginRecord($userAdmin->UserUid);
                                // ambil data entity dan project user yang login simpan ke session
                                $this->persistence->SaveState("entity_id", $userAdmin->EntityId);
                                $this->persistence->SaveState("entity_cd", $userAdmin->EntityCd);
                                $userpro = new Project($projectid);
                                //project gak mungkin null harusnya
                                $this->persistence->SaveState("allow_projects_id", $usrprojects);
                                $this->persistence->SaveState("project_id", $projectid);
                                $this->persistence->SaveState("project_cd", $userpro->ProjectCd);
                                $this->persistence->SaveState("project_name", $userpro->ProjectName);
                                $this->persistence->SaveState("user_lvl", $userAdmin->UserLvl);
                                $this->persistence->SaveState("useremp_id", $userAdmin->EmployeeId);
                                // Simpan data untuk lock tanggal periode ad / edit voucher
                                $this->persistence->SaveState("force_periode", $userAdmin->IsForceAccountingPeriod);
                                $log = $userAdmin->LoginActivityWriter($projectid, $username, 'Login success');
                                $log = $userAdmin->UserActivityWriter($projectid, 'home.login', 'LogIn to System', '', 'Success');
                                if ($pwdchcnt > 0) {
                                    if ($userAdmin->IsForceAccountingPeriod) {
                                        redirect_url("main/set_periode");
                                    } else {
                                        redirect_url("main");
                                    }
                                }else{
                                    $this->persistence->SaveState("info", "Mohon mengganti Password default Anda..");
                                    redirect_url("main/change_password");
                                }
                            }
                        }else{
                            $log = $userAdmin->LoginActivityWriter($projectid,$username,'User ID tidak aktif');
                            $this->Set("error", "Nama Pemakai terdaftar tapi tidak di-aktif-kan!"); //tampilkan pesan error
                        }
                    }else{
                        $log = $userAdmin->LoginActivityWriter($projectid,$username,'User ID belum terdaftar');
                        $this->Set("error", "Nama Pemakai belum terdaftar!"); //tampilkan pesan error
                    }
                } else {
                    $userAdmin = new UserAdmin();
                    $log = $userAdmin->LoginActivityWriter($projectid,$username,'User ID atau Password salah');
                    $this->Set("error", "Nama atau kata sandi yang dimasukkan salah!"); //tampilkan pesan error
                }
            }else{
                $userAdmin = new UserAdmin();
                $log = $userAdmin->LoginActivityWriter($projectid,$username,'Nilai Captha salah');
                $this->Set("error", "Nilai Captcha yang dimasukkan salah !"); //tampilkan pesan error
                //Dispatcher::RedirectUrl("home/login");
            }
		} else {
			$acl = AclManager::GetInstance();
			// Kita cek apakah user sudah login atau belum
			if ($acl->GetIsUserAuthenticated()) {
				// User sudah login ke system maka tidak perlu login lagi
				Dispatcher::RedirectUrl("main");
			}
		}
		//load project list
		$projects = new Project();
		$projects = $projects->LoadByEntityId(1);
		$this->Set("projects",$projects);
	}

	//proses validasi data login
	private function doLogin($username, $password, $projectid) {
		$acl = AclManager::GetInstance();
		$success = $acl->Authenticate($username, $password, $projectid);

		if ($success) {
			$acl->SerializeUser();
		}

		return $success;
	}


	public function logout() {
		require_once(MODEL . "master/user_admin.php");
		$acl = AclManager::GetInstance();
		$uid = $acl->CurrentUser->Id;
		$userAdmin = new UserAdmin();
		$userAdmin->Status = "7";
		$userAdmin->LoginTime = date('Y-m-d H:i:s');
		$userAdmin->LoginFrom = trim(getenv("REMOTE_ADDR"));
		$userAdmin->SessionId = null;
		$userAdmin->LoginRecord($uid);

		$acl->SignOut(); // Logout User yang aktif
		$acl->SerializeUser(); // hapus semua session data
		//$this->persistence->DestroyPersistence;

		Dispatcher::RedirectUrl("home/login");
	}

    public function capgambar(){
        //session_start();
        $this->persistence->SaveState("user_captcha", "");
        $text = substr(md5(microtime()),mt_rand(0,26),5);
        //$_SESSION["ttcapt"] = $text;
        $this->persistence->SaveState("user_captcha", $text);
        $height = 35;
        $width = 54;
        $tt_image = imagecreate($width, $height);
        $blue = imagecolorallocate($tt_image, 0, 0, 255);
        $white = imagecolorallocate($tt_image, 255, 255, 255);
        $font_size = 14;
        imagestring($tt_image, $font_size, 5, 8, $text, $white);
        /* Avoid Caching */
        header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header( "Content-type: image/png" );
        imagepng($tt_image);
        imagedestroy($tt_image );
    }
}
