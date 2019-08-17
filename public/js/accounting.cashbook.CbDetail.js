/**
 * Untuk mengelompokkan data Voucher Cashbook Detail. Ini merupakan data detail yang berulang-ulang.
 * Sama seperti accounting.voucher.VcDetail tetapi ada beberapa perbedaan terutama dibagian Jenis transaksi sehingga tidak ada Debit dan Credit
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */

function CbDetail(cloned, data) {
	// Property Creation
	this.DivContainer = cloned;
	this.Id = cloned.find("#Id");
	this.TrxType = cloned.find("#TrxType");
	this.LblBank = cloned.find("#lblBank");
	this.Bank = cloned.find("#Bank");
    this.Unit = cloned.find("#Unit");
	this.Department = cloned.find("#Department");
	this.Activity = cloned.find("#Activity");
	this.Project = cloned.find("#Project");
	this.Amount = cloned.find("#Amount");
	this.Note = cloned.find("#Note");
	this.Debtor = cloned.find("#Debtor");
	this.Creditor = cloned.find("#Creditor");
	this.Employee = cloned.find("#Employee");

	// Initialization
	this.Amount.autoNumeric();
	this.TrxType.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatOptionList,
		formatSelection: this.formatOptionResult
	});
	this.Debtor.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatDebtorList,
		formatSelection: this.formatDebtorResult,
		minimumInputLength: 2
	});
	this.Creditor.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatCreditorList,
		formatSelection: this.formatCreditorResult,
		minimumInputLength: 2
	});
	this.Employee.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatEmployeeList,
		formatSelection: this.formatEmployeeResult
	});
	if (data != undefined) {
		this.AssignData(data);
	}

	// Event Registration
	var self = this;    // Diperlukan reference ke instance object CbDetail karena pada event handler this sudah refer ke object yang invoke event
	this.TrxType.change(function(e) { self.TrxType_Change(this, e); });

	cloned.find("#btnDelete").click(function() {
		cloned.slideUp("fast", function() {
			$(this).parent().remove();
		});
	});

	cloned.show();
}

// Member declaration using prototype technique
CbDetail.prototype.AssignData = function (data) {
	this.Id.val(data.Id);
	this.TrxType.select2("val", data.TrxTypeId);
	this.TrxType_Change(this.TrxType[0], null);
	this.Bank.val(data.BankId);
    this.Unit.val(data.UnitId);
	this.Department.val(data.DepartmentId);
	this.Activity.val(data.ActivityId);
	this.Project.val(data.ProjectId);
	this.Amount.autoNumericSet(data.Amount);
	this.Note.val(data.Note);
	this.Debtor.val(data.DebtorId);
	this.Creditor.val(data.CreditorId);
	this.Employee.val(data.EmployeeId);

	if (data.Id != "") {
		// ada ID nya bearti data lama
		this.DivContainer.find("#btnDelete").hide();
		this.DivContainer.find("#markDelete").show().val(data.Id);	// Jgn Lupa Set Valuenya
		this.DivContainer.find("#lblMarkDelete").show();
	}
	if (data.MarkedForDeletion) {
		this.DivContainer.find("#markDelete").attr("checked", "checked");
	}
};

CbDetail.prototype.TrxType_Change = function(sender, e) {
	var transType = transTypes[sender.value];
	if (transType == undefined) {
		// Tidak ketemu data lsg hide saja
		this.LblBank.hide();
		this.Bank.hide();
	} else {
		// Ada data akan di show jika salah 1 kosong
		if (transType.Debit == null || transType.Credit == null) {
			this.LblBank.show();
			this.Bank.show();
		} else {
			this.LblBank.hide();
			this.Bank.hide();
		}
	}
};

// Select2 function
CbDetail.prototype.formatOptionList = function(state) {
	var transType = transTypes[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit">' + transType.DebitName + '</div><div class="colCredit">' + transType.CreditName + '</div><div class="colDesc">' + transType.Description + '</div>';
};

CbDetail.prototype.formatOptionResult = function(state) {
	var transType = transTypes[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit bold blue">' + transType.DebitName + '</div><div class="colCredit bold blue">' + transType.CreditName + '</div><div class="colDesc bold blue">' + transType.Description + '</div>';
};

CbDetail.prototype.formatDebtorList = function(state) {
	if (state.id == "") {
		return "-- PILIH DEBTOR --";
	}

	var originalOption = $(state.element);
	return '<div class="colSbu">' + originalOption.data("sbu") + '</div><div class="colCode">' + originalOption.data("code") + '</div><div class="colNama">' + originalOption.data("nama") + '</div><div class="colNote">' + originalOption.data("note") + '</div>';
};

CbDetail.prototype.formatDebtorResult = function(state) {
	if (state.id == "") {
		return "-- PILIH DEBTOR --";
	}

	var originalOption = $(state.element);
	return '<div class="colSbu bold blue">' + originalOption.data("sbu") + '</div><div class="colCode bold blue">' + originalOption.data("code") + '</div><div class="colNama bold blue">' + originalOption.data("nama") + '</div><div class="colNote bold blue">' + originalOption.data("note") + '</div>';
};

CbDetail.prototype.formatCreditorList = function(state) {
	if (state.id == "") {
		return "-- PILIH KREDITOR --";
	}

	var originalOption = $(state.element);
	return '<div class="colSbu">' + originalOption.data("sbu") + '</div><div class="colCode">' + originalOption.data("code") + '</div><div class="colNama">' + originalOption.data("nama") + '</div>';
};

CbDetail.prototype.formatCreditorResult = function(state) {
	if (state.id == "") {
		return "-- PILIH KREDITOR --";
	}

	var originalOption = $(state.element);
	return '<div class="colSbu bold blue">' + originalOption.data("sbu") + '</div><div class="colCode bold blue">' + originalOption.data("code") + '</div><div class="colNama bold blue">' + originalOption.data("nama") + '</div>';
};

CbDetail.prototype.formatEmployeeList = function(state) {
	if (state.id == "") {
		return "-- PILIH KARYAWAN --";
	}

	var originalOption = $(state.element);
	return '<div class="colCode">' + originalOption.data("nik") + '</div><div class="colNote">' + originalOption.data("nama") + '</div>';
};

CbDetail.prototype.formatEmployeeResult = function(state) {
	if (state.id == "") {
		return "-- PILIH KARYAWAN --";
	}

	var originalOption = $(state.element);
	return '<div class="colCode bold blue">' + originalOption.data("nik") + '</div><div class="colNote bold blue">' + originalOption.data("nama") + '</div>';
};