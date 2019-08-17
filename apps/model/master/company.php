<?php
class Company extends EntityBase {
	public $IsDeleted = false;
	public $EntityId;
	public $EntityCd;
	public $CompanyName;
	public $Address;
	public $City;
	public $Province;
	public $Telephone;
	public $Facsimile;
    public $Npwp;
    public $PersonInCharge;
    public $PicStatus;
    public $StartDate;
    public $GeneralCashAccId = 0;
    public $Email;
    public $Website;
    public $PpnInAccId = 0;
    public $PpnTrxAccId = 0;
    public $PpnOutAccId = 0;
    public $DefProjectId = 0;
    public $FileLogo;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
		//$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->CompanyName = $row["company_name"];
		$this->Address = $row["address"];
		$this->City = $row["city"];
		$this->Province = $row["province"];
		$this->Telephone = $row["telephone"];
		$this->Facsimile = $row["facsimile"];
        $this->Npwp = $row["npwp"];
        $this->PersonInCharge = $row["personincharge"];
        $this->PicStatus = $row["pic_status"];
        $this->StartDate = $row["start_date"];
        $this->GeneralCashAccId = $row["general_cash_acc_id"];
        $this->PpnInAccId = $row["ppn_in_acc_id"];
        $this->PpnTrxAccId = $row["ppn_trx_acc_id"];
        $this->PpnOutAccId = $row["ppn_out_acc_id"];
        $this->Email = $row["email"];
        $this->Website = $row["website"];
        $this->DefProjectId = $row["def_project_id"];
        $this->FileLogo = $row["file_logo"];
	}

    public function LoadAll($orderBy = "a.entity_cd", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.* FROM cm_company AS a ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.* FROM cm_company AS a ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Company();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

    public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_company AS a WHERE a.entity_id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByCode($code) {
		$this->connector->CommandText = "SELECT a.* FROM cm_company AS a WHERE a.entity_cd = ?code ORDER BY a.urutan";
		$this->connector->AddParameter("?code", $code);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM cm_company AS a WHERE a.entity_id = ?entity_id";
		$this->connector->AddParameter("?entity_id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO cm_company(ppn_trx_acc_id,file_logo,entity_cd,company_name,address,city,province,telephone,facsimile,npwp,personincharge,pic_status,start_date,general_cash_acc_id,email,website,ppn_in_acc_id,ppn_out_acc_id,def_project_id)
VALUES(?ppn_trx_acc_id,?file_logo,?entity_cd,?company_name,?address,?city,?province,?telephone,?facsimile,?npwp,?personincharge,?pic_status,?start_date,?general_cash_acc_id,?email,?website,?ppn_in_acc_id,?ppn_out_acc_id,?def_project_id)';
		$this->connector->AddParameter("?entity_cd", $this->EntityCd);
        $this->connector->AddParameter("?company_name", $this->CompanyName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?province", $this->Province);
        $this->connector->AddParameter("?telephone", $this->Telephone);
        $this->connector->AddParameter("?facsimile", $this->Facsimile);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?personincharge", $this->PersonInCharge);
        $this->connector->AddParameter("?pic_status", $this->PicStatus);
        $this->connector->AddParameter("?start_date", $this->StartDate);
        $this->connector->AddParameter("?general_cash_acc_id", $this->GeneralCashAccId);
        $this->connector->AddParameter("?ppn_in_acc_id", $this->PpnInAccId);
        $this->connector->AddParameter("?ppn_trx_acc_id", $this->PpnTrxAccId);
        $this->connector->AddParameter("?ppn_out_acc_id", $this->PpnOutAccId);
        $this->connector->AddParameter("?email", $this->Email);
        $this->connector->AddParameter("?website", $this->Website);
        $this->connector->AddParameter("?def_project_id", $this->DefProjectId);
        $this->connector->AddParameter("?file_logo", $this->FileLogo);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_company SET
	entity_cd = ?entity_cd,
	company_name = ?company_name,
	address = ?address,
	city = ?city,
	province = ?province,
	telephone = ?telephone,
	facsimile = ?facsimile,
	npwp = ?npwp,
	personincharge = ?personincharge,
	pic_status = ?pic_status,
	start_date = ?start_date,
	general_cash_acc_id = ?general_cash_acc_id,
	ppn_in_acc_id = ?ppn_in_acc_id,
	ppn_trx_acc_id = ?ppn_trx_acc_id,
	ppn_out_acc_id = ?ppn_out_acc_id,
	email = ?email,
	website = ?website,
	def_project_id = ?def_project_id,
	file_logo = ?file_logo 
WHERE entity_id = ?entity_id';
		$this->connector->AddParameter("?entity_cd", $this->EntityCd);
        $this->connector->AddParameter("?company_name", $this->CompanyName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?province", $this->Province);
        $this->connector->AddParameter("?telephone", $this->Telephone);
        $this->connector->AddParameter("?facsimile", $this->Facsimile);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?personincharge", $this->PersonInCharge);
        $this->connector->AddParameter("?pic_status", $this->PicStatus);
		$this->connector->AddParameter("?entity_id", $id);
        $this->connector->AddParameter("?start_date", $this->StartDate);
        $this->connector->AddParameter("?general_cash_acc_id", $this->GeneralCashAccId);
        $this->connector->AddParameter("?ppn_in_acc_id", $this->PpnInAccId);
        $this->connector->AddParameter("?ppn_trx_acc_id", $this->PpnTrxAccId);
        $this->connector->AddParameter("?ppn_out_acc_id", $this->PpnOutAccId);
        $this->connector->AddParameter("?email", $this->Email);
        $this->connector->AddParameter("?website", $this->Website);
        $this->connector->AddParameter("?def_project_id", $this->DefProjectId);
        $this->connector->AddParameter("?file_logo", $this->FileLogo);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
//		$this->connector->CommandText = 'DELETE FROM cm_company WHERE entity_id = ?id';
//		$this->connector->AddParameter("?id", $id);
		$this->connector->CommandText = "UPDATE cm_company SET is_deleted = 1 WHERE entity_id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

}

