var counter = 0;
/**
 * Untuk mengelompokkan data Detail Pr (Purchase Request). Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */
function PrDetail(cloned, data) {
    // Private variable
    this._counter = counter;    // Fix for access modified closure
    // Property Creation
    this.DivContainer = cloned;
    this.Id = cloned.find("#Id");
    this.Codes = cloned.find("#Codes");
    this.LblUnit = cloned.find("#lblUnit");
    this.MrDetailId = cloned.find("#MrDetailId");
    this.Category = cloned.find("#Category");
    this.InvId = cloned.find("#InvId");
    this.Qty = cloned.find("#Qty");
    this.Description = cloned.find("#Desc");
    this.Supplier1 = cloned.find("#Supplier1");
    this.Supplier2 = cloned.find("#Supplier2");
    this.Supplier3 = cloned.find("#Supplier3");
    this.Price1 = cloned.find("#Price1");
    this.Price2 = cloned.find("#Price2");
    this.Price3 = cloned.find("#Price3");
    this.Date1 = cloned.find("#Date1");
    this.Date2 = cloned.find("#Date2");
    this.Date3 = cloned.find("#Date3");
    this.SelectedSupplier = cloned.find("#SelectedSupplier");

    // Initialization
    this.Qty.autoNumeric();
    this.Price1.autoNumeric();
    this.Price2.autoNumeric();
    this.Price3.autoNumeric();
    this.Date1.attr("id", "Date1_" + this._counter);
    this.Date2.attr("id", "Date2_" + this._counter);
    this.Date3.attr("id", "Date3_" + this._counter);
    this.Date1.customDatePicker({ showOn: "focus" });
    this.Date2.customDatePicker({ showOn: "focus" });
    this.Date3.customDatePicker({ showOn: "focus" });
    if (data != undefined) {
        this.AssignData(data);
    }

    // Event Registration
    var self = this;    // Diperlukan reference ke instance object PrDetail karena pada event handler this sudah refer ke object yang invoke event
    this.Category.change(function(e) { self.Category_Change(this, e); });
    this.InvId.change(function(e) { self.InvId_Change(this, e); });
    cloned.find("#btnDelete").click(function() {
        cloned.slideUp("fast", function() {
            $(this).parent().remove();
        });
    });

    cloned.show();
    counter++;
}

// Member declaration using prototype technique
PrDetail.prototype.AssignData = function(data) {
    var temp = itemCodes[data.ItemId];
    if (temp == undefined) {
        alert("Invalid State ! Mohon ulangi proses dari awal ! Bila berulang hubungi system administrator");
        return;
    }
    var tokens = temp.split("|");   // Untuk dapat category Idnya kita harus refer ke barangnya

    this.Id.val(data.Id);
    this.Codes.val(temp);
    this.MrDetailId.val(data.MrDetailId);
    this.Category.val(tokens[3]);
    this.Category_Change(this.Category[0], null);
    this.InvId.val(data.ItemId);
    this.InvId_Change(this.InvId[0], null);
    this.Qty.autoNumericSet(data.Qty);
    this.Description.val(data.ItemDescription);
    this.Supplier1.val(data.SupplierId1);
    this.Supplier2.val(data.SupplierId2);
    this.Supplier3.val(data.SupplierId3);
    this.Price1.autoNumericSet(data.Price1);
    this.Price2.autoNumericSet(data.Price2);
    this.Price3.autoNumericSet(data.Price3);
    if (data.Date1 > 0) {
        this.Date1.datepicker("setDate",  new Date(data.Date1 * 1000));
    }
    if (data.Date2 > 0) {
        this.Date2.datepicker("setDate", new Date(data.Date2 * 1000));
    }
    if (data.Date3 > 0) {
        this.Date3.datepicker("setDate", new Date(data.Date3 * 1000));
    }
    this.SelectedSupplier.val(data.SelectedSupplier);

    if (data.Id != "" && data.Id != null) {
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
PrDetail.prototype.Category_Change = function(sender, e) {
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
PrDetail.prototype.InvId_Change = function(sender, e) {
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