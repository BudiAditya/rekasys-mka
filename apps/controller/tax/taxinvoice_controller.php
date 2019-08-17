<?php
class TaxInvoiceController extends AppController {
    private $userCompanyId;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "tax/taxinvoice.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
    }

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 50);
		$settings["columns"][] = array("name" => "a.source_from", "display" => "Sumber", "width" => 50);
		$settings["columns"][] = array("name" => "a.taxinvoice_date", "display" => "Tgl Invoice", "width" => 80);
        $settings["columns"][] = array("name" => "a.taxinvoice_no", "display" => "Nomor Seri Faktur", "width" => 110);
        $settings["columns"][] = array("name" => "a.relasi_name", "display" => "Relasi", "width" => 200);
        $settings["columns"][] = array("name" => "a.reff_no", "display" => "Invoice/Reff No.", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.tax_mode = 1,'Masukan','Keluaran')", "display" => "Mode", "width" => 50);
        $settings["columns"][] = array("name" => "a.tax_type", "display" => "Jenis", "width" => 100);
		$settings["columns"][] = array("name" => "format(a.tax_rate,2)", "display" => "%", "width" => 30, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.dpp_amount,0)", "display" => "DPP", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.tax_amount,0)", "display" => "Pajak", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.taxiv_status = 0,'Draft','Posted')", "display" => "Status", "width" => 50);
        $settings["columns"][] = array("name" => "a.tgl_lapor", "display" => "Tgl Lapor", "width" => 80);
        $settings["columns"][] = array("name" => "a.voucher_no", "display" => "No. Jurnal", "width" => 100);

		$settings["filters"][] = array("name" => "a.taxinvoice_no", "display" => "No. Faktur");
        $settings["filters"][] = array("name" => "a.reff_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.relasi_name", "display" => "Nama Relasi");
        $settings["filters"][] = array("name" => "a.taxinvoice_date", "display" => "Tgl Invoice");
        $settings["filters"][] = array("name" => "a.tgl_lapor", "display" => "Tgl Lapor");
        $settings["filters"][] = array("name" => "if(a.taxiv_status = 0,'Draft','Posted')", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Faktur Pajak";
			if ($acl->CheckUserAccess("taxinvoice", "add", "tax")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "tax.taxinvoice/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("taxinvoice", "edit", "tax")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "tax.taxinvoice/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Mohon memilih Faktur Pajak terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 Faktur Pajak",
											   "Confirm" => "Apakah anda mau merubah data Faktur Pajak yang dipilih ?");
			}
			if ($acl->CheckUserAccess("taxinvoice", "delete", "tax")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "tax.taxinvoice/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Mohon memilih Faktur Pajak terlebih dahulu sebelum melakukan proses edit !\n\nHarap memilih tepat 1 Faktur Pajak",
											   "Confirm" => "Apakah anda mau menghapus data Faktur Pajak yang dipilih ?");
			}

			if ($acl->CheckUserAccess("taxinvoice", "posting","tax")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "tax.taxinvoice/posting", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Faktur terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Posting Faktur Pajak yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "tax.taxinvoice/unposting", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Faktur terlebih dahulu sebelum proses pembatalan posting.",
                    "Confirm" => "Apakah anda mau membatalkan posting Faktur Pajak yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

			$settings["def_filter"] = 0;
			$settings["def_order"] = 2;
			$settings["singleSelect"] = false;
		} else {
			$settings["from"] = "vw_tax_invoice AS a";
			$settings["where"] = "a.entity_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    public function add() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "tax/taxtype.php");
        $taxInvoice = new TaxInvoice();
        $loader = null;
        // load combobox data
        $company = new Company();
		$company = $company->LoadById($this->userCompanyId);
        $loader = new TaxType();
        $taxtypes = $loader->LoadByEntity($this->userCompanyId);
        $this->Set("taxtypes", $taxtypes);
        $this->Set("company", $company);
        $this->Set("taxInvoice", $taxInvoice);
    }

    public function simpan($id = 0) {
        $taxInvoice = new TaxInvoice();
        $loader = null;
        $out = "ER|0";
        if (count($this->postData) > 0) {
            // OK user ada kirim data kita proses
            $taxInvoice->EntityId = $this->userCompanyId;
            $taxInvoice->SourceFrom = 'Manual';
            $taxInvoice->ReffNo = $this->GetPostValue("ReffNo");
            $taxInvoice->TaxInvoiceNo = $this->GetPostValue("TaxInvoiceNo");
            $taxInvoice->TaxInvoiceDate = $this->GetPostValue("TaxInvoiceDate");
            $taxInvoice->TglLapor = $this->GetPostValue("TglLapor");
            $taxInvoice->DbCrId = $this->GetPostValue("DbCrId");
            $taxInvoice->TaxTypeId = $this->GetPostValue("TaxTypeId");
            $taxInvoice->DppAmount = $this->GetPostValue("DppAmount");
            $taxInvoice->TaxAmount = $this->GetPostValue("TaxAmount");
            $taxInvoice->TaxMode = $this->GetPostValue("TaxMode");
            $taxInvoice->TaxRate = $this->GetPostValue("TaxRate");
            $taxInvoice->TaxivStatus = 0;
            $taxInvoice->CreatebyId = $this->userUid;
            $taxInvoice->UpdatebyId = $this->userUid;
            if ($id > 0){
                if ($taxInvoice->Update($id)) {
                    $out = "OK|1";
                    $taxInvoice->UpdateTaxInvoice();
                } else {
                    if ($this->connector->GetHasError()) {
                        if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                            $out = "ER|" . sprintf("No. Faktur: '%s' telah ada pada database !", $taxInvoice->TaxInvoiceNo);
                        } else {
                            $out = "ER|" . sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage());
                        }
                    }
                }
            }else {
                if ($taxInvoice->Insert()) {
                    $taxInvoice->UpdateTaxInvoice();
                    $out = "OK|1";
                } else {
                    if ($this->connector->GetHasError()) {
                        if ($this->connector->GetErrorCode() == $this->connector->GetDuplicateErrorCode()) {
                            $out = "ER|" . sprintf("No. Faktur: '%s' telah ada pada database !", $taxInvoice->TaxInvoiceNo);
                        } else {
                            $out = "ER|" . sprintf("System Error: %s. Please Contact System Administrator.", $this->connector->GetErrorMessage());
                        }
                    }
                }
            }
        }
        print ($out);
    }

    public function edit($id = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "tax/taxtype.php");
        $taxInvoice = new TaxInvoice();
        $loader = null;
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data Faktur Pajak sebelum melakukan edit data !");
            redirect_url("tax.taxinvoice");
        }
        $taxInvoice = $taxInvoice->FindById($id);
        if ($taxInvoice == null) {
            $this->persistence->SaveState("error", "Data Faktur Pajak yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("tax.taxinvoice");
        }
        if ($taxInvoice->TaxivStatus == 1) {
            $this->persistence->SaveState("error", "Faktur Pajak yang dipilih tidak berstatus -DRAFT-");
            redirect_url("tax.taxinvoice");
        }
        // load combobox data
        $company = new Company();
		$company = $company->LoadById($this->userCompanyId);
        $loader = new TaxType();
        $taxtypes = $loader->LoadByEntity($this->userCompanyId);
        $this->Set("taxtypes", $taxtypes);
        $this->Set("company", $company);
        $this->Set("taxInvoice", $taxInvoice);
    }

    private function DoUpdate(TaxInvoice $taxInvoice) {
        if ($taxInvoice->SourceFrom == "") {
            $this->Set("error", "Kode Tax Type masih kosong");
            return false;
        }

        if ($taxInvoice->TaxInvoice == "") {
            $this->Set("error", "Nama Tax Type masih kosong");
            return false;
        }

        if ($taxInvoice->Update($taxInvoice->Id) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Anda harus memilih data Faktur Pajak sebelum melakukan hapus data !");
            redirect_url("tax.taxinvoice");
        }

        $taxInvoice = new TaxInvoice();
        $taxInvoice = $taxInvoice->FindById($id);
        if ($taxInvoice == null) {
            $this->persistence->SaveState("error", "Data Faktur Pajak yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
            redirect_url("tax.taxinvoice");
        }

        if ($taxInvoice->TaxivStatus == 1) {
            $this->persistence->SaveState("error", "Faktur Pajak yang dipilih tidak berstatus -DRAFT-");
            redirect_url("tax.taxinvoice");
        }
        /** @var $taxInvoice TaxInvoice */
        if ($taxInvoice->Delete($id,$taxInvoice->ReffNo,$taxInvoice->TaxMode) == 1) {
            $this->persistence->SaveState("info", sprintf("Data Faktur Pajak: '%s' telah berhasil dihapus.", $taxInvoice->TaxInvoiceNo));
            redirect_url("tax.taxinvoice");
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal menghapus data Faktur Pajak: '%s'. Message: %s", $taxInvoice->TaxInvoiceNo, $this->connector->GetErrorMessage()));
        }
        redirect_url("tax.taxinvoice");
    }

    public function getcreditor_json(){
        require_once (MODEL . "master/creditor.php");
        $creditor = new Creditor();
        $creditor = $creditor->GetJSonCreditor($this->userCompanyId);
        echo json_encode($creditor);
    }

    public function getdebtor_json(){
        require_once (MODEL . "master/debtor.php");
        $debtor = new Debtor();
        $debtor = $debtor->GetJSonDebtor($this->userCompanyId);
        echo json_encode($debtor);
    }

    public function getarinvoice_json($debtorId){
        require_once (MODEL . "ar/invoice.php");
        $invoice = new Invoice();
        $invoice = $invoice->GetJSonInvoiceNonFakturPajak($debtorId);
        echo json_encode($invoice);
    }

    public function getapinvoice_json($creditorId){
        require_once (MODEL . "ap/invoice.php");
        $invoice = new \Ap\Invoice();
        $invoice = $invoice->GetJSonInvoiceNonFakturPajak($creditorId);
        echo json_encode($invoice);
    }

    public function getDataApInvoice(){
        require_once (MODEL . "ap/invoice.php");
        $out = "ER|0";
        if (count($this->postData) > 0) {
            $creditorId = $this->GetPostValue("relasiId");
            $invoiceNo = $this->GetPostValue("reffNo");
            $invoice = new Ap\Invoice();
            $invoice = $invoice->LoadByReffNo($invoiceNo);
            if ($invoice != null) {
                //cek creditor id
                if ($creditorId != $invoice->CreditorId) {
                    $out = "ER|1";
                } else {
                    $out = "OK|" . $invoice->Id . "|" . $invoice->FormatInvoiceDate(SQL_DATEONLY) . "|" . $invoice->TaxInvoiceNo . "|" . $invoice->BaseAmount;
                }
            }
        }
        print ($out);
    }

    public function getDataArInvoice(){
        require_once (MODEL . "ar/invoice.php");
        $out = "ER|0";
        if (count($this->postData) > 0) {
            $debtorId = $this->GetPostValue("relasiId");
            $invoiceNo = $this->GetPostValue("reffNo");
            $invoice = new Invoice();
            $invoice = $invoice->LoadByReffNo($invoiceNo);
            if ($invoice != null) {
                //cek debtor id
                if ($debtorId != $invoice->DebtorId) {
                    $out = "ER|1";
                } else {
                    $out = "OK|" . $invoice->Id . "|" . $invoice->FormatInvoiceDate(SQL_DATEONLY) . "|" . $invoice->TaxInvoiceNo . "|" . $invoice->BaseAmount;
                }
            }
        }
        print ($out);
    }

    public function posting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih Faktur Pajak yang akan di-posting");
            redirect_url("tax.taxinvoice");
        }

        require_once(MODEL . "accounting/voucher.php");
        require_once(MODEL . "tax/taxtype.php");
        require_once(MODEL . "common/doc_counter.php");

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            if ($this->doPosting($id, $infos, $errors)) {
                $this->connector->CommitTransaction();
            } else {
                $this->connector->RollbackTransaction();
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("tax.taxinvoice");
    }

    private function doPosting($id, &$infos, &$errors) {
        require_once (MODEL . "master/company.php");

        $taxinvoice = new TaxInvoice();
        $taxinvoice = $taxinvoice->LoadById($id);
        if ($taxinvoice == null) {
            // Invoice tidak ditemukan atau sudah dihapus. Skip dengan return false tanpa error message
            return false;
        }
        /** @var  $taxinvoice TaxInvoice  */
        if ($taxinvoice->TaxivStatus != 0) {
            $errors[] = sprintf("Maaf Faktur Pajak: %s tidak diproses karena statusnya bukan Draft! Status Dokumen: %s", $taxinvoice->TaxInvoiceNo, $taxinvoice->GetStatus());
            return false;
        }
        //check tgl lapor
        if ($taxinvoice->TglLapor >= $taxinvoice->TaxInvoiceDate) {
            // Proses posting
            $userId = \AclManager::GetInstance()->GetCurrentUser()->Id;
            $docCouter = new \DocCounter();
            $noVoucher = $docCouter->AutoDocNoAj($taxinvoice->EntityId, $taxinvoice->TglLapor, 1);
            if ($noVoucher === null) {
                $errorMessages[] = "Tidak dapat membuat VOUCHER ADJUSTMENT !";
                return false;
            }

            // #0q: Buat Master Voucher
            $voucher = new \Voucher();
            $voucher->DocumentTypeId = 1;
            $voucher->DocumentNo = $noVoucher;
            $voucher->Date = $taxinvoice->TglLapor;
            $voucher->EntityId = $taxinvoice->EntityId;
            $voucher->Note = 'Adjustment PPN In Transit - ' . $taxinvoice->ReffNo;
            $voucher->VoucherSource = "TAX MANAGEMENT";
            $voucher->CreatedById = $userId;
            $voucher->StatusCode = 4;                        // Masuk dalam status POSTED
            // #03: Detail Voucher
            $sqn = 1;
            // #03.1: Detail Utama
            $voucherDetail = new \VoucherDetail();
            $taxType = new TaxType();
            $taxType = $taxType->LoadById($taxinvoice->TaxTypeId);
            if ($taxType === null) {
                $errorMessages[] = "Tax Type tidak dikenal !";
                return false;
            }
            $voucherDetail->Sequence = $sqn;
            $voucherDetail->AccDebitId = $taxType->PostAccId;
            // Selalu gunakan Akun Kredit yang ada pada CreditorType
            $voucherDetail->AccCreditId = $taxType->TempAccId;
            $voucherDetail->Amount = $taxinvoice->TaxAmount;                        // Agar nilainya konsisten dengan laporan lainnya maka gunakan yang ada pada Property Dpp
            $voucherDetail->Note = 'No. Faktur Pajak: ' . $taxinvoice->TaxInvoiceNo;
            $voucherDetail->CreditorId = $taxinvoice->DbCrId;
            $voucher->Details[] = $voucherDetail;
            $sqn++;

            // #04: Simpan data Voucher Master
            $rs = $voucher->Insert();
            if ($rs != 1) {
                $errors[] = sprintf("Gagal buat Master Voucher Faktur: %s. Message: %s", $taxinvoice->TaxInvoiceNo, $this->connector->GetErrorMessage());
                return false;
            }

            // #04.1: Simpan data Voucher Detail
            $voucherDetail->VoucherId = $voucher->Id;
            $rs = $voucherDetail->Insert();
            if ($rs != 1) {
                $errors[] = sprintf("Gagal buat Detail Voucher Faktur: %s. Message: %s", $taxinvoice->TaxInvoiceNo, $this->connector->GetErrorMessage());
                return false;
            }

            // #05: Flag Invoice Supplier sebagai posted
            $taxinvoice->UpdatebyId = $userId;
            $taxinvoice->VoucherNo = $noVoucher;
            $taxinvoice->PostbyId = $userId;
            $rs = $taxinvoice->Post($taxinvoice->Id);
            if ($rs != 1) {
                $errors[] = sprintf("Gagal posting Faktur Pajak: %s. Error: %s", $taxinvoice->TaxInvoiceNo, $this->connector->GetErrorMessage());
                return false;
            }
            $infos[] = sprintf("Jurnal Adjustment PPN In Transit: %s sudah berhasil dibuat.", $taxinvoice->VoucherNo);
            return true;
        }else{
            $errors[] = sprintf("Tgl Lapor Faktur Pajak: %s belum diisi! (Minimal = Tgl Invoice)", $taxinvoice->TaxInvoiceNo);
            return false;
        }
    }

    public function unposting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih Faktur Pajak yang akan di-unposting");
            redirect_url("tax.taxinvoice");
        }

        require_once(MODEL . "accounting/voucher.php");
        require_once(MODEL . "tax/taxtype.php");
        require_once(MODEL . "common/doc_counter.php");

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            if ($this->doUnPosting($id, $infos, $errors)) {
                $this->connector->CommitTransaction();
            } else {
                $this->connector->RollbackTransaction();
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("tax.taxinvoice");
    }

    private function doUnPosting($id, &$infos, &$errors) {
        //proses unposting
        $taxinvoice = new TaxInvoice();
        $taxinvoice = $taxinvoice->LoadById($id);
        if ($taxinvoice == null) {
            // Biarkan saja ini data tidak ketemu tidak perlu set info atau error
            return false;
        }
        /** @var $taxinvoice TaxInvoice */
        if ($taxinvoice->TaxivStatus != 1) {
            $errors[] = sprintf("Maaf Faktur Pajak: %s tidak diproses karena tidak berstatus POSTED ! Status Dokumen: %s", $taxinvoice->TaxInvoiceNo, $taxinvoice->GetStatus());
            return false;
        }

        // OK mari kita mulai prosedur unposting
        // #01: Cari vouchernya dan hapus... (No Voucher akan sama dengan no Invoice Supplier)
        $voucher = new \Voucher();
        $rs = $voucher->DeleteByDocNo($taxinvoice->VoucherNo);
        if ($rs == -1) {
            $errors[] = sprintf("Gagal unposting Faktur Pajak (gagal hapus voucher): %s. Error: %s", $taxinvoice->TaxInvoiceNo, $this->connector->GetErrorMessage());
            return false;
        } else if ($rs == 0) {
            // Ini aneh... status posted tapi ga ketemu vouchernya pas saat hapus...
            $infos[] = sprintf("NOTICE: Faktur Pajak: %s tidak memiliki Voucher tetapi status POSTED.", $taxinvoice->TaxInvoiceNo);
        }

        // #02: Unposting status Invoice Supplier
        $taxinvoice->UpdatebyId = \AclManager::GetInstance()->GetCurrentUser()->Id;
        $rs = $taxinvoice->UnPost($taxinvoice->Id);
        if ($rs != 1) {
            $errors[] = sprintf("Gagal Unposting Faktur Pajak: %s. Error: %s", $taxinvoice->TaxInvoiceNo, $this->connector->GetErrorMessage());
            return false;
        }

        $infos[] = sprintf("Faktur Pajak: %s sudah berhasil di Unposting.", $taxinvoice->TaxInvoiceNo);
        return true;
    }
}
