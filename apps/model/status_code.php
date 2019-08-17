<?php
class StatusCode extends EntityBase {
	public $Key;
	public $Code;
	public $ShortDesc;
	public $Urutan;
	public $Desc;

	public function FillProperties(array $row) {
		$this->Key = $row["key"];
		$this->Code = $row["code"];
		$this->ShortDesc = $row["short_desc"];
		$this->Urutan = $row["urutan"];
		$this->Desc = $row["desc"];
	}

	/**
	 * @param string $key
	 * @return StatusCode[]
	 */
	private function Load($key) {
		$this->connector->CommandText =
"SELECT a.*
FROM sys_status_code AS a
WHERE a.key = ?key
ORDER BY a.code";
		$this->connector->AddParameter("?key", $key);
		$rs = $this->connector->ExecuteQuery();

		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new StatusCode();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadLoginAudit() {
		return $this->Load("login_audit");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadLotStatus() {
		return $this->Load("lot_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadUserAction() {
		return $this->Load("user_action");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadMrStatus() {
		return $this->Load("mr_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadPrStatus() {
		return $this->Load("pr_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadPoStatus() {
		return $this->Load("po_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadGnStatus() {
		return $this->Load("gn_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadIsStatus() {
		return $this->Load("is_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadStockType() {
		return $this->Load("stock_type");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadVoucherStatus() {
		return $this->Load("voucher_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadAccType() {
		return $this->Load("acc_type");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadNpkpStatus() {
		return $this->Load("npkp_status");
	}

	/**
	 * @return StatusCode[]
	 */
	public function LoadReceiptStatus() {
		return $this->Load("receipt_status");
	}

    public function LoadPaymentStatus() {
        return $this->Load("payment_status");
    }

	/**
	 * @param string $key
	 * @param int $code
	 * @return StatusCode
	 */
	public function FindBy($key, $code) {
		$this->connector->CommandText = "SELECT a.* FROM sys_status_code AS a WHERE a.key = ?key AND a.code = ?code";
		$this->connector->AddParameter("?key", $key);
		$this->connector->AddParameter("?code", $code);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}
}