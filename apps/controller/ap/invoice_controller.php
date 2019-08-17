<?php
namespace Ap;

/**
 * Class InvoiceController
 * @package Ap
 *
 * Berhubung live server sudah menggunakan PHP 5.3 mari kita gunakan semua fasilitas yang ada..
 */
class InvoiceController extends \AppController {
    private $userCompanyId;
    private $userProjectIds;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    /**
     * @var \CreditorType[]
     */
    private $creditorTypes = array();

    protected function Initialize() {
        require_once(MODEL . "ap/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userProjectIds = $this->persistence->LoadState("allow_projects_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userUid = \AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->trxMonth = date('n');
        $this->trxYear = date ('Y');
    }

    public function index() {
        $router = \Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "a.project_name", "display" => "Project", "width" => 80);
        $settings["columns"][] = array("name" => "a.invoice_date", "display" => "Date", "width" => 60);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "No. Invoice", "width" => 100);
        $settings["columns"][] = array("name" => "a.creditor_name", "display" => "Vendor/Supplier Name", "width" => 200);
        $settings["columns"][] = array("name" => "a.reff_no", "display" => "Reff Number", "width" => 120);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Payment", "width" => 60);
        $settings["columns"][] = array("name" => "a.due_date", "display" => "Due Date", "width" => 60);
        $settings["columns"][] = array("name" => "format(a.base_amount,0)", "display" => "Base Amount+", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.disc1_amount,0)", "display" => "Discount-", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.tax1_amount,0)", "display" => "Tax 1+", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.tax2_amount,0)", "display" => "Tax 2+", "width" => 60, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.base_amount + a.tax1_amount + a.tax2_amount,0)", "display" => "Total", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,0)", "display" => "Paid", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.balance_amount,0)", "display" => "Outstanding", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Approved',if(a.invoice_status = 2,'Posted','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.invoice_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.project_name", "display" => "Project Name");
        $settings["filters"][] = array("name" => "a.invoice_date", "display" => "Invoice Date");
        $settings["filters"][] = array("name" => "a.creditor_name", "display" => "Creditor Name");
        $settings["filters"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Approved',if(a.invoice_status = 2,'Posted','Void')))", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = \AclManager::GetInstance();
            $settings["title"] = "A/P Invoice";

            if ($acl->CheckUserAccess("ap.invoice", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.invoice/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.invoice", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.invoice/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data invoice",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.invoice", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ap.invoice/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.invoice", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.invoice/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data invoice","Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.invoice", "print")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Print Invoice", "Url" => "ap.invoice/invoice_print/invoice","Target"=>"_blank","Class" => "bt_print", "ReqId" => 2, "Confirm" => "Cetak Invoice yang dipilih?");
            }
            if ($acl->CheckUserAccess("ap.invoice", "view")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.invoice/overview","Target"=>"_blank","Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.invoice", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve", "Url" => "ap.invoice/approve", "Class" => "bt_process", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Approve", "Url" => "ap.invoice/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            if ($acl->CheckUserAccess("ap.invoice", "posting")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "ap.invoice/posting", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "ap.invoice/unposting", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Invoice terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ap_invoice_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.balance_amount > 0 And a.invoice_status < 3";
            }
        }

        $dispatcher = \Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* entry data penjualan*/
    public function add($invoiceId = 0) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "common/trx_type.php");
        require_once(MODEL . "common/ap_invoice_type.php");
        require_once(MODEL . "master/activity.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "master/units.php");
        require_once(MODEL . "tax/taxtype.php");
        $loader = null;
        $invoice = new Invoice();
        if ($invoiceId > 0 ) {
            $invoice = $invoice->LoadById($invoiceId);
            if ($invoice == null) {
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.invoice");
            }
            if ($invoice->PaidAmount > 0) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah terbayap. Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ap.invoice");
            }
            if ($invoice->InvoiceStatus == 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Approve- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ap.invoice");
            }
            if ($invoice->InvoiceStatus == 2) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Posting- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ap.invoice");
            }
            if ($invoice->InvoiceStatus == 3) {
                $this->persistence->SaveState("error", sprintf("Maaf Invoice No. %s sudah di-Void- Tidak boleh diubah lagi..", $invoice->InvoiceNo));
                redirect_url("ap.invoice");
            }
            if ($invoice->CreatebyId <> \AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$invoice->InvoiceNo));
                redirect_url("ap.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new \Project();
        if ($this->userLevel < 5) {
            $project = $loader->LoadAllowedProject($this->userProjectIds);
        }else{
            $project = $loader->LoadByEntityId($this->userCompanyId);
        }
        //load creditor
        $loader = new \Creditor();
        $creditors = $loader->LoadByEntity($this->userCompanyId);
        //load dept
        $loader = new \Department();
        $depts = $loader->LoadByEntityId($this->userCompanyId);
        //load units
        $loader = new \Units();
        $units = $loader->LoadAll($this->userCompanyId);
        //load activity
        $loader = new \Activity();
        $activitys = $loader->LoadByEntityId($this->userCompanyId);
        //load trxtype
        $loader = new \TrxType();
        $trxtypes = $loader->LoadByModuleId($this->userCompanyId,2);
        //load trxtype
        $loader = new \ApInvoiceType();
        $invtype = $loader->LoadByEntity($this->userCompanyId);
        //load taxtype
        $loader = new \TaxType();
        $taxtype = $loader->LoadByMode($this->userCompanyId,1);
        $this->Set("taxtypes", $taxtype);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("projects", $project);
        $this->Set("trxtypes", $trxtypes);
        $this->Set("invtypes", $invtype);
        $this->Set("creditors", $creditors);
        $this->Set("depts", $depts);
        $this->Set("units", $units);
        $this->Set("activitys", $activitys);
        $this->Set("invoice", $invoice);
        $acl = \AclManager::GetInstance();
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
            $invoice->CreditorId = $this->GetPostValue("CreditorId");
            $invoice->InvoiceType = $this->GetPostValue("InvoiceType");
            $invoice->CreditTerms = $this->GetPostValue("CreditTerms");
            $invoice->InvoiceDescs = $this->GetPostValue("InvoiceDescs");
            $invoice->InvoiceNo = $this->GetPostValue("InvoiceNo");
            $invoice->ReffNo = $this->GetPostValue("ReffNo");
            $invoice->GrnNo = $this->GetPostValue("GrnNo");
            $invoice->TaxInvoiceNo = $this->GetPostValue("TaxInvoiceNo");
            $invoice->TaxType1Id = $this->GetPostValue("TaxType1Id");
            $invoice->TaxType2Id = $this->GetPostValue("TaxType2Id");
            $invoice->Tax1Rate = $this->GetPostValue("Tax1Rate");
            $invoice->Tax2Rate = $this->GetPostValue("Tax2Rate");
            $invoice->Disc1Pct = $this->GetPostValue("Disc1Pct");
            $invoice->Disc1Amount = $this->GetPostValue("Disc1Amount");
            if ($invoice->CreditTerms > 0) {
                $invoice->PaymentType = 2;
            }else{
                $invoice->PaymentType = 1;
            }
            if ($invoice->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new \DocCounter();
                $invoice->InvoiceNo = $docCounter->AutoDocNo($invoice->EntityId,$invoice->InvoiceType, $invoice->InvoiceDate, 1);
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
            $invdetail->ItemDescs = $this->GetPostValue("aItemDescs");
            $invdetail->ActivityId = $this->GetPostValue("aActivityId");
            $invdetail->DeptId = $this->GetPostValue("aDeptId");
            $invdetail->UnitId = $this->GetPostValue("aUnitId");
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

    public function edit_detail($dId = null) {
        $rst = null;
        $invdetail = new InvoiceDetail();
        $invdetail = $invdetail->LoadById($dId);
        if (count($this->postData) > 0) {
            $invdetail->ItemId = $this->GetPostValue("aItemId");
            $invdetail->ItemCode = $this->GetPostValue("aItemCode");
            $invdetail->ItemName = $this->GetPostValue("aItemName");
            $invdetail->Qty = $this->GetPostValue("aQty");
            $invdetail->Price = $this->GetPostValue("aPrice");
            $invdetail->ItemDescs = $this->GetPostValue("aItemDescs");
            $invdetail->ActivityId = $this->GetPostValue("aActivityId");
            $invdetail->DeptId = $this->GetPostValue("aDeptId");
            $invdetail->UnitId = $this->GetPostValue("aUnitId");
            $invdetail->UomCd = $this->GetPostValue("aUomCd");
            // update ke table
            $rs = $invdetail->Update($dId);
            if ($rs > 0) {
                $rst = 'OK|Proses update data berhasil!';
            } else {
                $rst = 'ER|Gagal update data!';
            }
        }else{
            $rst = "ER|No Data updated!";
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
            redirect_url("ap.invoice");
            return;
        }

        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang diminta tidak ditemukan / sudah dihapus.");
            redirect_url("ap.invoice");
            return;
        }
        
        if ($invoice->InvoiceStatus > 0) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
            redirect_url("ap.invoice/view/" . $invoice->Id);
            return;
        }

        // Everything is green
        $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
        if ($invoice->Delete($invoice->Id)) {
            $this->persistence->SaveState("info", sprintf("Dokumen Invoice: %s sudah berhasil dihapus.", $invoice->InvoiceNo));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Invoice: %s ! Harap hubungi system administrator.<br />Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage()));
        }

        redirect_url("ap.invoice");
    }

    public function void($id) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Maaf anda harus memilih dokumen Invoice terlebih dahulu.");
            redirect_url("ap.invoice");
            return;
        }

        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang diminta tidak ditemukan / sudah dihapus.");
            redirect_url("ap.invoice");
            return;
        }

        if ($invoice->InvoiceStatus > 0) {
            $this->persistence->SaveState("error", "Maaf Dokumen Invoice yang akan dihapus sedang dalam tahap process. Mohon batalkan terlebih dahulu sebelum hapus.");
            redirect_url("ap.invoice/view/" . $invoice->Id);
            return;
        }

        // Everything is green
        $invoice->UpdatebyId = \AclManager::GetInstance()->GetCurrentUser()->Id;
        if ($invoice->Void($invoice->Id)) {
            $this->persistence->SaveState("info", sprintf("Dokumen Invoice: %s sudah berhasil dihapus.", $invoice->InvoiceNo));
        } else {
            $this->persistence->SaveState("error", sprintf("Gagal hapus Dokumen Invoice: %s ! Harap hubungi system administrator.<br />Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage()));
        }

        redirect_url("ap.invoice");
    }

    public function view($invoiceId = 0) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/project.php");
        require_once(MODEL . "master/creditor.php");
        require_once(MODEL . "tax/taxtype.php");
        require_once(MODEL . "common/ap_invoice_type.php");
        $acl = \AclManager::GetInstance();
        $loader = null;
        $invoice = new Invoice();
        if ($invoiceId > 0 ) {
            $invoice = $invoice->LoadById($invoiceId);
            if ($invoice == null) {
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new \Project();
        $project = $loader->LoadByEntityId($this->userCompanyId);
        //load creditor
        $loader = new \Creditor();
        $creditors = $loader->LoadByEntity($this->userCompanyId);
        //load taxtype
        $loader = new \TaxType();
        $taxtype = $loader->LoadByMode($this->userCompanyId,1);
        $this->Set("taxtypes", $taxtype);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("projects", $project);
        $this->Set("creditors", $creditors);
        $this->Set("invoice", $invoice);
        $this->Set("acl", $acl);
        //load trxtype
        $loader = new \ApInvoiceType();
        $invtype = $loader->LoadByEntity($this->userCompanyId);
        $this->Set("invtypes", $invtype);
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
            if ($invoice->Approve($id,$this->userUid)) {
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
        redirect_url("ap.invoice");
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
            if ($invoice->Unapprove($id,$this->userUid)) {
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
        redirect_url("ap.invoice");
    }

    public function posting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice Suppier yang akan di-posting");
            redirect_url("ap.invoice");
        }
        require_once(MODEL . "common/creditor_type.php");
        require_once(MODEL . "accounting/voucher.php");
        require_once(MODEL . "common/trx_type.php");
        require_once(MODEL . "tax/taxtype.php");
        require_once(MODEL . "master/coa.php");

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
        redirect_url("ap.invoice");
    }

    private function doPosting($id, &$infos, &$errors) {

        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            // Invoice tidak ditemukan atau sudah dihapus. Skip dengan return false tanpa error message
            return false;
        }

        if ($invoice->InvoiceStatus != 1) {
            $errors[] = sprintf("Maaf AP Invoice: %s tidak diproses karena statusnya bukan Approved ! Status Dokumen: %s", $invoice->InvoiceNo, $invoice->GetStatus());
            return false;
        }

        // Cari creditor typenya untuk keperluan posting
        /** @var $creditorType \CreditorType */
        if (array_key_exists($invoice->CreditorId, $this->creditorTypes)) {
            $creditorType = $this->creditorTypes[$invoice->CreditorId];
        } else {
            $creditorType = new \CreditorType();
            $creditorType = $creditorType->LoadByCreditorId($invoice->CreditorId);

            // Jgn lupa cache data...
            $this->creditorTypes[$invoice->CreditorId] = $creditorType;
        }

        // Proses posting
        $userId = \AclManager::GetInstance()->GetCurrentUser()->Id;

        if ($invoice->InvoiceType == 7 || $invoice->InvoiceType == 8) {
            // #01: Load details invoice supplier
            $invoice->LoadDetails();

            // #02: Buat Master Voucher
            $voucher = new \Voucher();
            $voucher->DocumentTypeId = $invoice->InvoiceType;		// Disamakan dengan jenis yang dipilih pada saat pembuata invoice supplier
            $voucher->DocumentNo = $invoice->InvoiceNo;				// Disamakan dengan dokumen aslinya
            $voucher->Date = $invoice->InvoiceDate;							// Disamakan dengan dokumen aslinya
            $voucher->EntityId = $invoice->EntityId;
            $voucher->Note = 'Posting Otomatis Reff: '.$invoice->ReffNo.' ('.$invoice->InvoiceDescs.')';
            $voucher->VoucherSource = "AP";

            $voucher->CreatedById = $userId;
            $voucher->StatusCode = 4;						// Masuk dalam status POSTED

            // #03: Detail Voucher
            $sqn = 1;
            foreach ($invoice->Details as $detail) {
                // #03.1: Detail Utama
                $voucherDetail = new \VoucherDetail();

                $trxType = new \TrxType();
                $trxType = $trxType->LoadById($detail->ItemId);

                $voucherDetail->Sequence = $sqn;
                $voucherDetail->AccDebitId = $trxType->AccDebitId;
                // Selalu gunakan Akun Kredit yang ada pada CreditorType
                $voucherDetail->AccCreditId = $creditorType->AccControlId;
                $voucherDetail->Amount = $detail->Qty * $detail->Price;							// Agar nilainya konsisten dengan laporan lainnya maka gunakan yang ada pada Property Dpp
                $voucherDetail->CreditorId = $invoice->CreditorId;
                $voucherDetail->Note = $detail->ItemName;
                $voucherDetail->ProjectId = $invoice->ProjectId;
                $voucherDetail->ActivityId = $detail->ActivityId;
                $voucherDetail->DepartmentId = $detail->DeptId;
                $voucherDetail->UnitId = $detail->UnitId;
                $voucher->Details[] = $voucherDetail;
                $sqn++;
            }
            //tax1 process
            if ($invoice->TaxType1Id > 0){
                $taxtype = new \TaxType($invoice->TaxType1Id);
                if ($taxtype == null){
                    $errors[] = "Jenis Pajak #1 belum terdaftar!";
                    return false;
                }
                $coa = new \Coa();
                $voucherDetail = new \VoucherDetail();
                $voucherDetail->Sequence = $sqn;
                if ($taxtype->TaxCode == 'VAT-IN') {
                    if ($invoice->TaxInvoiceNo != '' && $invoice->TaxInvoiceNo != null) {
                        $voucherDetail->Note = $taxtype->TaxType;
                        $voucherDetail->AccDebitId = $taxtype->PostAccId;
                    } else {
                        $coa = $coa->LoadById($taxtype->TempAccId);
                        if ($coa == null) {
                            $voucherDetail->Note = $taxtype->TaxType;
                        }else{
                            $voucherDetail->Note = $coa->AccName;
                        }
                        $voucherDetail->AccDebitId = $taxtype->TempAccId;
                    }
                }else{
                    $voucherDetail->Note = $taxtype->TaxType;
                    $voucherDetail->AccDebitId = $taxtype->PostAccId;
                }
                if ($taxtype->IsDeductable == 0) {
                    // Selalu gunakan Akun Kredit yang ada pada CreditorType
                    $voucherDetail->AccCreditId = $creditorType->AccControlId;
                }else{
                    $voucherDetail->AccDebitId = $creditorType->AccControlId;
                    $voucherDetail->AccCreditId = $taxtype->PostAccId;
                }
                if ($invoice->Tax1Amount < 0) {
                    $voucherDetail->Amount = $invoice->Tax1Amount * -1;
                }else{
                    $voucherDetail->Amount = $invoice->Tax1Amount;
                }
                $voucherDetail->CreditorId = $invoice->CreditorId;
                $voucherDetail->ProjectId = $invoice->ProjectId;
                $voucherDetail->ActivityId = $detail->ActivityId;
                $voucherDetail->DepartmentId = $detail->DeptId;
                $voucherDetail->UnitId = $detail->UnitId;
                $voucher->Details[] = $voucherDetail;
            }

            //tax2 process
            if ($invoice->TaxType2Id > 0){
                $sqn++;
                $taxtype = new \TaxType($invoice->TaxType2Id);
                if ($taxtype == null){
                    $errors[] = "Jenis Pajak #2 belum terdaftar!";
                    return false;
                }
                $coa = new \Coa();
                $voucherDetail = new \VoucherDetail();
                $voucherDetail->Sequence = $sqn;
                if ($taxtype->TaxCode == 'VAT-IN') {
                    if ($invoice->TaxInvoiceNo != '' && $invoice->TaxInvoiceNo != null) {
                        $voucherDetail->Note = $taxtype->TaxType;
                        $voucherDetail->AccDebitId = $taxtype->PostAccId;
                    } else {
                        $coa = $coa->LoadById($taxtype->TempAccId);
                        if ($coa == null) {
                            $voucherDetail->Note = $taxtype->TaxType;
                        }else{
                            $voucherDetail->Note = $coa->AccName;
                        }
                        $voucherDetail->AccDebitId = $taxtype->TempAccId;
                    }
                }else{
                    $voucherDetail->Note = $taxtype->TaxType;
                    $voucherDetail->AccDebitId = $taxtype->PostAccId;
                }
                if ($taxtype->IsDeductable == 0) {
                    // Selalu gunakan Akun Kredit yang ada pada CreditorType
                    $voucherDetail->AccCreditId = $creditorType->AccControlId;
                }else{
                    $voucherDetail->AccDebitId = $creditorType->AccControlId;
                    $voucherDetail->AccCreditId = $taxtype->PostAccId;
                }
                if ($invoice->Tax2Amount < 0) {
                    $voucherDetail->Amount = $invoice->Tax2Amount * -1;
                }else{
                    $voucherDetail->Amount = $invoice->Tax2Amount;
                }
                $voucherDetail->CreditorId = $invoice->CreditorId;
                $voucherDetail->ProjectId = $invoice->ProjectId;
                $voucherDetail->ActivityId = $detail->ActivityId;
                $voucherDetail->DepartmentId = $detail->DeptId;
                $voucherDetail->UnitId = $detail->UnitId;
                $voucher->Details[] = $voucherDetail;
            }

            // #04: Simpan data
            $rs = $voucher->Insert();
            if ($rs != 1) {
                $errors[] = sprintf("Gagal proses data master Voucher untuk Invoice Supplier: %s. Message: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                return false;
            }

            foreach ($voucher->Details as $voucherDetail) {
                $voucherDetail->VoucherId = $voucher->Id;

                $rs = $voucherDetail->Insert();
                if ($rs != 1) {
                    $errors[] = sprintf("Gagal proses data detail Voucher untuk Invoice Supplier: %s. Message: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                    return false;
                }
            }
        }

        // #05: Flag Invoice Supplier sebagai posted
        $invoice->UpdatebyId = $userId;
        $rs = $invoice->Post($invoice->Id);
        if ($rs != 1) {
            $errors[] = sprintf("Gagal posting Invoice Supplier: %s (gagal ganti flag). Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
            return false;
        }

        $infos[] = sprintf("Jurnal Invoice Supplier: %s sudah berhasil dibuat.", $invoice->InvoiceNo);
        return true;
    }

    public function unposting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda tidak memilih Invoice Supplier yang akan di-unposting");
            redirect_url("ap.invoice");
        }

        require_once(MODEL . "accounting/voucher.php");

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
        if (count($ids) > 1) {
            \Dispatcher::RedirectUrl("ap.invoice");
        } else {
            \Dispatcher::RedirectUrl("ap.invoice/view/" . $ids[0]);
        }
    }

    private function doUnPosting($id, &$infos, &$errors) {
        $invoice = new Invoice();
        $invoice = $invoice->LoadById($id);
        if ($invoice == null || $invoice->IsDeleted) {
            // Biarkan saja ini data tidak ketemu tidak perlu set info atau error
            return false;
        }
        if ($this->userCompanyId != 7 && $this->userCompanyId != null) {
            if ($invoice->EntityId != $this->userCompanyId) {
                // Coba akses data lintas Company
                return false;
            }
        }
        if ($invoice->InvoiceStatus != 2) {
            $errors[] = sprintf("Maaf Invoice Supplier: %s tidak diproses karena tidak berstatus POSTED ! Status Dokumen: %s", $invoice->InvoiceNo, $invoice->GetStatus());
            return false;
        }

        // Jika sudah ada pembayaran tidak boleh un-posting
        if ($invoice->IsPaymentInProgress()) {
            $invoice->LoadPayments();
            $links = "";
            $helper = new \AppHelper();
            foreach ($invoice->Payments as $payment) {
                $links[] = sprintf('<a href="%s">%s</a>', $helper->site_url("ap.payment/view/" . $payment->Id), $payment->DocumentNo);
            }

            $errors[] = sprintf("Maaf Invoice Supplier: %s tidak diproses karena sudah ada proses pembayaran.<br />Links: %s", $invoice->InvoiceNo, implode(", ", $links));
            return false;
        }

        // OK mari kita mulai prosedur unposting
        // #01: Cari vouchernya dan hapus... (No Voucher akan sama dengan no Invoice Supplier)
        $voucher = new \Voucher();
        $rs = $voucher->DeleteByDocNo($invoice->InvoiceNo);
        if ($rs == -1) {
            $errors[] = sprintf("Gagal unposting Invoice Supplier (gagal hapus voucher): %s. Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
            return false;
        } else if ($rs == 0 && $invoice->InvoiceType != 21) {
            // Ini aneh... status posted tapi ga ketemu vouchernya pas saat hapus...
            $infos[] = sprintf("NOTICE: Dokumen Invoice Supplier: %s tidak memiliki Voucher tetapi status POSTED.", $invoice->InvoiceNo);
        }

        // #02: Unposting status Invoice Supplier
        $invoice->UpdatebyId = \AclManager::GetInstance()->GetCurrentUser()->Id;
        $rs = $invoice->UnPost($invoice->Id);
        if ($rs != 1) {
            $errors[] = sprintf("Gagal unposting Invoice Supplier (gagal set flag): %s. Error: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
            return false;
        }

        $infos[] = sprintf("Dokumen Invoice Supplier: %s sudah berhasil di unposting.", $invoice->InvoiceNo);
        return true;
    }

    public function posting_by_period() {
        if (count($this->getData) > 0) {
            $start = strtotime($this->GetGetValue("start"));
            $end = strtotime($this->GetGetValue("end"));

            if (!is_int($start) || !is_int($end)) {
                $this->Set("error", "Maaf data yang dikirim tidak lengkap. Mohon memilih tanggal kembali.");
            } else {
                if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
                    $query =
                        "SELECT a.id
FROM ap_invoice_master AS a
WHERE a.is_deleted = 0 AND a.doc_status = 0 AND a.doc_date BETWEEN ?start AND ?end";
                } else {
                    $query =
                        "SELECT a.id
FROM ap_invoice_master AS a
WHERE a.is_deleted = 0 AND a.doc_status = 0 AND a.entity_id = ?sbu AND a.doc_date BETWEEN ?start AND ?end";
                    $this->connector->AddParameter("?sbu", $this->userCompanyId);
                }
                $this->connector->CommandText = $query;
                $this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
                $this->connector->AddParameter("?end", date(SQL_DATETIME, $end));

                // Proses data...
                $rs = $this->connector->ExecuteQuery();
                if ($rs == null) {
                    // Error ?
                    $this->Set("error", "Gagal mengambil data Invoice Supplier. Error: " . $this->connector->GetErrorMessage());
                } else {
                    if ($rs->GetNumRows() == 0) {
                        $this->Set("info", "Tidak ada data Invoice Supplier yang dapat diposting pada periode yang diminta.");
                    } else {
                        require_once(MODEL . "common/creditor_type.php");
                        require_once(MODEL . "accounting/voucher.php");
                        require_once(MODEL . "common/trx_type.php");
                        require_once(MODEL . "common/tax_rate.php");

                        $infos = array();
                        $errors = array();
                        while ($row = $rs->FetchAssoc()) {
                            $id = $row["id"];

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
                        redirect_url("ap.invoice");
                    }
                }
            }
        } else {
            $start = mktime(0, 0, 0, date("n"), 1);
            $end = time();
        }

        $this->Set("start", $start);
        $this->Set("end", $end);
    }

    public function unposting_by_period() {
        if (count($this->getData) > 0) {
            $start = strtotime($this->GetGetValue("start"));
            $end = strtotime($this->GetGetValue("end"));

            if (!is_int($start) || !is_int($end)) {
                $this->Set("error", "Maaf data yang dikirim tidak lengkap. Mohon memilih tanggal kembali.");
            } else {
                if ($this->userCompanyId == 7 || $this->userCompanyId == null) {
                    $query =
                        "SELECT a.id
FROM ap_invoice_master AS a
WHERE a.is_deleted = 0 AND a.doc_status = 1 AND a.doc_date BETWEEN ?start AND ?end";
                } else {
                    $query =
                        "SELECT a.id
FROM ap_invoice_master AS a
WHERE a.is_deleted = 0 AND a.doc_status = 1 AND a.entity_id = ?sbu AND a.doc_date BETWEEN ?start AND ?end";
                    $this->connector->AddParameter("?sbu", $this->userCompanyId);
                }
                $this->connector->CommandText = $query;
                $this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
                $this->connector->AddParameter("?end", date(SQL_DATETIME, $end));

                // Proses data...
                $rs = $this->connector->ExecuteQuery();
                if ($rs == null) {
                    // Error ?
                    $this->Set("error", "Gagal mengambil data Invoice Supplier. Error: " . $this->connector->GetErrorMessage());
                } else {
                    if ($rs->GetNumRows() == 0) {
                        $this->Set("info", "Tidak ada data Invoice Supplier yang dapat dibatalkan pada periode yang diminta.<br />Apakah anda sudah pernah melakukan posting data.");
                    } else {
                        require_once(MODEL . "accounting/voucher.php");

                        $infos = array();
                        $errors = array();
                        while ($row = $rs->FetchAssoc()) {
                            $id = $row["id"];

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
                        redirect_url("ap.invoice");
                    }
                }
            }
        } else {
            $start = mktime(0, 0, 0, date("n"), 1);
            $end = time();
        }

        $this->Set("start", $start);
        $this->Set("end", $end);
    }

    public function overview() {
        require_once(MODEL . 'master/creditor.php');
        require_once(MODEL . 'common/doc_type.php');

        if (count($this->getData) > 0) {
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $creditorId = $this->GetGetValue("creditorId");
            $status = $this->GetGetValue("status");
            $docTypeId = $this->GetGetValue("docTypeId");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText =
                "SELECT a.*, b.entity_cd, c.creditor_name, d.project_name
	FROM t_ap_invoice_master AS a
	JOIN cm_company AS b ON a.entity_id = b.entity_id
	JOIN ap_creditor_master AS c ON a.creditor_id = c.id
	LEFT JOIN cm_project AS d ON a.project_id = d.id
WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND a.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }

            if ($creditorId != -1) {
                $this->connector->CommandText .= " AND a.creditor_id = ?creditor";
                $this->connector->AddParameter("?creditor", $creditorId);
            }

            if ($docTypeId != -1) {
                $this->connector->CommandText .= " AND a.invoice_type = ?doc";
                $this->connector->AddParameter("?doc", $docTypeId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.invoice_status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.invoice_date BETWEEN ?start AND ?end ORDER BY a.invoice_no ASC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $startDate = time();
            $endDate = time();
            $creditorId = -1;
            $status = -1;
            $docTypeId = -1;
            $output = "web";
            $report = null;
        }

        $creditor = new \Creditor();
        $this->Set("creditors", $creditor->LoadAll());
        $this->Set("credt", $creditor->FindById($creditorId));
        $this->Set("creditorId", $creditorId);

        $docType = new \DocType();
        $this->Set("docTypes", $docType->LoadAll());
        $this->Set("doc", $docType->FindById($docTypeId));

        $this->Set("status", $status);
        $this->Set("docTypeId", $docTypeId);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
    }

    public function getTaxtypeData($id){
        require_once (MODEL . "tax/taxtype.php");
        $taxtype = new \TaxType();
        $taxtype = $taxtype->LoadById($id);
        $out = "ER|0";
        if ($taxtype != null){
            $out = "OK|1|".$taxtype->TaxCode.'|'.$taxtype->TaxRate.'|'.$taxtype->IsDeductable;
        }
        print ($out);
    }
}


// End of File: invoice_controller.php
