<?php
class ReceiptController extends AppController {
    private $userCompanyId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "ar/receipt.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("entity_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->trxMonth = date('n');
        $this->trxYear = date ('Y');
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "a.receipt_date", "display" => "Rec Date", "width" => 60);
        $settings["columns"][] = array("name" => "a.receipt_no", "display" => "No. Receipt", "width" => 100);
        $settings["columns"][] = array("name" => "a.debtor_name", "display" => "Debtor Name", "width" => 200);
        $settings["columns"][] = array("name" => "a.receipt_descs", "display" => "Description", "width" => 300);
        $settings["columns"][] = array("name" => "format(a.receipt_amount,0)", "display" => "Receipt Amount", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.allocate_amount,0)", "display" => "Alocate Amount", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.receipt_amount - a.allocate_amount,0)", "display" => "Balance Amount", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.receipt_status = 0,'Draft',if(a.receipt_status = 1,'Approved',if(a.receipt_status = 2,'Posted','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.receipt_no", "display" => "No. Receipt");
        $settings["filters"][] = array("name" => "a.receipt_date", "display" => "Receipt Date");
        $settings["filters"][] = array("name" => "a.debtor_name", "display" => "Debtor Name");
        $settings["filters"][] = array("name" => "a.receipt_descs", "display" => "Description");
        $settings["filters"][] = array("name" => "if(a.receipt_status = 0,'Draft',if(a.receipt_status = 1,'Approved',if(a.receipt_status = 2,'Posted','Void')))", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 2;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "O/R List";

            if ($acl->CheckUserAccess("ar.receipt", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ar.receipt/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.receipt", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ar.receipt/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Receipt terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.receipt", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ar.receipt/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ar.receipt", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ar.receipt/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Receipt terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data receipt","Confirm" => "");
            }

            if ($acl->CheckUserAccess("ar.invoice", "print")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Print Receipt", "Url" => "ar.receipt/print","Target"=>"_blank","Class" => "bt_print", "ReqId" => 2, "Confirm" => "Cetak Invoice yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.receipt", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.receipt/overview", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.receipt", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve", "Url" => "ar.receipt/approve", "Class" => "bt_process", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Receipt terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Approve", "Url" => "ar.receipt/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Receipt terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            if ($acl->CheckUserAccess("ar.receipt", "posting")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "ar.receipt/posting", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Receipt terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "ar.receipt/unposting", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Receipt terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ar_receipt_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And year(a.receipt_date) = ".$this->trxYear;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add($recId = 0) {
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "common/warkattype.php");
        require_once(MODEL . "master/debtor.php");
        $loader = null;
        $log = new UserAdmin();
		$receipt = new Receipt();
        if ($recId > 0 ) {
            $receipt = $receipt->LoadById($recId);
            if ($receipt == null) {
                $this->persistence->SaveState("error", "Maaf Data Receipt dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.receipt");
            }
            if ($receipt->ReceiptStatus == 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Receipt No. %s sudah di-Approve-. Tidak boleh diubah lagi..", $receipt->ReceiptNo));
                redirect_url("ar.receipt");
            }
            if ($receipt->ReceiptStatus == 2) {
                $this->persistence->SaveState("error", sprintf("Maaf Receipt No. %s sudah di-Posting- Tidak boleh diubah lagi..", $receipt->ReceiptNo));
                redirect_url("ar.receipt");
            }
            if ($receipt->ReceiptStatus == 3) {
                $this->persistence->SaveState("error", sprintf("Maaf Receipt No. %s sudah di-Void- Tidak boleh diubah lagi..", $receipt->ReceiptNo));
                redirect_url("ar.receipt");
            }
        }
        // load details
        $receipt->LoadDetails();
        //load data cabang
        $loader = new Bank();
        $banks = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        //load debtor
        $loader = new Debtor();
        $debtors = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("debtors", $debtors);
        $this->Set("receipt", $receipt);
        $this->Set("banks", $banks);
        $this->Set("warkattypes", $warkattypes);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
	}

    public function proses_master($invId = 0) {
        $receipt = new Receipt();
        if (count($this->postData) > 0) {
            $receipt->EntityId = $this->userCompanyId;
            $receipt->ReceiptDate = $this->GetPostValue("ReceiptDate");
            $receipt->ReceiptNo = $this->GetPostValue("ReceiptNo");
            $receipt->ReceiptDescs = $this->GetPostValue("ReceiptDescs");
            $receipt->DebtorId = $this->GetPostValue("DebtorId");
            $receipt->WarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $receipt->WarkatBankId = $this->GetPostValue("WarkatBankId");
            $receipt->WarkatNo = $this->GetPostValue("WarkatNo");
            $receipt->WarkatDate = $this->GetPostValue("WarkatDate");
            $receipt->ReceiptAmount = 0;
            $receipt->AllocateAmount = 0;
            $receipt->ReceiptStatus = 0;
            $receipt->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($receipt->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $receipt->ReceiptNo = $docCounter->AutoDocNoOr($receipt->EntityId, $receipt->ReceiptDate, 1);
                $rs = $receipt->Insert();
                if ($rs == 1) {
                    printf("OK|A|%d|%s",$receipt->Id,$receipt->ReceiptNo);
                }else{
                    printf("ER|A|%d",$receipt->Id);
                }
            }else{
                $receipt->UpdatebyId = $this->userUid;
                $rs = $receipt->Update($receipt->Id);
                if ($rs == 1) {
                    printf("OK|U|%d|%s",$receipt->Id,$receipt->ReceiptNo);
                }else{
                    printf("ER|U|%d",$receipt->Id);
                }
            }
        }else{
            printf("ER|X|%d",$invId);
        }
    }

	public function view($recId = null) {
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "common/warkattype.php");
        require_once(MODEL . "master/debtor.php");
        $loader = null;
        $log = new UserAdmin();
        $receipt = new Receipt();
        if ($recId > 0 ) {
            $receipt = $receipt->LoadById($recId);
            if ($receipt == null || $receipt->ReceiptStatus == 3) {
                $this->persistence->SaveState("error", "Maaf Data Receipt dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.receipt");
            }
        }
        // load details
        $receipt->LoadDetails();
        //load data cabang
        $loader = new Bank();
        $banks = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        //load debtor
        $loader = new Debtor();
        $debtors = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("debtors", $debtors);
        $this->Set("receipt", $receipt);
        $this->Set("banks", $banks);
        $this->Set("warkattypes", $warkattypes);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
	}

    public function delete($receiptId) {
        // Cek datanya
        $receipt = new Receipt();
        $log = new UserAdmin();
        $receipt = $receipt->FindById($receiptId);
        if($receipt == null){
            $this->Set("error", "Maaf Data Receipt dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.receipt");
        }
        if($receipt->ReceiptStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Receipt No. %s sudah berstatus -APPROVED-",$receipt->ReceiptNo));
            redirect_url("ar.receipt");
        }
        /** @var $receipt Receipt */
        if ($receipt->Delete($receiptId) > 0) {
            $this->persistence->SaveState("info", sprintf("Data Receipt No: %s sudah berhasil dihapus", $receipt->ReceiptNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt',$receipt->ReceiptNo,'Success');
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Receipt No: %s gagal dihapus", $receipt->ReceiptNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt',$receipt->ReceiptNo,'Failed');
        }
        redirect_url("ar.receipt");
    }

    public function void($receiptId) {
        // Cek datanya
        $receipt = new Receipt();
        $log = new UserAdmin();
        $receipt = $receipt->FindById($receiptId);
        if($receipt == null){
            $this->Set("error", "Maaf Data Receipt dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.receipt");
        }
        if($receipt->ReceiptStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Receipt No. %s sudah berstatus -APPROVED-",$receipt->ReceiptNo));
            redirect_url("ar.receipt");
        }
        if($receipt->ReceiptStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Receipt No. %s sudah berstatus -VOID-",$receipt->ReceiptNo));
            redirect_url("ar.receipt");
        }
        //cek alokasi pembayaran
        if ($receipt->AllocateAmount > 0){
            $this->persistence->SaveState("error", sprintf("Data Receipt No. %s sudah ada alokasi penerimaan!",$receipt->ReceiptNo));
            redirect_url("ar.receipt");
        }
        /** @var $receipt Receipt */
        if ($receipt->Delete($receiptId) > 0) {
            $this->persistence->SaveState("info", sprintf("Data Receipt No: %s sudah berhasil dibatalkan", $receipt->ReceiptNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt',$receipt->ReceiptNo,'Success');
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Receipt No: %s gagal dibatalkan", $receipt->ReceiptNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt',$receipt->ReceiptNo,'Failed');
        }
        redirect_url("ar.receipt");
    }

	public function add_detail($receiptId = null) {
        $receipt = new Receipt($receiptId);
        $log = new UserAdmin();
        $recdetail = new ReceiptDetail();
        $recdetail->ReceiptId = $receiptId;
        if (count($this->postData) > 0) {
            $recdetail->InvoiceId = $this->GetPostValue("aInvoiceId");
            $recdetail->InvoiceOutstanding = $this->GetPostValue("aInvoiceOutStanding");
            $recdetail->AllocateAmount = $this->GetPostValue("aAllocateAmount");
            $recdetail->PotPph = 0;
            $recdetail->PotLain = 0;
            $rs = $recdetail->Insert()== 1;
            if ($rs > 0) {
                $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Add Receipt detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$receipt->ReceiptNo,'Success');
                echo json_encode(array());
            } else {
                $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Add Receipt detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$receipt->ReceiptNo,'Failed');
                echo json_encode(array('errorMsg'=>'Some database errors occured.'));
            }
        }
	}    

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $recdetail = new ReceiptDetail();
        $recdetail = $recdetail->FindById($id);
        if ($recdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($recdetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$recdetail->ReceiptId,'Success');
            printf("Data Detail Receipt ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Delete Receipt detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$recdetail->ReceiptId,'Failed');
            printf("Maaf, Data Detail Receipt ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($receiptId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $loader = null;
        $receipt = new Receipt();
        $receipt = $receipt->LoadById($receiptId);
        if($receipt == null){
            $this->persistence->SaveState("error", "Maaf Data Receipt dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.receipt");
        }
        // load details
        $receipt->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new Karyawan();
        $banks = $loader->LoadAll();
        $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
        //kirim ke view
        $this->Set("sales", $banks);
        $this->Set("cabangs", $cabang);
        $this->Set("receipt", $receipt);
        $this->Set("userName", $userName);
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/warkattype.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sWarkatBankId = $this->GetPostValue("WarkatBankId");
            $sReceiptStatus = $this->GetPostValue("ReceiptStatus");
            $sWarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $receipt = new Receipt();
            $reports = $receipt->Load4Reports($this->userCompanyId,$sCabangId,$sWarkatBankId,$sContactsId,$sWarkatTypeId,$sReceiptStatus,$sStartDate,$sEndDate);
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sWarkatBankId = 0;
            $sReceiptStatus = -1;
            $sWarkatTypeId = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $reports = null;
        }
        $customer = new Contacts();
        $customer = $customer->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        $loader = new Bank();
        $banks = $loader->LoadAll();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3){
            $cabang = $loader->LoadByEntityId($this->userCompanyId);
        }else{
            $cabang = $loader->LoadById($this->userCompanyId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("customers",$customer);
        $this->Set("banks",$banks);
        $this->Set("CabangId",$sCabangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("WarkatBankId",$sWarkatBankId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("ReceiptStatus",$sReceiptStatus);
        $this->Set("WarkatTypeId",$sWarkatTypeId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCompanyId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("warkattypes",$warkattypes);
    }

    public function getReceiptItemRows($id){
        $receipt = new Receipt();
        $rows = $receipt->GetReceiptItemRow($id);
        print($rows);
    }

    public function createTextReceipt($id){
        $receipt = new Receipt($id);
        if ($receipt <> null){
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $receipt->CompanyName);
            fwrite($myfile, "\n".'FAKTUR PENJUALAN');

            fclose($myfile);
        }
    }

    public function getoutstandinginvoices_plain($debtorId = 0 ,$invoiceNo = null){
        require_once(MODEL . "ar/invoice.php");
        $ret = 'ER|0';
        if($invoiceNo != null || $invoiceNo != ''){
            /** @var $invoice Invoice[] */
            $invoice = new Invoice();
            $invoice = $invoice->GetUnpaidInvoices($debtorId,$invoiceNo);
            if ($invoice != null){
                $ret = 'OK|'.$invoice->Id.'|'.date(JS_DATE,$invoice->InvoiceDate).'|'.date(JS_DATE,$invoice->DueDate).'|'.$invoice->BalanceAmount;
            }
        }
        print $ret;
    }

    public function getoutstandinginvoices_json($debtorId){
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $receipt = new Receipt();
        $itemlists = $receipt->GetJSonUnpaidInvoices($debtorId);
        echo json_encode($itemlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.receipt");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $receipt = new Receipt();
            $receipt = $receipt->FindById($id);
            /** @var $receipt Receipt */
            // process receipt
            if($receipt->ReceiptStatus == 0 && $receipt->BalanceAmount == 0 && $receipt->AllocateAmount > 0){
                $rs = $receipt->Approve($receipt->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Approve Receipt',$receipt->ReceiptNo,'Success');
                    $infos[] = sprintf("Data Receipt No.: '%s' (%s) telah berhasil di-approve.", $receipt->ReceiptNo, $receipt->ReceiptDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Approve Receipt',$receipt->ReceiptNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Receipt: '%s'. Message: %s", $receipt->ReceiptNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($receipt->BalanceAmount > 0){
                    $errors[] = sprintf("Receipt No.%s penerimaan belum dialokasi semua !",$receipt->ReceiptNo);
                }else {
                    $errors[] = sprintf("Data Receipt No.%s tidak berstatus -Draft- !", $receipt->ReceiptNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.receipt");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.receipt");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $receipt = new Receipt();
            $receipt = $receipt->FindById($id);
            /** @var $receipt Receipt */
            // process invoice
            if($receipt->ReceiptStatus == 1){
                $rs = $receipt->Unapprove($receipt->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Un-Approve Receipt',$receipt->ReceiptNo,'Success');
                    $infos[] = sprintf("Data Receipt No.: '%s' (%s) telah berhasil di-batalkan.", $receipt->ReceiptNo, $receipt->ReceiptDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Un-Approve Receipt',$receipt->ReceiptNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Receipt: '%s'. Message: %s", $receipt->ReceiptNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($receipt->ReceiptStatus == 0){
                    $errors[] = sprintf("Data Receipt No.%s masih berstatus -Draft- !",$receipt->ReceiptNo);
                }else{
                    $errors[] = sprintf("Data Receipt No.%s masih berstatus -Posted- !",$receipt->ReceiptNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.receipt");
    }

    public function posting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.receipt");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $receipt = new Receipt();
            $receipt = $receipt->FindById($id);
            /** @var $receipt Receipt */
            // process receipt
            if($receipt->ReceiptStatus == 1){
                $rs = $receipt->Posting($receipt->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Posting Receipt',$receipt->ReceiptNo,'Success');
                    $infos[] = sprintf("Data Receipt No.: '%s' (%s) telah berhasil di-posting.", $receipt->ReceiptNo, $receipt->ReceiptDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Posting Receipt',$receipt->ReceiptNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses posting Data Receipt: '%s'. Message: %s", $receipt->ReceiptNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Receipt No.%s tidak berstatus -Approved- !",$receipt->ReceiptNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.receipt");
    }

    public function unposting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.receipt");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $receipt = new Receipt();
            $receipt = $receipt->FindById($id);
            /** @var $receipt Receipt */
            // process invoice
            if($receipt->ReceiptStatus == 2){
                $rs = $receipt->Unposting($receipt->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Un-Approve Receipt',$receipt->ReceiptNo,'Success');
                    $infos[] = sprintf("Data Receipt No.: '%s' (%s) telah berhasil di-batalkan.", $receipt->ReceiptNo, $receipt->ReceiptDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ar.receipt','Un-Approve Receipt',$receipt->ReceiptNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Receipt: '%s'. Message: %s", $receipt->ReceiptNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($receipt->ReceiptStatus == 1){
                    $errors[] = sprintf("Data Receipt No.%s masih berstatus -POSTED- !",$receipt->ReceiptNo);
                }else{
                    $errors[] = sprintf("Data Receipt No.%s masih berstatus -DRAFT- !",$receipt->ReceiptNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.receipt");
    }

    public function overview() {
        require_once(MODEL . 'master/debtor.php');
        require_once(MODEL . 'status_code.php');

        $receipt = new Receipt();

        if (count($this->getData) > 0) {
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $debtorId = $this->GetGetValue("debtorId");
            $status = $this->GetGetValue("status");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText =
                "SELECT a.*, b.alloc_amt, b.deduction_amt, c.entity_cd, d.debtor_cd, d.debtor_name, e.short_desc, f.bank_name, g.acc_no
FROM t_ar_receipt_master AS a
	JOIN (
		SELECT aa.receipt_id, SUM(aa.allocate_amount) AS alloc_amt, SUM(aa.pot_pph) AS deduction_amt
		FROM t_ar_receipt_detail AS aa
		GROUP BY aa.receipt_id
	) AS b ON a.id = b.receipt_id
	JOIN cm_company AS c ON a.entity_id = c.entity_id
	JOIN ar_debtor_master AS d ON a.debtor_id = d.id
	JOIN sys_status_code AS e ON a.receipt_status = e.code AND e.key = 'receipt_status'
	JOIN cm_bank_account AS f ON a.warkat_bank_id = f.id
	JOIN cm_acc_detail AS g ON f.acc_id = g.id
WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND c.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }

            if ($debtorId != -1) {
                $this->connector->CommandText .= " AND d.id = ?debtor";
                $this->connector->AddParameter("?debtor", $debtorId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.receipt_status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.receipt_date BETWEEN ?start AND ?end ORDER BY a.receipt_date DESC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $startDate = time();
            $endDate = time();
            $debtorId = -1;
            $status = -1;
            $output = "web";
            $report = null;
        }

        $debtor = new Debtor();
        $debtorAll = $this->userCompanyId != 7 ? $debtor->LoadByEntity($this->userCompanyId) : $debtor->LoadAll();
        $this->Set("debtors", $debtorAll);
        $this->Set("debt", $debtor->FindById($debtorId));
        $this->Set("debtorId", $debtorId);

        $syscode = new StatusCode();
        $this->Set("codes", $syscode->LoadReceiptStatus());
        $this->Set("codeName", $syscode->FindBy('receipt_status', $status));
        $this->Set("status", $status);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
    }
}


// End of File: estimasi_controller.php
