/**
 * Untuk mengelompokkan data Detail Invoice AR. Ini merupakan data detail yang berulang-ulang.
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
	this.TrxType = cloned.find("#TrxId");
	this.TaxScheme = cloned.find("#TaxId");
	this.BaseAmount = cloned.find("#BaseAmount");
	this.Description = cloned.find("#Description");

	// Initialization
	this.BaseAmount.autoNumeric();
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
	cloned.find("#btnDelete").click(function() {
		cloned.slideUp("fast", function() {
			$(this).parent().remove();
		});
	});

	cloned.show();
}

// Member declaration using prototype technique
InvDetail.prototype.AssignData = function(data) {
	this.TrxType.select2("val", data.TrxId);
	this.TaxScheme.val(data.TaxSchemaId);
	this.BaseAmount.autoNumericSet(data.BaseAmount);
	this.Description.val(data.TrxDesc);

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
	//tampilkan nilai kolom "Deskripsi" saat kolom "Tipe transaksi" diklik
	if (sender.value == "") {
		this.Description.val("");
	} else {
		//nilainya dalam bentuk array dan diambil via JSON
		this.Description.val(jsTrxIds[sender.value].Description);
	}
};

// Select2 function
InvDetail.prototype.formatOptionList = function(state) {
	var transType = jsTrxIds[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit">' + transType.DebitName + '</div><div class="colCredit">' + transType.CreditName + '</div><div class="colDesc">' + transType.Code + " - " + transType.Description + '</div>';
};

InvDetail.prototype.formatOptionResult = function(state) {
	var transType = jsTrxIds[state.id];
	if (transType == undefined) {
		return "-- PILIH JENIS TRANSAKSI --";
	}

	return '<div class="colDebit bold blue">' + transType.DebitName + '</div><div class="colCredit bold blue">' + transType.CreditName + '</div><div class="colDesc bold blue">' + transType.Code + " - " + transType.Description + '</div>';
};