/**
 * Untuk mengelompokkan data Detail Invoice AP. Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */
function InvDetail(cloned, data) {
    // Property Creation
    this.DivContainer = cloned;
    this.Id = cloned.find("#Id");
    this.TrxType = cloned.find("#TrxType");
    this.TaxScheme = cloned.find("#TaxScheme");
    this.TrAccount = cloned.find("#trAccount");
    this.AccountId = cloned.find("#AccountId");
    this.Description = cloned.find("#Description");
    this.Qty = cloned.find("#Qty");
    this.UomCd = cloned.find("#UomCd");
    this.Price = cloned.find("#Price");
	this.Department = cloned.find("#Department");
	this.Activity = cloned.find("#Activity");
	this.Project = cloned.find("#Project");

    // Initialization
    this.Qty.autoNumeric();
    this.Price.autoNumeric();
	this.TrxType.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatOptionList,
		formatSelection: this.formatOptionResult
	});
    if (data != undefined) {
        this.AssignData(data);
    }

    // Event Registration
    var self = this;    // Diperlukan reference ke instance object RentalCharge karena pada event handler this sudah refer ke object yang invoke event
    this.TrxType.change(function(e) { self.TrxType_Change(this, e); });
	//this.Department.change(function(e) { self.Department_Change(this, e); });
    cloned.find("#btnDelete").click(function() {
        cloned.slideUp("fast", function() {
            $(this).parent().remove();
        });
    });

    cloned.show();
}

// Member declaration using prototype technique
InvDetail.prototype.AssignData = function(data) {
    this.TrxType.select2("val", data.TrxTypeId);
    this.TaxScheme.val(data.TaxSchemeId);
    this.AccountId.val(data.AccountId);
    this.Description.val(data.Description);
    this.Qty.autoNumericSet(data.Qty);
    this.UomCd.val(data.UomCd);
    this.Price.autoNumericSet(data.Price);
	this.Department.val(data.DepartmentId);
	//this.Department_Change(this.Department[0], null);
	this.Activity.val(data.ActivityId);
	this.Project.val(data.ProjectId);

    if (data.Id != "") {
        // ada ID nya bearti data lama
        this.Id.val(data.Id);
        this.DivContainer.find("#btnDelete").hide();
        this.DivContainer.find("#markDelete").show().val(data.Id);	// Jgn Lupa Set Valuenya
        this.DivContainer.find("#lblMarkDelete").show();
    }
    if (data.MarkedForDeletion) {
        this.DivContainer.find("#markDelete").attr("checked", "checked");
    }
};

/**
 * @param sender
 * @param e
 * @event
 */
InvDetail.prototype.TrxType_Change = function(sender, e) {
    var id = sender.value;
    var trxType = trxTypesJs[id];

    if (trxType != undefined && trxType.Debit == null) {
        this.TrAccount.show();
    } else {
        this.TrAccount.hide();
    }
    this.AccountId.val("");
};

/**
 *
 * @param sender
 * @param e
 * @event
 */

// Select2 function
InvDetail.prototype.formatOptionList = function(state) {
	var transType = trxTypesJs[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit">' + transType.DebitName + '</div><div class="colCredit">' + transType.CreditName + '</div><div class="colDesc">' + transType.Description + '</div>';
};

InvDetail.prototype.formatOptionResult = function(state) {
	var transType = trxTypesJs[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit bold blue">' + transType.DebitName + '</div><div class="colCredit bold blue">' + transType.CreditName + '</div><div class="colDesc bold blue">' + transType.Description + '</div>';
};