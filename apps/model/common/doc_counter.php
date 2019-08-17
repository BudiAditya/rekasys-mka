<?php
class DocCounter extends EntityBase {
	public $Id;
	public $EntityId;
	public $EntityCd;
    public $DocTypeId;
	public $TrxMonth;
    public $TrxYear;
	public $Counter;
    public $IsLocked;

	// Helper Variable

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->EntityId = $row["entity_id"];
		$this->EntityCd = $row["entity_cd"];
        $this->DocTypeId = $row["doctype_id"];
		$this->TrxMonth = $row["trx_month"];
        $this->TrxYear = $row["trx_year"];
		$this->Counter = $row["counter"];
        $this->IsLocked = $row["is_locked"];
	}

	public function LoadAll($orderBy = "a.doc_code") {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_doccounter AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new DocType();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

	public function LoadById($id) {
		return $this->FindById($id);
	}

	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_doccounter AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
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

	/**
	 * Mengambil document counter berdasarkan jenis dokumen nya
	 *
	 * @param int $docType
	 * @param int $sbu
	 * @param int $date
	 * @return DocCounter|null
	 */
	public function LoadByDocType($docType, $sbu, $date) {
		$this->connector->CommandText =
"SELECT a.*, b.entity_cd
FROM cm_doccounter AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
WHERE a.doctype_id = ?docType AND a.entity_id = ?sbu AND a.trx_year = ?year AND a.trx_month = ?month";
		$this->connector->AddParameter("?docType", $docType);
		$this->connector->AddParameter("?sbu", $sbu);
		$this->connector->AddParameter("?year", date("Y", $date));
		$this->connector->AddParameter("?month", date("n", $date));

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO cm_doccounter(entity_id,doctype_id,trx_month,trx_year,counter,is_locked)
VALUES(?entity_id,?doctype_id,?trx_month,?trx_year,?counter,?is_locked)';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?doctype_id", $this->DocTypeId);
		$this->connector->AddParameter("?trx_month", $this->TrxMonth);
		$this->connector->AddParameter("?trx_year", $this->TrxYear);
		$this->connector->AddParameter("?counter", $this->Counter);
        $this->connector->AddParameter("?is_locked", $this->IsLocked);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE cm_doccounter SET
	doctype_id = ?doctype_id,
	trx_month = ?trx_month,
	trx_year = ?trx_year,
	counter = ?counter,
	is_locked = ?is_locked
WHERE id = ?id';
        $this->connector->AddParameter("?doctype_id", $this->DocTypeId);
        $this->connector->AddParameter("?trx_month", $this->TrxMonth);
        $this->connector->AddParameter("?trx_year", $this->TrxYear);
        $this->connector->AddParameter("?counter", $this->Counter);
        $this->connector->AddParameter("?is_locked", $this->IsLocked);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM cm_doccounter WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	/**
	 * Berguna untuk mengambil data nomor dokumen yang sifatnya running number.
	 * Running number akan berbeda-beda berdasarkan jenis dokumen dan tanggal. Harap menggunakan AutoDocNoXxx() untuk spesifik per dokumen
	 * NOTE: Pada database itu menyimpan nomor terakhir sehingga sebelum kita return counter harus di +1
	 *
	 * @param int $EntityId
	 * @param int $RequestedDocType
	 * @param int $Tgl
	 * @param int $Mode        (0 = Read Counter Only, 1 = Read Counter then Increase by One)
	 * @throws Exception
	 * @return null|string return null jika sudah locked statusnya
	 */
	public function AutoDocNo($EntityId, $RequestedDocType, $Tgl, $Mode){
		if (!is_int($Tgl)) {
			$Tgl = time();
		}
        $Tahun = date("Y",$Tgl);
        $Bulan = date("m",$Tgl);
        $result = null;

		// Step 1: Cek apakah dokumen yang diminta berupa id atau kode
		require_once(MODEL . "common/doc_type.php");
		$docType = new DocType();
		if (is_numeric($RequestedDocType)) {
			$docType = $docType->FindById($RequestedDocType);
		} else {
			$docType = $docType->FindByCode($RequestedDocType);
		}
		if ($docType == null) {
			throw new Exception("Requested Document Type is not registered yet ! Requested Document Type: " . $RequestedDocType);
		}

		// Step 2-1: Query buat ambil document counter dynamic
			$this->connector->CommandText = "SELECT a.counter, a.is_locked FROM cm_doccounter AS a WHERE a.entity_id = ?entity_id AND a.doctype_id = ?doctype_id AND a.trx_year = ?trx_year AND a.trx_month = ?trx_month";

		// Step 2-2: Execute Query
		$this->connector->AddParameter("?entity_id", $EntityId);
		$this->connector->AddParameter("?doctype_id", $docType->Id);
		$this->connector->AddParameter("?trx_year", $Tahun);
		$this->connector->AddParameter("?trx_month", $Bulan);
		$reader = $this->connector->ExecuteQuery();


		// Step 3: Proses nomor document counter
		if ($reader == null) {
			// Hmmm klo ini harusnya pure database error...
			throw new Exception("Failed to get data from Document Counter ! DBase error: " . $this->connector->GetErrorMessage());
		}

		// Step 2-1: Jika nomor dokumen tidak ada karena sudah ganti bulan atau tahun...
		if ($reader->GetNumRows() == 0) {
			$counter = 0;
			// Ops... no record for current month and year for a specific document...
			$this->connector->CommandText = "INSERT INTO cm_doccounter (entity_id,doctype_id,trx_year,trx_month,counter,is_locked) VALUES(?entity_id,?doctype_id,?trx_year,?trx_month,$counter,0)";

			$rs = $this->connector->ExecuteNonQuery();
			if ($rs != 1) {
				throw new Exception("Failed to insert data to Document Counter ! DBase error: " . $this->connector->GetErrorMessage());
			}

			$isLocked = false;
		} else {
			// OK data counter sudah ada...
			$row = $reader->FetchAssoc();

			$counter = $row["counter"];
			$isLocked = $row["is_locked"] == 1;
		}

		// Step 2-2: Generate nomor dokumennya... semua data sudah ada
		if ($isLocked) {
			// OK Tell user this document counter have been locked
			return null;
		}
		$result = sprintf('%s/%s/%02s/%06s',$docType->DocCode,$Tahun,$Bulan,$counter+1);

		// Step 3: Update document counter if specified by mode
		if ($Mode == 1) {
			$this->connector->CommandText = "UPDATE cm_doccounter SET counter = counter + 1 WHERE entity_id = ?entity_id AND doctype_id = ?doctype_id AND trx_year = ?trx_year AND trx_month = ?trx_month";
			$rs = $this->connector->ExecuteNonQuery();
			if ($rs != 1) {
				throw new Exception("Failed to increment Document Counter ! DBase error: " . $this->connector->GetErrorMessage());
			}
		}

		return $result;
    }

	public function AutoDocNoMr($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 17, $tgl, $mode);
	}

	public function AutoDocNoPr($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 16, $tgl, $mode);
	}

    public function AutoDocNoRr($sbu, $tgl, $mode) {
        return $this->AutoDocNo($sbu, 22, $tgl, $mode);
    }

	public function AutoDocNoPo($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 13, $tgl, $mode);
	}

    public function AutoDocNoRo($sbu, $tgl, $mode) {
        return $this->AutoDocNo($sbu, 23, $tgl, $mode);
    }

	public function AutoDocNoGn($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 5, $tgl, $mode);
	}

    public function AutoDocNoRn($sbu, $tgl, $mode) {
        return $this->AutoDocNo($sbu, 24, $tgl, $mode);
    }

	public function AutoDocNoIs($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 9, $tgl, $mode);
	}

	public function AutoDocNoNpkp($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 18, $tgl, $mode);
	}

	public function AutoDocNoAdjustmentInventory($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 19, $tgl, $mode);
	}

	public function AutoDocNoSo($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 20, $tgl, $mode);
	}

	public function AutoDocNoIv($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 10, $tgl, $mode);
	}

	public function AutoDocNoOpAr($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 11, $tgl, $mode);
	}

	public function AutoDocNoOr($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 12, $tgl, $mode);
	}

	public function AutoDocNoBk($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 2, $tgl, $mode);
	}

	public function AutoDocNoBm($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 3, $tgl, $mode);
	}

	public function AutoDocNoPv($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 14, $tgl, $mode);
	}

	public function AutoDocNoCn($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 4, $tgl, $mode);
	}

	public function AutoDocNoAj($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 1, $tgl, $mode);
	}

	public function AutoDocNoRe($sbu, $tgl, $mode) {
		return $this->AutoDocNo($sbu, 15, $tgl, $mode);
	}
}
