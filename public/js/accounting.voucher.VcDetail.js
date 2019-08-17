/**
 * Untuk mengelompokkan data Voucher Detail. Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */

function VcDetail(cloned, data) {
	// Property Creation
	this.DivContainer = cloned;
	this.Id = cloned.find("#Id");
	this.Debit = cloned.find("#Debit");
	this.Credit = cloned.find("#Credit");
	this.Department = cloned.find("#Department");
	this.Activity = cloned.find("#Activity");
	this.Project = cloned.find("#Project");
	this.Amount = cloned.find("#Amount");
	this.Note = cloned.find("#Note");
	this.Debtor = cloned.find("#Debtor");
	this.Creditor = cloned.find("#Creditor");
	this.Employee = cloned.find("#Employee");
    this.Unit = cloned.find("#Unit");

	// Initialization
	this.Amount.autoNumeric();
	this.Debit.select2({
		placeholderOption: "first",
		allowClear: false,
		minimumInputLength: 2,
		formatResult: this.formatOptionList,
		formatSelection: this.formatOptionResult
	});
	this.Credit.select2({
		placeholderOption: "first",
		allowClear: false,
		minimumInputLength: 2,
		formatResult: this.formatOptionList,
		formatSelection: this.formatOptionResult
	});
	if (data != undefined) {
		this.AssignData(data);
	}

	// Event Registration
	var self = this;    // Diperlukan reference ke instance object VcDetail karena pada event handler this sudah refer ke object yang invoke event
	//this.Department.change(function(e) { self.Department_Change(this, e); });

	cloned.find("#btnDelete").click(function() {
		cloned.slideUp("fast", function() {
			$(this).parent().remove();
		});
	});

	cloned.show();
}

// Member declaration using prototype technique
VcDetail.prototype.AssignData = function (data) {
	this.Id.val(data.Id);
	this.Debit.select2("val", data.AccDebitId);
	this.Credit.select2("val", data.AccCreditId);
	this.Department.val(data.DepartmentId);
	//this.Department_Change(this.Department[0], null);
	this.Activity.val(data.ActivityId);
	this.Project.val(data.ProjectId);
	this.Amount.autoNumericSet(data.Amount);
	this.Note.val(data.Note);
	this.Debtor.val(data.DebtorId);
	this.Creditor.val(data.CreditorId);
	this.Employee.val(data.EmployeeId);
    this.Unit.val(data.UnitId);

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

// Select2 function
VcDetail.prototype.formatOptionList = function(state) {
	if (state.id == "") {
		return "-- PILIH AKUN --";
	}

	var originalOption = $(state.element);
	return '<div class="colCode">' + originalOption.data("code") + '</div><div class="colText">' + originalOption.data("text") + '</div>';
};

VcDetail.prototype.formatOptionResult = function(state) {
	if (state.id == "") {
		return "-- PILIH AKUN --";
	}

	var originalOption = $(state.element);
	return '<div class="colCode bold blue">' + originalOption.data("code") + '</div><div class="colText bold blue">' + originalOption.data("text") + '</div>';
};