<?php
class Employee extends EntityBase {
	public $Id;
	public $IsDeleted = false;
    public $EntityId;
	public $EntityCd;
	public $BadgeId;
	public $Nik;
    public $Gol;
	public $Nama;
    public $DeptId;
    public $DeptCd;
    public $Jabatan;
    public $MulaiKerja;
    public $Agama;
    public $StsKaryawan;
    public $Jkelamin;
    public $T4Lahir;
    public $TglLahir;
    public $Alamat;
    public $Pendidikan;
    public $Bagian;
    public $NoSim;
    public $StsPajak;
    public $NoHp;
    public $Npwp;
    public $NoBpjsTk;
    public $NoBpjsKes;
    public $NoInhealth;
    public $IsAktif;
    public $Fphoto;
    public $Fsignature;
    public $IsJht = 0;
    public $IsPpensiun = 0;
    public $PasInhealth;
    public $NmIbuKandung;
    public $Bank;
    public $NoRek;
    public $Poh;
    public $Email;
    public $CreatebyId;
    public $UpdatebyId;


	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->BadgeId = $row["badge_id"];
        $this->Nik = $row["nik"];
        $this->Gol = $row["gol"];
		$this->Nama = $row["nama"];
        $this->DeptId = $row["dept_id"];
        $this->DeptCd = $row["dept_code"];
        $this->Jabatan = $row["jabatan"];
        $this->Bagian = $row["bagian"];
        $this->MulaiKerja = strtotime($row["mulai_kerja"]);
        $this->Agama = $row["agama"];
        $this->StsKaryawan = $row["sts_karyawan"];
        $this->Jkelamin = $row["jk"];
        $this->T4Lahir = $row["t4_lahir"];
        $this->TglLahir = strtotime($row["tgl_lahir"]);
        $this->Alamat = $row["alamat"];
        $this->Pendidikan = $row["pendidikan"];
        $this->NoSim = $row["no_sim"];
        $this->StsPajak = $row["sts_pajak"];
        $this->NoHp = $row["no_hp"];
        $this->Npwp = $row["npwp"];
        $this->NoBpjsTk = $row["no_bpjs_tk"];
        $this->NoBpjsKes = $row["no_bpjs_kes"];
        $this->NoInhealth = $row["no_inhealth"];
        $this->IsAktif = $row["is_aktif"];
        $this->Fphoto = $row["fphoto"];
        $this->Fsignature = $row["fsignature"];
        $this->Email = $row["email"];
        $this->NmIbuKandung = $row["nm_ibu_kandung"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatMulaiKerja($format = HUMAN_DATE) {
        return is_int($this->MulaiKerja) ? date($format, $this->MulaiKerja) : null;
    }

    public function FormatTglLahir($format = HUMAN_DATE) {
        return is_int($this->TglLahir) ? date($format, $this->TglLahir) : null;
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Nama[]
	 */
	public function LoadAll($orderBy = "a.nama") {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.dept_code FROM hr_employee_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_dept As c On a.dept_id = c.id ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Employee();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Nama
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.dept_code FROM hr_employee_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_dept As c On a.dept_id = c.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	/**
	 * @param int $id
	 * @return Nama
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $eti
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Nama[]
	 */
	public function LoadByEntityId($eti, $orderBy = "a.nama", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.*, b.entity_cd, c.dept_code FROM hr_employee_master AS a JOIN cm_company AS b ON a.entity_id = b.entity_id LEFT JOIN cm_dept As c On a.dept_id = c.id WHERE a.entity_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
        $result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Employee();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText =
        'INSERT INTO hr_employee_master(fsignature,nm_ibu_kandung,email,badge_id,bagian,fphoto,entity_id,nik,gol,nama,dept_id,jabatan,mulai_kerja,agama,sts_karyawan,jk,t4_lahir,tgl_lahir,alamat,pendidikan,no_sim,sts_pajak,no_hp,npwp,no_bpjs_tk,no_bpjs_kes,no_inhealth,is_aktif,createby_id,create_time)
        VALUES(?fsignature,?nm_ibu_kandung,?email,?badge_id,?bagian,?fphoto,?entity_id,?nik,?gol,?nama,?dept_id,?jabatan,?mulai_kerja,?agama,?sts_karyawan,?jk,?t4_lahir,?tgl_lahir,?alamat,?pendidikan,?no_sim,?sts_pajak,?no_hp,?npwp,?no_bpjs_tk,?no_bpjs_kes,?no_inhealth,?is_aktif,?createby_id,now())';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?badge_id", $this->BadgeId,"varchar");
        $this->connector->AddParameter("?nik", $this->Nik,"varchar");
        $this->connector->AddParameter("?gol", $this->Gol);
        $this->connector->AddParameter("?nama", $this->Nama);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?jabatan", $this->Jabatan);
        $this->connector->AddParameter("?bagian", $this->Bagian);
        $this->connector->AddParameter("?mulai_kerja", $this->FormatMulaiKerja(SQL_DATEONLY));
        $this->connector->AddParameter("?agama", $this->Agama);
        $this->connector->AddParameter("?sts_karyawan", $this->StsKaryawan);
        $this->connector->AddParameter("?jk", $this->Jkelamin);
        $this->connector->AddParameter("?t4_lahir", $this->T4Lahir);
        $this->connector->AddParameter("?tgl_lahir", $this->FormatTglLahir(SQL_DATEONLY));
        $this->connector->AddParameter("?alamat", $this->Alamat);
        $this->connector->AddParameter("?pendidikan", $this->Pendidikan);
        $this->connector->AddParameter("?no_sim", $this->NoSim,"varchar");
        $this->connector->AddParameter("?sts_pajak", $this->StsPajak,"varchar");
        $this->connector->AddParameter("?no_hp", $this->NoHp,"varchar");
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?no_bpjs_tk", $this->NoBpjsTk,"char");
        $this->connector->AddParameter("?no_bpjs_kes", $this->NoBpjsKes,"varchar");
        $this->connector->AddParameter("?no_inhealth", $this->NoInhealth,"varchar");
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?fphoto", $this->Fphoto);
        $this->connector->AddParameter("?fsignature", $this->Fsignature);
        $this->connector->AddParameter("?email", $this->Email);
        $this->connector->AddParameter("?nm_ibu_kandung", $this->NmIbuKandung);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
	    $sql = "UPDATE hr_employee_master SET
            entity_id = ?entity_id,
            badge_id = ?badge_id,
            nik = ?nik,
            gol = ?gol,
            nama = ?nama,
            dept_id = ?dept_id,
            jabatan = ?jabatan,
            bagian = ?bagian,
            mulai_kerja = ?mulai_kerja,
            agama = ?agama,
            sts_karyawan = ?sts_karyawan,
            jk = ?jk,
            t4_lahir = ?t4_lahir,
            tgl_lahir = ?tgl_lahir,
            alamat = ?alamat,
            pendidikan = ?pendidikan,
            no_sim = ?no_sim,
            sts_pajak = ?sts_pajak,
            no_hp = ?no_hp,
            npwp = ?npwp,
            no_bpjs_tk = ?no_bpjs_tk,
            no_bpjs_kes = ?no_bpjs_kes,
            no_inhealth = ?no_inhealth,
            is_aktif = ?is_aktif,";
	        if ($this->Fphoto != null) {
                $sql.= "fphoto = ?fphoto,";
            }
            if ($this->Fsignature != null) {
                $sql.= "fsignature = ?fsignature,";
            }
            $sql.= "email = ?email,
            nm_ibu_kandung = ?nm_ibu_kandung,
            updateby_id = ?updateby_id,
            update_time = now()
        WHERE id = ?id";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?badge_id", $this->BadgeId,"varchar");
        $this->connector->AddParameter("?nik", $this->Nik,"varchar");
        $this->connector->AddParameter("?gol", $this->Gol);
        $this->connector->AddParameter("?nama", $this->Nama);
        $this->connector->AddParameter("?dept_id", $this->DeptId);
        $this->connector->AddParameter("?jabatan", $this->Jabatan);
        $this->connector->AddParameter("?bagian", $this->Bagian);
        $this->connector->AddParameter("?mulai_kerja", $this->FormatMulaiKerja(SQL_DATEONLY));
        $this->connector->AddParameter("?agama", $this->Agama);
        $this->connector->AddParameter("?sts_karyawan", $this->StsKaryawan);
        $this->connector->AddParameter("?jk", $this->Jkelamin);
        $this->connector->AddParameter("?t4_lahir", $this->T4Lahir);
        $this->connector->AddParameter("?tgl_lahir", $this->FormatTglLahir(SQL_DATEONLY));
        $this->connector->AddParameter("?alamat", $this->Alamat);
        $this->connector->AddParameter("?pendidikan", $this->Pendidikan);
        $this->connector->AddParameter("?no_sim", $this->NoSim,"varchar");
        $this->connector->AddParameter("?sts_pajak", $this->StsPajak,"varchar");
        $this->connector->AddParameter("?no_hp", $this->NoHp,"varchar");
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?no_bpjs_tk", $this->NoBpjsTk,"char");
        $this->connector->AddParameter("?no_bpjs_kes", $this->NoBpjsKes,"varchar");
        $this->connector->AddParameter("?no_inhealth", $this->NoInhealth,"varchar");
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?fphoto", $this->Fphoto);
        $this->connector->AddParameter("?fsignature", $this->Fsignature);
        $this->connector->AddParameter("?email", $this->Email);
        $this->connector->AddParameter("?nm_ibu_kandung", $this->NmIbuKandung);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE hr_employee_master SET is_deleted = 1, is_aktif = 0 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function GetAutoNik($entityId = 1) {
        // function untuk menggenerate nik karyawan
        $xnik = null;
        $cnik = null;
        $this->connector->CommandText = "SELECT max(a.nik) as maxNik FROM hr_employee_master as a WHERE a.entity_id = ?entityId";
        $this->connector->AddParameter("?entityId", $entityId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs != null) {
            $row = $rs->FetchAssoc();
            $xnik = $row["maxNik"];
            $cnik = $xnik +1;
        } else {
            $cnik = ltrim($entityId).'001';
        }
        return $cnik;
    }
}
