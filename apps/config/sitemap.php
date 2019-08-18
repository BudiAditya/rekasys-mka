<?php
/**
 * Later this file will be automatically auto-generated...
 * Menu are stored in database but we create this file for faster menu creation
 */

// Load required library
require_once(LIBRARY . "node.php");

// This act as menu container
$root = new Node("[ROOT]");
$root->AddNode("HOME", "main");
$menu = $root->AddNode("COMMON", null, "menu");
	$menu->AddNode("Common Master Files", null, "title");
	$menu->AddNode("Company Information", "master.company");
    $menu->AddNode("Projects Master", "master.project");
    $menu->AddNode("Activity Master", "master.activity");
    $subMenu = $menu->AddNode("Unit & Equipment Master", null, "submenu");
        $subMenu->AddNode("Unit Brand", "master.unitbrand");
        $subMenu->AddNode("Unit Type", "master.unittype");
        $subMenu->AddNode("Unit Class", "master.unitclass");
        $subMenu->AddNode("Unit Component", "master.unitcomp");
        $subMenu->AddNode("Unit Master", "master.units");
    $menu->AddNode("Document Type Master", "common.doctype");
    $menu->AddNode("Document Counter", "common.doccounter");
	$menu->AddNode("Unit of Measure", "common.uom");
    $menu->AddNode("Setup and Administration", null, "title");
	$menu->AddNode("User Administration", "master.useradmin");
    $menu->AddNode("Password Change", "main/change_password");
	$menu->AddNode("Accounting Periode", "main/set_periode");

$menu = $root->AddNode("PRODUCTION", null, "menu");
	$menu->AddNode("Master Files", null, "title");


$menu = $root->AddNode("PLANT & MAINTENANCE", null, "menu");
	$menu->AddNode("Master Files", null, "title");


    $menu = $root->AddNode("A/R", null, "menu");
    $menu->AddNode("A/R Master Files", null, "title");
        $menu->AddNode("Debtor Type", "common.debtortype");
        $menu->AddNode("Debtor Master", "master.debtor");
        $menu->AddNode("Invoice Type", "common.arinvoicetype");
    $menu->AddNode("A/R Transaction", null, "title");
        $menu->AddNode("A/R Invoice", "ar.invoice");
        $menu->AddNode("A/R Receipt", "ar.receipt");
    $menu->AddNode("A/R Transaction", null, "title");
        $menu->AddNode("A/R Invoice Listing", "ar.invoice/overview");
        $menu->AddNode("A/R Receipt Listing", "ar.receipt/overview");
        $menu->AddNode("Kartu Piutang Debtor", "ar.report/kartu_piutang");
        $menu->AddNode("Rekap Piutang Debtor", "ar.report/rekap_piutang");
        $menu->AddNode("A/R Rekap Debtor Aging", "ar.report/rekap_aging");
        $menu->AddNode("Aging Piutang Per Debtor", "ar.report/detail_aging");

$menu = $root->AddNode("A/P", null, "menu");
    $menu->AddNode("A/P Master Files", null, "title");
        $menu->AddNode("Creditor Type", "common.creditortype");
        $menu->AddNode("Creditor Master", "master.creditor");
        $menu->AddNode("Invoice Type", "common.apinvoicetype");
    $menu->AddNode("A/P Transactions", null, "title");
        $menu->AddNode("A/P Invoice", "ap.invoice");
        $menu->AddNode("A/P Payment", "ap.payment");
    $menu->AddNode("A/P Reports", null, "title");
        $menu->AddNode("A/P Invoice Listing", "ap.invoice/overview");
        $menu->AddNode("A/P Payment Listing", "ap.payment/overview");
        $menu->AddNode("Kartu Hutang Supplier", "ap.report/kartu_hutang");
        $menu->AddNode("Rekap Hutang Supplier", "ap.report/rekap_hutang");
        $menu->AddNode("Rekap Aging Hutang Supplier", "ap.report/rekap_aging");
        $menu->AddNode("Aging Hutang Supplier", "ap.report/detail_aging");

$menu = $root->AddNode("PURCHASING", null, "menu");
	$menu->AddNode("Procurement Transaction", null, "title");
    	$menu->AddNode("Prices & Quotation List", "ap.inquiry");
        $menu->AddNode("Purchase Requisition (PR)", "purchase.pr");
        $menu->AddNode("Proses PR menjadi PO", "purchase.po/find_pr");
        $menu->AddNode("Repair Requisition (RR)", "purchase.rr");
        $menu->AddNode("Proses RR menjadi RO", "purchase.ro/find_rr");
	$menu->AddNode("Purchase Order Process", null, "title");
        $menu->AddNode("Purchase Order (PO)", "purchase.po");
        $menu->AddNode("Repair Order (RO)", "purchase.ro");
        $menu->AddNode("Rekap Barang PO", "purchase.po/item_recap");
        $menu->AddNode("Rekap Barang RO", "purchase.ro/item_recap");

$menu = $root->AddNode("INVENTORY", null, "menu");
	$menu->AddNode("Master Files", null, "title");
	//$menu->AddNode("Gudang", "inventory.warehouse");
	    $menu->AddNode("Items Category", "inventory.itemcategory");
	    $menu->AddNode("Item Stock Location", "common.stocklocation");
	    $menu->AddNode("Items & Parts List", "inventory.item");
        $menu->AddNode("Unit of Measure", "common.uom");
        $menu->AddNode("Inventory Opening", "inventory.icobal");
	$menu->AddNode("Inventory Transaction", null, "title");
        $menu->AddNode("Material Requisition (MR)", "inventory.mr");
        $menu->AddNode("M/R Process (PR/IS)", "inventory.mr/unfinished");
        $menu->AddNode("Purchase Requisition (PR)", "inventory.pr");
        $menu->AddNode("Repair Requisition (RR)", "inventory.rr");
        $menu->AddNode("Goods Receipt Note (GRN)", "inventory.gn");
        $menu->AddNode("Repaired Receipt Note (RRN)", "inventory.rn");
        $menu->AddNode("Inventory Issue (IS)", "inventory.is");
    $menu->AddNode("Stock Monitoring", null, "title");
	    $menu->AddNode("MR Unfinished", "inventory.mr/search_unfinished");
        $menu->AddNode("Item Stock Position", "inventory.stock");
        $menu->AddNode("Rekap Item GRN", "inventory.gn/item_recap");
        $menu->AddNode("Rekap Item Issue", "inventory.is/item_recap");
	$menu->AddNode("Stock Adjustment", null, "title");
        $menu->AddNode("Stock Opname (SO)", "inventory.so");

$menu = $root->AddNode("CASH BOOK", null, "menu");
	$menu->AddNode("Master Files", null, "title");
        $menu->AddNode("Cash/Bank Master File", "master.bank");
        $menu->AddNode("Cash Request Category", "accounting.cashrequestcategory");
        $menu->AddNode("Cash Book Transaction", null, "title");
    //	$menu->AddNode("Cash/Bank Opening Balance", "cb.openingbalance");
        $menu->AddNode("Cash Request (NPKP)", "accounting.cashrequest");
	$subMenu = $menu->AddNode("NPKP Process", null, "submenu");
		$subMenu->AddNode("NPKP Ready for Funding", "accounting.funding/list");
		$subMenu->AddNode("NPKP Funding List", "accounting.funding");
	$subMenu = $menu->AddNode("Cash In Transaction", null, "submenu");
		$subMenu->AddNode("Cash In List", "accounting.bkm");
		$subMenu->AddNode("Cash In Entry", "accounting.bkm/add");
	$subMenu = $menu->AddNode("Cash Out Transaction", null, "submenu");
		$subMenu->AddNode("Cash Out List", "accounting.bkk");
		$subMenu->AddNode("Cash Out Entry", "accounting.bkk/add");
	$menu->AddNode("Cash Book Reports", null, "title");
	$subMenu = $menu->AddNode("Cash/Bank Reports", null, "submenu");
		$subMenu->AddNode("Rekap Cash/Bank In", "accounting.cashbookreport/recap_in");
		$subMenu->AddNode("Rekap Cash/Bank Out", "accounting.cashbookreport/recap_out");
		$subMenu->AddNode("Cash/Bank In", "accounting.cashbookreport/rpt_in");
		$subMenu->AddNode("Cash/Bank Out", "accounting.cashbookreport/rpt_out");
//	$menu->AddNode("Cash/Bank Journal", "cb.journal");
	$subMenu = $menu->AddNode("Cash Flow Report", null, "submenu");
		$subMenu->AddNode("Rekap Cash Flow", "accounting.cashflow/rpt_recap");
		$subMenu->AddNode("Detail Per Akun", "accounting.cashflow/rpt_detail");
    $menu->AddNode("NPKP Report", "accounting.cashrequest/overview");
	$menu->AddNode("Laporan BKK dan BKM", "accounting.cashbookreport/bkk_bkm");
    $menu->AddNode("Laporan Petty Cash", "accounting.cashbookreport/pettycash");

$menu = $root->AddNode("ASSETS", null, "menu");
    $menu->AddNode("Master Files", null, "title");
        $menu->AddNode("Assets Category", "asset.assetcategory");
        $menu->AddNode("Asset List", "asset.asset");
	$menu->AddNode("Asset Depreciation", null, "title");
        $menu->AddNode("Depreciation Process", "asset.depreciation/process_all");
        $menu->AddNode("Depreciation Journal", "asset.voucher");
    $menu->AddNode("Asset Report", null, "title");
        $menu->AddNode("Asset Report", "asset.asset/report");
        $menu->AddNode("Depreciation Report", "asset.depreciation/report");

$menu = $root->AddNode("H R D", null, "menu");
    $menu->AddNode("Master Files", null, "title");
        $menu->AddNode("Department Master", "master.department");
        $menu->AddNode("Employee Master", "hr.employee");

$menu = $root->AddNode("T M", null, "menu");
    $menu->AddNode("Master Files", null, "title");
        $menu->AddNode("Tax Type", "tax.taxtype");
        $menu->AddNode("Tax Scheme", "common.taxrate");
    $menu->AddNode("Tax Transaction", null, "title");
        $menu->AddNode("Tax Invoice List", "tax.taxinvoice");

$menu = $root->AddNode("LEDGER", null, "menu");
	$menu->AddNode("G/L Master Files", null, "title");
	    $menu->AddNode("Chart of Account (COA)", "master.coa");
        $subMenu = $menu->AddNode("Transaction Type", null, "submenu");
            $subMenu->AddNode("Transaction Class", "common.trxclass");
            $subMenu->AddNode("Transaction Type", "common.trxtype");
        $subMenu = $menu->AddNode("Opening Balance", null, "submenu");
            $subMenu->AddNode("Account Opening Balance", "accounting.obal");
            $subMenu->AddNode("Employee Loan Opening", "hr.obal");
            $subMenu->AddNode("A/R Debtor Opening", "ar.obal");
            $subMenu->AddNode("A/P Creditor Opening", "ap.obal");
        //$menu->AddNode("Accounting Voucher Type", "accounting.vouchertype");
    $menu->AddNode("G/L Operation", null, "title");
        $menu->AddNode("General Voucher Entry", "accounting.voucher/add_master");
	    $menu->AddNode("Accounting Voucher List", "accounting.voucher");
	    $menu->AddNode("Print Voucher", "accounting.voucher/print_all");
//	$menu->AddNode("Accounting Process", null, "title");
//	$menu->AddNode("Month & Year Closing", "gl.fgl_glclosing");
	$menu->AddNode("G/L Reports", null, "title");
	$subMenu = $menu->AddNode("Sub Ledger Reports", null, "submenu");
		$subMenu->AddNode("Detail", "accounting.subledger/detail");
        $subMenu->AddNode("Summary", "accounting.subledger/recap");
    $subMenu = $menu->AddNode("Journal Reports", null, "submenu");
        $subMenu->AddNode("Detail", "accounting.report/journal");
        $subMenu->AddNode("Summary", "accounting.report/recap");
    $menu->AddNode("Cost & Revenue", "accounting.subledger/costrevenue");
    $menu->AddNode("Trial Balance", "accounting.trialbalance/recap");
    $menu->AddNode("Worksheet Balance", "accounting.worksheetbalance/recap");
    $subMenu = $menu->AddNode("Ledger Report", null, "submenu");
        $subMenu->AddNode("Account Opening Balance", "ledger.obal");
        $subMenu->AddNode("SubLedger Detail", "ledger.subledger/detail");
        $subMenu->AddNode("SubLedger Summary", "ledger.subledger/recap");
        $subMenu->AddNode("Journal Detail", "ledger.report/journal");
        $subMenu->AddNode("Journal Summary", "ledger.report/recap");
        $subMenu->AddNode("Cost & Revenue", "ledger.subledger/costrevenue");
        $subMenu->AddNode("Trial Balance", "ledger.trialbalance/recap");
        $subMenu->AddNode("Worksheet Balance", "ledger.worksheetbalance/recap");

// Special access for corporate
$persistence = PersistenceManager::GetInstance();
$isCorporate = $persistence->LoadState("is_corporate");
$forcePeriode = $persistence->LoadState("force_periode");

if ($forcePeriode) {
	$root->AddNode("Ganti Periode", "main/set_periode");
}
//$root->AddNode("Notifikasi", "main");
$root->AddNode("LOGOUT", "home/logout");

// End of file: sitemap.php.php
