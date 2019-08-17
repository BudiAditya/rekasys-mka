/**
 * Untuk mengelompokkan data Tax Invoice Detail
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
	this.TrxId = cloned.find("#TrxId");
	this.TrxCd = cloned.find("#TrxCd");
	this.Dpp = cloned.find("#Dpp");
	this.Tax = cloned.find("#Tax");
	this.Description = cloned.find("#Description");

	// Initialization
	this.Dpp.autoNumeric();
	this.Tax.autoNumeric();
	if (data != undefined) {
		this.AssignData(data);
	}

	// Event Registration
	var self = this;    // Diperlukan reference ke instance object VcDetail karena pada event handler this sudah refer ke object yang invoke event
	this.TrxId.change(function(e) { self.TrxId_Change(this, e); });

	cloned.find("#btnDelete").click(function() {
		cloned.slideUp("fast", function() {
			$(this).parent().remove();
		});
	});

	cloned.show();
}

// Member declaration using prototype technique
InvDetail.prototype.AssignData = function (data) {
	this.Id.val(data.Id);
	this.TrxId.val(data.TrxId);
	this.TrxCd.val(data.TrxCd);
	this.Dpp.autoNumericSet(data.Dpp);
	this.Tax.autoNumericSet(data.Tax);
	this.Description.val(data.Description);

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

InvDetail.prototype.TrxId_Change = function(sender, e) {
	this.TrxCd.val(jsTrxTypes[sender.value]);
};