<?php
class InvoiceController extends AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->trxMonth = date('n');
        $this->trxYear = date ('Y');
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 80);
        $settings["columns"][] = array("name" => "a.invoice_date", "display" => "Date", "width" => 60);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "No. Invoice", "width" => 90);
        $settings["columns"][] = array("name" => "a.debtor_name", "display" => "Debtor Name", "width" => 200);
        $settings["columns"][] = array("name" => "a.reff_no", "display" => "Reff Number", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Payment", "width" => 60);
        $settings["columns"][] = array("name" => "a.due_date", "display" => "Due Date", "width" => 60);
        $settings["columns"][] = array("name" => "format(a.base_amount,0)", "display" => "Base Amount+", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.vat_amount,0)", "display" => "VAT+", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.wht_amount,0)", "display" => "WHT-", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.base_amount + a.vat_amount - a.wht_amount,0)", "display" => "Total", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,0)", "display" => "Paid", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.balance_amount,0)", "display" => "Outstanding", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Approved',if(a.invoice_status = 2,'Posted','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.invoice_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.project_name", "display" => "Project Name");
        $settings["filters"][] = array("name" => "a.invoice_date", "display" => "Invoice Date");
        $settings["filters"][] = array("name" => "a.debtor_name", "display" => "Debtor Name");
        $settings["filters"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Approved',if(a.invoice_status = 2,'Posted','Void')))", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "A/R Invoice";

            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ar.invoice/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.invoice", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ar.invoice/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data invoice",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.invoice", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ar.invoice/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ar.invoice", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ar.invoice/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data invoice","Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.invoice", "print")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Print Invoice", "Url" => "ar.invoice/invoice_print/invoice","Target"=>"_blank","Class" => "bt_print", "ReqId" => 2, "Confirm" => "Cetak Invoice yang dipilih?");
            }
            if ($acl->CheckUserAccess("ar.invoice", "view")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.invoice/overview","Target"=>"_blank","Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.invoice", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve", "Url" => "ar.invoice/approve", "Class" => "bt_process", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Approve", "Url" => "ar.invoice/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            if ($acl->CheckUserAccess("ar.invoice", "posting")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "ar.invoice/posting", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "ar.invoice/unposting", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ar_invoice_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "Year(a.invoice_date) = " . $this->trxYear ." And a.invoice_status < 3";
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* entry data penjualan*/
    public function add($invoiceId = 0) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/debtor.php");
        require_once(MODEL . "common/trx_type.php");
        require_once(MODEL . "common/ar_invoice_type.php");
        $loader = null;
        $invoice = new Invoice();
        if ($invoiceId > 0 ) {
            $invoice = $invoice->LoadById($invoiceId);
            if ($invoice == null) {
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.invoice");
            }
            if ($invoice->PaidAmount > 0) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah terbayar. Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->InvoiceStatus == 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Approve- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->InvoiceStatus == 2) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Posting- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->InvoiceStatus == 3) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Void- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->CreatebyId <> AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new Project();
        if ($this->userLevel < 5) {
            $project = $loader->LoadAllowedProject($this->userProjectIds);
        }else{
            $project = $loader->LoadByEntityId($this->userCompanyId);
        }
        //load debtor
        $loader = new Debtor();
        $debtors = $loader->LoadByEntity($this->userCompanyId);
        //load trxtype
        $loader = new TrxType();
        $trxtypes = $loader->LoadByModuleId($this->userCompanyId,3);
        //load trxtype
        $loader = new ArInvoiceType();
        $invtype = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("projects", $project);
        $this->Set("trxtypes", $trxtypes);
        $this->Set("invtypes", $invtype);
        $this->Set("debtors", $debtors);
        $this->Set("invoice", $invoice);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->InvoiceItemsCount($invoiceId));
    }

    public function proses_master($invId = 0) {
        $invoice = new Invoice();
        if (count($this->postData) > 0) {
            $invoice->Id = $invId;
            $invoice->EntityId = $this->userCompanyId;
            $invoice->ProjectId = $this->GetPostValue("ProjectId");
            $invoice->InvoiceDate = strtotime($this->GetPostValue("InvoiceDate"));
            $invoice->DueDate = strtotime($this->GetPostValue("DueDate"));
            $invoice->DebtorId = $this->GetPostValue("DebtorId");
            $invoice->InvoiceType = $this->GetPostValue("InvoiceType");
            $invoice->CreditTerms = $this->GetPostValue("CreditTerms");
            $invoice->InvoiceDescs = $this->GetPostValue("InvoiceDescs");
            $invoice->InvoiceNo = $this->GetPostValue("InvoiceNo");
            $invoice->ReffNo = $this->GetPostValue("ReffNo");
            $invoice->VatPct = $this->GetPostValue("VatPct");
            $invoice->WhtPct = $this->GetPostValue("WhtPct");
            $invoice->TaxInvoiceNo = $this->GetPostValue("TaxInvoiceNo");
            if ($invoice->CreditTerms > 0) {
                $invoice->PaymentType = 2;
            }else{
                $invoice->PaymentType = 1;
            }
            if ($invoice->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $invoice->InvoiceNo = $docCounter->AutoDocNoIv($invoice->EntityId, $invoice->InvoiceDate, 1);
                $invoice->CreatebyId = $this->userUid;
                $rs = $invoice->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$invoice->Id,$invoice->InvoiceNo);
                }else{
                    printf("ER|A|%d",$invoice->Id);
                }
            }else{
                $invoice->UpdatebyId = $this->userUid;
                $rs = $invoice->Update($invoice->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$invoice->Id,$invoice->InvoiceNo);
                }else{
                    printf("ER|U|%d",$invoice->Id);
                }
            }
        }else{
            printf("ER|X|%d",$invId);
        }
    }

    public function add_detail($invId = null) {
        $rst = null;
        $invoice = new Invoice($invId);
        $invdetail = new InvoiceDetail();
        $invdetail->InvoiceId = $invId;
        if (count($this->postData) > 0) {
            $invdetail->ItemId = $this->GetPostValue("aItemId");
            $invdetail->ItemCode = $this->GetPostValue("aItemCode");
            $invdetail->ItemName = $this->GetPostValue("aItemName");
            $invdetail->Qty = $this->GetPostValue("aQty");
            $invdetail->Price = $this->GetPostValue("aPrice");
            $invdetail->ItemDescs = '-';
            $invdetail->UomCd = $this->GetPostValue("aUomCd");
            // item baru simpan
            $rs = $invdetail->Insert() == 1;
            if ($rs > 0) {
                $rst = printf('OK|%s|Proses simpan data berhasil!',$invdetail->Id);
            } else {
                $rst = 'ER|Gagal proses simpan data!';
            }
        }else{
            $rst = "ER|No Data posted!";
        }
        print($rst);
    }
    
    public function delete_detail($id) {
        // Cek datanya
        $invdetail = new InvoiceDetail();
        $invdetail = $invdetail->FindById($id);
        if ($invdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($invdetail->Delete($id) == 1) {
            printf("Data Detail Invoice ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Invoice ID: %d gagal dihapus!",$id);
        }
    }

    public function delete($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Invoice terlebih dahulu.");
            redirect_url("ar.invoice");
            return;
        }

        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang diminta tidak ditemukan / sudah dihapus.");
            redirect_url("ar.invoice");
            return;
        }
        
        if ($invoice->InvoiceStatus > 0) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
            redirect_url("ar.invoice/view/" . $invoice->Id);
            return;
        }

        // Everything is green
        $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
        if ($invoice->Delete($invoice->Id)) {
            $this->persistence->SaveState("info", sprintf("Dokumen Invoice: %s sudah berhasil dihapus.", $invoice->InvoiceNo));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Invoice: %s ! Harap hubungi system administrator.<br />Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage()));
        }

        redirect_url("ar.invoice");
    }

    public function void($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Invoice terlebih dahulu.");
            redirect_url("ar.invoice");
            return;
        }

        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang diminta tidak ditemukan / sudah dihapus.");
            redirect_url("ar.invoice");
            return;
        }

        if ($invoice->InvoiceStatus > 0) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
            redirect_url("ar.invoice");
            return;
        }

        // Everything is green
        $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
        if ($invoice->Delete($invoice->Id)) {
            $this->persistence->SaveState("info", sprintf("Dokumen Invoice: %s sudah berhasil dihapus.", $invoice->InvoiceNo));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Invoice: %s ! Harap hubungi system administrator.<br />Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage()));
        }

        redirect_url("ar.invoice");
    }

    public function view($invoiceId = 0) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/debtor.php");
        require_once(MODEL . "common/trx_type.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $invoice = new Invoice();
        if ($invoiceId > 0 ) {
            $invoice = $invoice->LoadById($invoiceId);
            if ($invoice == null) {
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new Project();
        $project = $loader->LoadByEntityId($this->userCompanyId);
        //load debtor
        $loader = new Debtor();
        $debtors = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("projects", $project);
        $this->Set("debtors", $debtors);
        $this->Set("invoice", $invoice);
        $this->Set("acl", $acl);
    }

    public function getInvoiceItemRows($id){
        $invoice = new Invoice();
        $rows = $invoice->GetInvoiceItemRow($id);
        print($rows);
    }

    public function InvoiceItemsCount($id){
        $invoice = new Invoice();
        $rows = $invoice->GetInvoiceItemRow($id);
        return $rows;
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice yang akan di-approve");
            redirect_url("ap.invoice");
        }

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            $invoice = new Invoice($id);
            if ($invoice->InvoiceStatus == 0) {
                if ($invoice->Approve($id, $this->userUid)) {
                    $this->connector->CommitTransaction();
                    $infos[] = "Invoice No: ".$invoice->InvoiceNo." berhasil di-Approve!";
                } else {
                    $this->connector->RollbackTransaction();
                    $errors[] = "Invoice No: ".$invoice->InvoiceNo." gagal di-Approve!";
                }
            }else{
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." tidak berstatus -Draft-";
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice yang akan di-approve");
            redirect_url("ap.invoice");
        }

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            $invoice = new Invoice($id);
            if ($invoice->InvoiceStatus == 1) {
                if ($invoice->Unapprove($id, $this->userUid)) {
                    $this->connector->CommitTransaction();
                    $infos[] = "Invoice No: ".$invoice->InvoiceNo." berhasil di-Unapprove!";
                } else {
                    $this->connector->RollbackTransaction();
                    $errors[] = "Invoice No: ".$invoice->InvoiceNo." gagal di-Unapprove!";
                }
            }else{
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." tidak berstatus -Approve-";
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function posting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice yang akan di-posting");
            redirect_url("ap.invoice");
        }

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            $invoice = new Invoice($id);
            if ($invoice->InvoiceStatus <> 1){
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." tidak berstatus -Approved-";
                continue;
            }else {
                if ($invoice->InvoiceStatus == 1) {
                    if ($invoice->Posting($id, $this->userUid)) {
                        $this->connector->CommitTransaction();
                        $infos[] = "Invoice No: " . $invoice->InvoiceNo . " berhasil di-Posting!";
                    } else {
                        $this->connector->RollbackTransaction();
                        $errors[] = "Invoice No: " . $invoice->InvoiceNo . " gagal di-Posting!";
                    }
                }else{
                    $errors[] = "Invoice No: " . $invoice->InvoiceNo . " gagal di-Posting!";
                }
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function unposting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice yang akan di-posting");
            redirect_url("ap.invoice");
        }

        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $this->connector->BeginTransaction();
            $invoice = new Invoice($id);
            //cek status
            if ($invoice->InvoiceStatus <> 2){
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." tidak berstatus -Posted-";
                continue;
            }
            if ($invoice->PaidAmount > 0){
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." sudah terbayar!";
                continue;
            }
            if ($invoice->InvoiceStatus == 2) {
                if ($invoice->Unposting($id, $this->userUid)) {
                    $this->connector->CommitTransaction();
                    $infos[] = "Invoice No: ".$invoice->InvoiceNo." berhasil di-Unposting!";
                } else {
                    $this->connector->RollbackTransaction();
                    $errors[] = "Invoice No: ".$invoice->InvoiceNo." gagal di-Unposting!";
                }
            }else{
                $errors[] = "Invoice No: ".$invoice->InvoiceNo." tidak gagal di-Unposting!";
            }
        }

        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function overview() {
        require_once(MODEL . 'master/debtor.php');
        require_once(MODEL . "common/ar_invoice_type.php");

        if (count($this->getData) > 0) {
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $debtorId = $this->GetGetValue("debtorId");
            $status = $this->GetGetValue("status");
            $invTypeId = $this->GetGetValue("invTypeId", array());
            $output = $this->GetGetValue("output", "web");
            $groupBy = $this->GetGetValue("groupBy", -1);

            switch ($groupBy) {
                case 1:
                    $key = "debtor_name";
                    $groupByClause = "a.debtor_name, a.invoice_no ASC";
                    break;
                case 2:
                    $key = "billtype_desc";
                    $groupByClause = "d.invoice_type, a.invoice_no ASC";
                    break;
                default:
                    $key = null;
                    $groupBy = -1;
                    $groupByClause = "a.invoice_no ASC";
                    break;
            }

            $this->connector->CommandText =
                "SELECT a.*, b.entity_cd, c.debtor_cd, c.debtor_name, d.invoice_type, e.short_desc
FROM t_ar_invoice_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ar_debtor_master AS c ON a.debtor_id = c.id
	LEFT JOIN ar_invoicetype AS d ON a.invoice_type = d.id
	JOIN sys_status_code AS e ON a.invoice_status = e.code AND e.key = 'invoice_status'
WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND a.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }

            if ($debtorId != -1) {
                $this->connector->CommandText .= " AND c.id = ?debtor";
                $this->connector->AddParameter("?debtor", $debtorId);
            }

            if (count($invTypeId) > 0) {
                $this->connector->CommandText .= " AND d.id IN ?type";
                $this->connector->AddParameter("?type", $invTypeId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.invoice_status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.invoice_date BETWEEN ?start AND ?end ORDER BY $groupByClause";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $startDate = mktime(0, 0, 0, date("n"), 1);
            $endDate = mktime(0, 0, 0);
            $debtorId = -1;
            $status = -1;
            $invTypeId = array();
            $output = "web";
            $groupBy = -1;
            $key = null;
            $report = null;
        }

        $debtor = new \Debtor();
        $this->Set("debtors", $debtor->LoadByEntity($this->userCompanyId));
        $this->Set("debt", $debtor->FindById($debtorId));
        $this->Set("debtorId", $debtorId);

        $invType = new ArInvoiceType();
        $this->Set("invTypes", $invType->LoadByEntity($this->userCompanyId));

        $this->Set("status", $status);
        $this->Set("invTypeId", $invTypeId);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
        $this->Set("groupBy", $groupBy);
        $this->Set("key", $key);
    }
}


// End of File: invoice_controller.php
