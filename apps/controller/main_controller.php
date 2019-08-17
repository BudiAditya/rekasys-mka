<?php

class MainController extends AppController {

	protected function Initialize() { }

	public function index() {
		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}

		//require_once(LIBRARY . "user_notification.php");
		//$this->Set("notifications", Notification::GetCurrentUserNotifications());

		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function change_password() {
		if ($this->persistence->StateExists("info")) {
			$this->Set("info", $this->persistence->LoadState("info"));
			$this->persistence->DestroyState("info");
		}

		if (count($this->postData) == 0) {
			return;
		}

		// OK mari kita ganti passwordnya
		$old = $this->GetPostValue("Old");
		$new = $this->GetPostValue("New");
		$retype = $this->GetPostValue("Retype");

		if ($old == "") {
			$this->Set("error", "Maaf mohon mengetikkan password lama anda");
			return;
		}
		if ($new == "") {
			$this->Set("error", "Maaf mohon mengetikkan password baru anda");
			return;
		}
		if ($new == $old) {
			$this->Set("error", "Password lama dan password baru sama.");
			return;
		}
		if ($new != $retype) {
			$this->Set("error", "Password baru dan ulangi tidak sama");
			return;
		}

		$old = md5($old);
		$new = md5($new);

		$this->connector->CommandText = "UPDATE sys_users SET pwdchange_cnt = pwdchange_cnt +1, user_pwd = ?new WHERE user_uid = ?id AND user_pwd = ?old";
		$this->connector->AddParameter("?new", $new);
		$this->connector->AddParameter("?id", AclManager::GetInstance()->GetCurrentUser()->Id);
		$this->connector->AddParameter("?old", $old);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->persistence->SaveState("info", "Password anda telah berhasil dirubah. Password baru akan efektif pada login berikutnya.");
			redirect_url("main");
		} else {
			$this->Set("error", "Maaf password lama anda salah.");
		}
	}

	public function set_periode() {
		if (count($this->postData) > 0) {
			$year = $this->GetPostValue("year");
			$month = $this->GetPostValue("month");

			$this->persistence->SaveState("acc_year", $year);
			$this->persistence->SaveState("acc_month", $month);

			// OK karena simpan persistence sifatnya void kita asumsikan berhasil
			redirect_url("main");
		} else {
			if ($this->persistence->StateExists("acc_year")) {
				$year = $this->persistence->LoadState("acc_year");
			} else {
				$year = date("Y");
			}
			if ($this->persistence->StateExists("acc_month")) {
				$month = $this->persistence->LoadState("acc_month");
			} else {
				$month = date("n");
			}

		}

		$this->Set("year", $year);
		$this->Set("month", $month);

		if ($this->persistence->StateExists("error")) {
			$this->Set("error", $this->persistence->LoadState("error"));
			$this->persistence->DestroyState("error");
		}
	}

	public function dbackup(){
        // Database configuration
        $host = "127.0.0.1";
        $username = "root";
        $password = "";
        $database_name = "db_mka";
        $port = "3308";

    // Get connection object and set the charset
        $conn = mysqli_connect($host, $username, $password, $database_name, $port);
        $conn->set_charset("utf8");


    // Get All Table Names From the Database
        $tables = array();
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "";
        foreach ($tables as $table) {

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i ++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j ++) {
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        if(!empty($sqlScript))
        {
            // Save the SQL script to a backup file
            $backup_file_name = $database_name . '_backup_' . time() . '.sql';
            $fileHandler = fopen($backup_file_name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);

            // Download the SQL backup file to the browser
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backup_file_name));
            ob_clean();
            flush();
            readfile($backup_file_name);
            exec('rm ' . $backup_file_name);
        }
    }
}
