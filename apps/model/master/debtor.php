<?php
class Debtor extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;
	public $EntityId;
	public $EntityCd;
	public $DebtorCd;
	public $DebtorTypeId;
	public $DebtorTypeCd;
    public $DebtorName;
	public $Address1;
	public $Address2;
	public $Address3;
	public $PostalCode;
	public $PhoneNo;
	public $HandPhone;
	public $FaxNo;
	public $Remark;
	public $Npwp;
	public $ContactPerson;
	public $Position;
	public $EmailAddress;
	public $WebSite;
	public $CoreBusiness;
	public $BankAccount;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
		$this->DebtorCd = $row["debtor_cd"];
		$this->DebtorTypeId = $row["debtortype_id"];
		$this->DebtorTypeCd = $row["debtortype_cd"];
		$this->DebtorName = $row["debtor_name"];
		$this->Address1 = $row["address1"];
		$this->Address2 = $row["address2"];
		$this->Address3 = $row["address3"];
		$this->PostalCode = $row["post_cd"];
		$this->PhoneNo = $row["tel_no"];
		$this->HandPhone = $row["hand_phone"];
		$this->FaxNo = $row["fax_no"];
		$this->Remark = $row["remark"];
		$this->Npwp = $row["npwp"];
		$this->ContactPerson = $row["contact_person"];
		$this->Position = $row["position"];
		$this->EmailAddress = $row["email_add"];
		$this->WebSite = $row["web_site"];
		$this->CoreBusiness = $row["core_business"];
		$this->BankAccount = $row["bank_account"];
	}

	/**
	 * @param int $id
	 * @return Debtor
	 */
	public function LoadById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Debtor[]
	 */
	public function LoadAll($orderBy = "a.debtor_name", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Debtor();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $companyId
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Debtor[]
	 */
	public function LoadByEntity($companyId, $orderBy = "a.debtor_name", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.entity_id = ?sbu
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.is_deleted = 0 AND a.entity_id = ?sbu
ORDER BY $orderBy";
		}
		$this->connector->AddParameter("?sbu", $companyId);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Debtor();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @param int $debtorType
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Debtor[]
	 */
	public function LoadByDebtorType($debtorType, $orderBy = "a.debtor_name", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.debtortype_id = ?type
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.is_deleted = 0 AND a.debtortype_id = ?type
ORDER BY $orderBy";
		}
		$this->connector->AddParameter("?type", $debtorType);

		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Debtor();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}
	/**
	 * @param int $id
	 * @return Debtor
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd, c.debtortype_cd
FROM ar_debtor_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtortype AS c ON a.debtortype_id = c.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
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
'INSERT INTO ar_debtor_master
(entity_id, debtor_cd, debtortype_id,debtor_name, address1, address2, address3, post_cd, tel_no,
hand_phone, fax_no, remark, npwp, contact_person, position,email_add, web_site, core_business, bank_account,
createby_id, create_time)
VALUES
(?entity_id, ?debtor_cd, ?debtortype_id, ?debtor_name, ?address1, ?address2, ?address3, ?post_cd, ?tel_no,
?hand_phone, ?fax_no, ?remark, ?npwp, ?contact_person, ?position,?email_add, ?web_site, ?core_business, ?bank_account,
?user, NOW())';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?debtor_cd", $this->DebtorCd);
		$this->connector->AddParameter("?debtortype_id", $this->DebtorTypeId);
		$this->connector->AddParameter("?debtor_name", $this->DebtorName);
		$this->connector->AddParameter("?address1", $this->Address1);
		$this->connector->AddParameter("?address2", $this->Address2);
		$this->connector->AddParameter("?address3", $this->Address3);
		$this->connector->AddParameter("?post_cd", $this->PostalCode);
		$this->connector->AddParameter("?tel_no", $this->PhoneNo);
		$this->connector->AddParameter("?hand_phone", $this->HandPhone);
		$this->connector->AddParameter("?fax_no", $this->FaxNo);
		$this->connector->AddParameter("?remark", $this->Remark);
		$this->connector->AddParameter("?npwp", $this->Npwp);
		$this->connector->AddParameter("?contact_person", $this->ContactPerson);
		$this->connector->AddParameter("?position", $this->Position);
		$this->connector->AddParameter("?email_add", $this->EmailAddress);
		$this->connector->AddParameter("?web_site", $this->WebSite);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$this->connector->AddParameter("?core_business", $this->CoreBusiness);
		$this->connector->AddParameter("?bank_account", $this->BankAccount);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE ar_debtor_master SET
	debtor_cd = ?debtor_cd
	, debtortype_id = ?debtortype_id
	, debtor_name = ?debtor_name
	, address1 = ?address1
	, address2 = ?address2
	, address3 = ?address3
	, post_cd = ?post_cd
	, tel_no = ?tel_no
	, hand_phone = ?hand_phone
	, fax_no = ?fax_no
	, remark = ?remark
	, npwp = ?npwp
	, contact_person = ?contact_person
	, position = ?position
	, email_add = ?email_add
	, web_site = ?web_site
	, updateby_id = ?user
	, update_time = NOW()
	, core_business = ?core_business
	, bank_account = ?bank_account
WHERE id = ?id';
        $this->connector->AddParameter("?debtor_cd", $this->DebtorCd);
        $this->connector->AddParameter("?debtortype_id", $this->DebtorTypeId);
        $this->connector->AddParameter("?debtor_name", $this->DebtorName);
        $this->connector->AddParameter("?address1", $this->Address1);
        $this->connector->AddParameter("?address2", $this->Address2);
        $this->connector->AddParameter("?address3", $this->Address3);
        $this->connector->AddParameter("?post_cd", $this->PostalCode);
        $this->connector->AddParameter("?tel_no", $this->PhoneNo);
        $this->connector->AddParameter("?hand_phone", $this->HandPhone);
        $this->connector->AddParameter("?fax_no", $this->FaxNo);
        $this->connector->AddParameter("?remark", $this->Remark);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?contact_person", $this->ContactPerson);
        $this->connector->AddParameter("?position", $this->Position);
        $this->connector->AddParameter("?email_add", $this->EmailAddress);
        $this->connector->AddParameter("?web_site", $this->WebSite);
        $this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?core_business", $this->CoreBusiness);
		$this->connector->AddParameter("?bank_account", $this->BankAccount);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		//$this->connector->CommandText = 'Delete From ar_debtormaster WHERE id = ?id';
		$this->connector->CommandText = 'Update ar_debtormaster Set is_deleted = 1 WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function GetAutoCode($debtorname = null) {
        // function untuk menggenerate kode contact
        $xcode = null;
        $ckode = null;
        $credcd = null;
        $nol = "0000";
        $ins = strtoupper(substr($debtorname, 0, 1)) . "-";
        $this->connector->CommandText = "SELECT debtor_cd FROM ar_debtor_master WHERE LEFT(debtor_cd,2) = ?ins ORDER BY debtor_cd DESC LIMIT 1";
        $this->connector->AddParameter("?ins", $ins);
        $rs = $this->connector->ExecuteQuery();
        if ($rs != null) {
            $row = $rs->FetchAssoc();
            $credcd = $row["debtor_cd"];
            if ($credcd == "") {
                return $xcode = $ins . "0001";
            } else {
                $num = substr($credcd, 2, 4);
                if (is_numeric($num)) {
                    $num = $num + 1;
                    return $xcode = $ins . substr($nol, 0, 4 - strlen($num)) . $num;
                } else {
                    $ins = strtoupper(substr($debtorname, 0, 1)) . "-00";
                    $this->connector->CommandText = "select debtor_cd from m_contacts Where left(debtor_cd,4) = ?ins Order By debtor_cd Desc limit 1";
                    $this->connector->AddParameter("?ins", $ins);
                    $rs = $this->connector->ExecuteQuery();
                    if ($rs != null) {
                        $row = $rs->FetchAssoc();
                        $credcd = $row["debtor_cd"];
                        $num = substr($credcd, 2, 4);
                        if (is_numeric($num)) {
                            $num = $num + 1;
                            return $xcode = $ins . substr($nol, 0, 2 - strlen($num)) . $num;
                        } else {
                            return $ins . substr($nol, 0, 2) . "1";
                        }
                    } else {
                        return $xcode = $ins . "0001";
                    }
                }
            }
        } else {
            return $xcode;
        }
	}

    public function GetJSonDebtor($entityId = 1 ,$sort = 'a.debtor_name', $order = 'ASC') {
        $sql = "SELECT a.id,a.debtor_cd as code,a.debtor_name as name FROM ar_debtor_master AS a Where a.is_deleted = 0 And a.entity_id = ?entityId";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?entityId", $entityId);
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        //$result = array('total'=>$data['count'],'rows'=>$rows);
        return $rows;
    }
}
