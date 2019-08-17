/**
 * Untuk mengelompokkan data Detail Mr. Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */
function MrDetail(cloned, data) {
    // Property Creation
    this.DivContainer = cloned;
    this.Id = cloned.find("#Id");
    this.Codes = cloned.find("#Codes");
    this.LblUnit = cloned.find("#lblUnit");
    this.Category = cloned.find("#Category");
    this.InvId = cloned.find("#InvId");
    this.UnitId = cloned.find("#UnitId");
    this.Qty = cloned.find("#Qty");
    this.Description = cloned.find("#Desc");

    // Initialization
    this.Qty.autoNumeric();
    if (data != undefined) {
        this.AssignData(data);
    }

    // Event Registration
    var self = this;    // Diperlukan reference ke instance object MrDetail karena pada event handler this sudah refer ke object yang invoke event
    this.Category.change(function(e) { self.Category_Change(this, e); });
    this.InvId.change(function(e) { self.InvId_Change(this, e); });
    cloned.find("#btnDelete").click(function() {
        cloned.slideUp("fast", function() {
            $(this).parent().remove();
        });
    });

    cloned.show();
}

// Member declaration using prototype technique
MrDetail.prototype.AssignData = function(data) {
    var temp = itemCodes[data.ItemId];
    if (temp == undefined) {
        alert("Invalid State ! Mohon ulangi proses dari awal ! Bila berulang hubungi system administrator");
        return;
    }
    var tokens = temp.split("|");   // Untuk dapat category Idnya kita harus refer ke barangnya

    this.Id.val(data.Id);
    this.Codes.val(temp);
    this.Category.val(tokens[3]);
    this.Category_Change(this.Category[0], null);
    this.InvId.val(data.ItemId);
    this.UnitId.val(data.UnitId);
    this.InvId_Change(this.InvId[0], null);
    this.Qty.autoNumericSet(data.RequestedQty);
    this.Description.val(data.ItemDescription);

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

/**
 * @param sender
 * @param e
 * @event
 */
MrDetail.prototype.Category_Change = function(sender, e) {
    if (sender.value == "") {
        this.InvId.html('<option value="">-- MOHON PILIH KATEGORI DAHULU --</option>');
        this.Codes.val("");
        this.LblUnit.text("");
    } else {
        this.InvId.html(items[sender.value]);
    }
};

/**
 * @param sender
 * @param e
 * @event
 */
MrDetail.prototype.InvId_Change = function(sender, e) {
    var data = itemCodes[sender.value];
    if (data == undefined) {
        this.Codes.val("");
        this.LblUnit.text("");
    } else {
        var tokens = data.split("|");
        this.Codes.val(data);
        this.LblUnit.text(tokens[2]);
    }
};