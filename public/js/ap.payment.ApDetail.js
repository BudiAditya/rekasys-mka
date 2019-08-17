/**
 * Untuk mengelompokkan data Detail Payment AP. Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */
function ApDetail(cloned, data) {
    // Property Creation
    this.DivContainer = cloned;
	this.Id = cloned.find("#Id");
    this.LblNote = cloned.find("#lblNote");
    this.InvoiceId = cloned.find("#InvoiceId");
    this.InvoiceNo = cloned.find("#InvoiceNo");
    this.GnId = cloned.find("#GnId");
    this.Amount = cloned.find("#Amount");

    // Initialization
    this.Amount.autoNumeric();
    if (data != undefined) {
        this.AssignData(data);
    }

    // Event Registration
    var self = this;    // Diperlukan reference ke instance object ApDetail karena pada event handler this sudah refer ke object yang invoke event
    this.InvoiceId.change(function(e) { self.InvoiceId_Change(this, e); });
    this.GnId.change(function(e) { self.GnId_Change(this, e); });
    cloned.find("#btnDelete").click(function() {
        cloned.slideUp("fast", function() {
            $(this).parent().remove();
        });
    });

    cloned.show();
}

// Member declaration using prototype technique
ApDetail.prototype.AssignData = function(data) {
    this.InvoiceId.val(data.InvoiceId);
    this.InvoiceNo.val(data.InvoiceNo);
    this.GnId.val(data.GnId);
    this.Amount.autoNumericSet(data.Amount);

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
ApDetail.prototype.InvoiceId_Change = function(sender, e) {
    if (sender.value == "") {
        this.LblNote.text("[Mohon memilih Invoice/GN terlebih dahulu]");
        this.InvoiceId.val("");
        this.Amount.autoNumericSet(0);
    } else {
        var invoice = jsUnPaidInvoices[sender.value];
        if (invoice == undefined) {
            alert("Data invoice tidak ditemukan mohon periksa kembali");
            this.DivContainer.parents("li")[0].remove();
        } else {
            // Invalidate GN
            this.GnId.val("");

            this.LblNote.text("Total Amount : Rp. " + $.autoNumeric.Format(null, invoice.TotalAmount) + " Total Pembayaran : Rp. " + $.autoNumeric.Format(null, invoice.TotalPaid) + " Sisa : Rp. " + $.autoNumeric.Format(null, invoice.Remainder));
            this.InvoiceNo.val(invoice.DocumentNo);
            this.Amount.autoNumericSet(invoice.Remainder);
        }
    }
};

/**
 * @param sender
 * @param e
 * @event
 */
ApDetail.prototype.GnId_Change = function(sender, e) {
    if (sender.value == "") {
        this.LblNote.text("[Mohon memilih Invoice/GN terlebih dahulu]");
        this.InvoiceId.val("");
        this.Amount.autoNumericSet(0);
    } else {
        var gn = jsUnPaidGns[sender.value];
        if (gn == undefined) {
            alert("Data GN tidak ditemukan mohon periksa kembali");
            this.DivContainer.parents("li")[0].remove();
        } else {
            // Invalidate Invoice
            this.InvoiceId.val("");

            this.LblNote.text("Total Amount : Rp. " + $.autoNumeric.Format(null, gn.TotalAmount) + " Total Pembayaran : Rp. " + $.autoNumeric.Format(null, gn.TotalPaid) + " Sisa : Rp. " + $.autoNumeric.Format(null, gn.Remainder));
            this.InvoiceNo.val(gn.DocumentNo);
            this.Amount.autoNumericSet(gn.Remainder);
        }
    }
};