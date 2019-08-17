<?php
class PaymentController extends AppController {
    private $userCompanyId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "ap/payment.php");
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
        $settings["columns"][] = array("name" => "a.payment_date", "display" => "Rec Date", "width" => 60);
        $settings["columns"][] = array("name" => "a.payment_no", "display" => "No. Payment", "width" => 100);
        $settings["columns"][] = array("name" => "a.creditor_name", "display" => "Creditor Name", "width" => 200);
        $settings["columns"][] = array("name" => "a.payment_descs", "display" => "Description", "width" => 300);
        $settings["columns"][] = array("name" => "format(a.payment_amount,0)", "display" => "Payment Amount", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.allocate_amount,0)", "display" => "Alocate Amount", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.payment_amount - a.allocate_amount,0)", "display" => "Balance Amount", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.payment_status = 0,'Draft',if(a.payment_status = 1,'Approved',if(a.payment_status = 2,'Posted','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.payment_no", "display" => "No. Payment");
        $settings["filters"][] = array("name" => "a.payment_date", "display" => "Payment Date");
        $settings["filters"][] = array("name" => "a.creditor_name", "display" => "Creditor Name");
        $settings["filters"][] = array("name" => "a.payment_descs", "display" => "Description");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 2;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Payment List";

            if ($acl->CheckUserAccess("ap.payment", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.payment/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.payment", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.payment/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Payment terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.payment", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ap.payment/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.payment", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.payment/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Payment terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data payment","Confirm" => "");
            }

            if ($acl->CheckUserAccess("ar.invoice", "print")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Print Payment", "Url" => "ap.payment/print","Target"=>"_blank","Class" => "bt_print", "ReqId" => 2, "Confirm" => "Cetak Invoice yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.payment", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.payment/report", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.payment", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve", "Url" => "ap.payment/approve", "Class" => "bt_process", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Payment terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Approve", "Url" => "ap.payment/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Payment terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
            if ($acl->CheckUserAccess("ap.payment", "posting")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Posting", "Url" => "ap.payment/posting", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Payment terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Un-Posting", "Url" => "ap.payment/unposting", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Payment terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data invoice yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ap_payment_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And year(a.payment_date) = ".$this->trxYear;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add($recId = 0) {
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "common/warkattype.php");
        require_once(MODEL . "master/creditor.php");
        $loader = null;
        $log = new UserAdmin();
		$payment = new Payment();
        if ($recId > 0 ) {
            $payment = $payment->LoadById($recId);
            if ($payment == null) {
                $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.payment");
            }
            if ($payment->PaymentStatus == 1) {
                $this->persistence->SaveState("error", sprintf("Maaf Payment No. %s sudah di-Approve-. Tidak boleh diubah lagi..", $payment->PaymentNo));
                redirect_url("ap.payment");
            }
            if ($payment->PaymentStatus == 2) {
                $this->persistence->SaveState("error", sprintf("Maaf Payment No. %s sudah di-Posting- Tidak boleh diubah lagi..", $payment->PaymentNo));
                redirect_url("ap.payment");
            }
            if ($payment->PaymentStatus == 3) {
                $this->persistence->SaveState("error", sprintf("Maaf Payment No. %s sudah di-Void- Tidak boleh diubah lagi..", $payment->PaymentNo));
                redirect_url("ap.payment");
            }
        }
        // load details
        $payment->LoadDetails();
        //load data cabang
        $loader = new Bank();
        $banks = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        //load creditor
        $loader = new Creditor();
        $creditors = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("creditors", $creditors);
        $this->Set("payment", $payment);
        $this->Set("banks", $banks);
        $this->Set("warkattypes", $warkattypes);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
	}

    public function proses_master($invId = 0) {
	    $log = new UserAdmin();
        $payment = new Payment();
        if (count($this->postData) > 0) {
            $payment->EntityId = $this->userCompanyId;
            $payment->PaymentDate = $this->GetPostValue("PaymentDate");
            $payment->PaymentNo = $this->GetPostValue("PaymentNo");
            $payment->PaymentDescs = $this->GetPostValue("PaymentDescs");
            $payment->CreditorId = $this->GetPostValue("CreditorId");
            $payment->WarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $payment->WarkatBankId = $this->GetPostValue("WarkatBankId");
            $payment->WarkatNo = $this->GetPostValue("WarkatNo");
            $payment->WarkatDate = $this->GetPostValue("WarkatDate");
            $payment->PaymentAmount = 0;
            $payment->AllocateAmount = 0;
            $payment->PaymentStatus = 0;
            $payment->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($payment->Id == 0) {
                require_once(MODEL . "common/doc_counter.php");
                $docCounter = new DocCounter();
                $payment->PaymentNo = $docCounter->AutoDocNoPv($payment->EntityId, $payment->PaymentDate, 1);
                $rs = $payment->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Add New Payment',$payment->PaymentNo,'Success');
                    printf("OK|A|%d|%s",$payment->Id,$payment->PaymentNo);
                }else{
                    printf("ER|A|%d",$payment->Id);
                }
            }else{
                $payment->UpdatebyId = $this->userUid;
                $rs = $payment->Update($payment->Id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Update Payment',$payment->PaymentNo,'Success');
                    printf("OK|U|%d|%s",$payment->Id,$payment->PaymentNo);
                }else{
                    printf("ER|U|%d",$payment->Id);
                }
            }
        }else{
            printf("ER|X|%d",$invId);
        }
    }

	public function view($recId = null) {
        require_once(MODEL . "master/bank.php");
        require_once(MODEL . "common/warkattype.php");
        require_once(MODEL . "master/creditor.php");
        $loader = null;
        $log = new UserAdmin();
        $payment = new Payment();
        if ($recId > 0 ) {
            $payment = $payment->LoadById($recId);
            if ($payment == null || $payment->PaymentStatus == 3) {
                $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.payment");
            }
        }
        // load details
        $payment->LoadDetails();
        //load data cabang
        $loader = new Bank();
        $banks = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new WarkatType();
        $warkattypes = $loader->LoadAll();
        //load creditor
        $loader = new Creditor();
        $creditors = $loader->LoadByEntity($this->userCompanyId);
        //kirim ke view
        $this->Set("creditors", $creditors);
        $this->Set("payment", $payment);
        $this->Set("banks", $banks);
        $this->Set("warkattypes", $warkattypes);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
	}

    public function delete($paymentId) {
        // Cek datanya
        $payment = new Payment();
        $log = new UserAdmin();
        $payment = $payment->FindById($paymentId);
        if($payment == null){
            $this->Set("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        if($payment->PaymentStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -APPROVED-",$payment->PaymentNo));
            redirect_url("ap.payment");
        }
        /** @var $payment Payment */
        if ($payment->Delete($paymentId) > 0) {
            $this->persistence->SaveState("info", sprintf("Data Payment No: %s sudah berhasil dihapus", $payment->PaymentNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Delete Payment',$payment->PaymentNo,'Success');
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Payment No: %s gagal dihapus", $payment->PaymentNo));
        }
        redirect_url("ap.payment");
    }

    public function void($paymentId) {
        // Cek datanya
        $payment = new Payment();
        $log = new UserAdmin();
        $payment = $payment->FindById($paymentId);
        if($payment == null){
            $this->Set("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        if($payment->PaymentStatus == 1){
            $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -APPROVED-",$payment->PaymentNo));
            redirect_url("ap.payment");
        }
        if($payment->PaymentStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -POSTED-",$payment->PaymentNo));
            redirect_url("ap.payment");
        }
        if($payment->PaymentStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Payment No. %s sudah berstatus -VOID-",$payment->PaymentNo));
            redirect_url("ap.payment");
        }
        //cek alokasi pembayaran
        if ($payment->AllocateAmount > 0){
            $this->persistence->SaveState("error", sprintf("Data Payment No. %s sudah ada alokasi penerimaan!",$payment->PaymentNo));
            redirect_url("ap.payment");
        }
        /** @var $payment Payment */
        if ($payment->Delete($paymentId) > 0) {
            $this->persistence->SaveState("info", sprintf("Data Payment No: %s sudah berhasil dibatalkan", $payment->PaymentNo));
            $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Delete Payment',$payment->PaymentNo,'Success');
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Payment No: %s gagal dibatalkan", $payment->PaymentNo));
        }
        redirect_url("ap.payment");
    }

	public function add_detail($paymentId = null) {
        $payment = new Payment($paymentId);
        $log = new UserAdmin();
        $recdetail = new PaymentDetail();
        $recdetail->PaymentId = $paymentId;
        if (count($this->postData) > 0) {
            $recdetail->InvoiceId = $this->GetPostValue("aInvoiceId");
            $recdetail->InvoiceOutstanding = $this->GetPostValue("aInvoiceOutStanding");
            $recdetail->AllocateAmount = $this->GetPostValue("aAllocateAmount");
            $recdetail->PotPph = 0;
            $recdetail->PotLain = 0;
            $rs = $recdetail->Insert()== 1;
            if ($rs > 0) {
                $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Add Payment detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$payment->PaymentNo,'Success');
                echo json_encode(array());
            } else {
                echo json_encode(array('errorMsg'=>'Some database errors occured.'));
            }
        }
	}    

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $recdetail = new PaymentDetail();
        $recdetail = $recdetail->FindById($id);
        if ($recdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($recdetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Delete Payment detail -> Inv. '.$recdetail->InvoiceNo.' = '.$recdetail->AllocateAmount,$recdetail->PaymentId,'Success');
            printf("Data Detail Payment ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Payment ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($paymentId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $loader = null;
        $payment = new Payment();
        $payment = $payment->LoadById($paymentId);
        if($payment == null){
            $this->persistence->SaveState("error", "Maaf Data Payment dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.payment");
        }
        // load details
        $payment->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new Karyawan();
        $banks = $loader->LoadAll();
        $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
        //kirim ke view
        $this->Set("sales", $banks);
        $this->Set("cabangs", $cabang);
        $this->Set("payment", $payment);
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
            $sPaymentStatus = $this->GetPostValue("PaymentStatus");
            $sWarkatTypeId = $this->GetPostValue("WarkatTypeId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $payment = new Payment();
            $reports = $payment->Load4Reports($this->userCompanyId,$sCabangId,$sWarkatBankId,$sContactsId,$sWarkatTypeId,$sPaymentStatus,$sStartDate,$sEndDate);
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sWarkatBankId = 0;
            $sPaymentStatus = -1;
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
        $this->Set("PaymentStatus",$sPaymentStatus);
        $this->Set("WarkatTypeId",$sWarkatTypeId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCompanyId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("warkattypes",$warkattypes);
    }

    public function getPaymentItemRows($id){
        $payment = new Payment();
        $rows = $payment->GetPaymentItemRow($id);
        print($rows);
    }

    public function createTextPayment($id){
        $payment = new Payment($id);
        if ($payment <> null){
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $payment->CompanyName);
            fwrite($myfile, "\n".'FAKTUR PENJUALAN');

            fclose($myfile);
        }
    }

    public function getoutstandinginvoices_plain($creditorId = 0 ,$invoiceNo = null){
        require_once(MODEL . "ap/invoice.php");
        $ret = 'ER|0';
        if($invoiceNo != null || $invoiceNo != ''){
            /** @var $invoice Invoice[] */
            $invoice = new Ap\Invoice();
            $invoice = $invoice->GetUnpaidInvoices($creditorId,$invoiceNo);
            if ($invoice != null){
                $ret = 'OK|'.$invoice->Id.'|'.date(JS_DATE,$invoice->InvoiceDate).'|'.date(JS_DATE,$invoice->DueDate).'|'.$invoice->BalanceAmount;
            }
        }
        print $ret;
    }

    public function getoutstandinginvoices_json($creditorId){
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $payment = new Payment();
        $itemlists = $payment->GetJSonUnpaidInvoices($creditorId);
        echo json_encode($itemlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $payment = new Payment();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process payment
            if($payment->PaymentStatus == 0 && $payment->BalanceAmount == 0 && $payment->AllocateAmount > 0){
                $rs = $payment->Approve($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Approve Payment',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Data Payment No.: '%s' (%s) telah berhasil di-approve.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Approve Payment',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Payment: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($payment->BalanceAmount > 0){
                    $errors[] = sprintf("Payment No.%s belum dialokasi semua!",$payment->PaymentNo);
                }else {
                    $errors[] = sprintf("Data Payment No.%s tidak berstatus -Draft- !", $payment->PaymentNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $payment = new Payment();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process invoice
            if($payment->PaymentStatus == 1){
                $rs = $payment->Unapprove($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Un-Approve Payment',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Data Payment No.: '%s' (%s) telah berhasil di-batalkan.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Un-Approve Payment',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Payment: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($payment->PaymentStatus == 0){
                    $errors[] = sprintf("Data Payment No.%s masih berstatus -Draft- !",$payment->PaymentNo);
                }else{
                    $errors[] = sprintf("Data Payment No.%s masih berstatus -Posted- !",$payment->PaymentNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function posting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $payment = new Payment();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process payment
            if($payment->PaymentStatus == 1){
                $rs = $payment->Posting($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Posting Payment',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Data Payment No.: '%s' (%s) telah berhasil di-posting.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Posting Payment',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses posting Data Payment: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Payment No.%s tidak berstatus -Approved- !",$payment->PaymentNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function unposting() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.payment");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $payment = new Payment();
            $payment = $payment->FindById($id);
            /** @var $payment Payment */
            // process invoice
            if($payment->PaymentStatus == 2){
                $rs = $payment->Unposting($payment->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Un-Approve Payment',$payment->PaymentNo,'Success');
                    $infos[] = sprintf("Data Payment No.: '%s' (%s) telah berhasil di-batalkan.", $payment->PaymentNo, $payment->PaymentDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCompanyId,'ap.payment','Un-Approve Payment',$payment->PaymentNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Payment: '%s'. Message: %s", $payment->PaymentNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($payment->PaymentStatus == 1){
                    $errors[] = sprintf("Data Payment No.%s masih berstatus -POSTED- !",$payment->PaymentNo);
                }else{
                    $errors[] = sprintf("Data Payment No.%s masih berstatus -DRAFT- !",$payment->PaymentNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.payment");
    }

    public function overview() {
        require_once(MODEL . 'master/creditor.php');
        require_once(MODEL . 'status_code.php');

        $payment = new Payment();

        if (count($this->getData) > 0) {
            $startDate = strtotime($this->GetGetValue("startDate"));
            $endDate = strtotime($this->GetGetValue("endDate"));
            $creditorId = $this->GetGetValue("creditorId");
            $status = $this->GetGetValue("status");
            $output = $this->GetGetValue("output", "web");

            $this->connector->CommandText =
                "SELECT a.*, b.alloc_amt, b.deduction_amt, c.entity_cd, d.creditor_cd, d.creditor_name, e.short_desc, f.bank_name, g.acc_no
FROM t_ap_payment_master AS a
	JOIN (
		SELECT aa.payment_id, SUM(aa.allocate_amount) AS alloc_amt, SUM(aa.pot_pph) AS deduction_amt
		FROM t_ap_payment_detail AS aa
		GROUP BY aa.payment_id
	) AS b ON a.id = b.payment_id
	JOIN cm_company AS c ON a.entity_id = c.entity_id
	JOIN ap_creditor_master AS d ON a.creditor_id = d.id
	JOIN sys_status_code AS e ON a.payment_status = e.code AND e.key = 'payment_status'
	JOIN cm_bank_account AS f ON a.warkat_bank_id = f.id
	JOIN cm_acc_detail AS g ON f.acc_id = g.id
WHERE a.is_deleted = 0";

            if ($this->userCompanyId != 7) {
                $this->connector->CommandText .= " AND c.entity_id = ?entity";
                $this->connector->AddParameter("?entity", $this->userCompanyId);
            }

            if ($creditorId != -1) {
                $this->connector->CommandText .= " AND d.id = ?creditor";
                $this->connector->AddParameter("?creditor", $creditorId);
            }

            if ($status != -1) {
                $this->connector->CommandText .= " AND a.payment_status = ?status";
                $this->connector->AddParameter("?status", $status);
            }

            $this->connector->CommandText .= " AND a.payment_date BETWEEN ?start AND ?end ORDER BY a.payment_date DESC";
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $report = $this->connector->ExecuteQuery();

        } else {
            $startDate = time();
            $endDate = time();
            $creditorId = -1;
            $status = -1;
            $output = "web";
            $report = null;
        }

        $creditor = new Creditor();
        $creditorAll = $this->userCompanyId != 7 ? $creditor->LoadByEntity($this->userCompanyId) : $creditor->LoadAll();
        $this->Set("creditors", $creditorAll);
        $this->Set("debt", $creditor->FindById($creditorId));
        $this->Set("creditorId", $creditorId);

        $syscode = new StatusCode();
        $this->Set("codes", $syscode->LoadPaymentStatus());
        $this->Set("codeName", $syscode->FindBy('payment_status', $status));
        $this->Set("status", $status);

        $this->Set("report", $report);
        $this->Set("startDate", $startDate);
        $this->Set("endDate", $endDate);
        $this->Set("output", $output);
    }
}


// End of File: estimasi_controller.php
