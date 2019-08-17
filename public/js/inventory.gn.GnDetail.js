/**
 * Untuk mengelompokkan data Detail GN. Ini merupakan data detail yang berulang-ulang.
 * JavaScript is prototype based and doesn't know about classes therefore this way is the most similar way to class concept
 *
 * @param cloned
 * @param [data]
 * @constructor
 */
function GnDetail(cloned, data) {
    // Property Creation
    this.DivContainer = cloned;
    this.Id = cloned.find("#Id");
    this.LblUnit = cloned.find("#lblUnit");
    this.InvId = cloned.find("#InvId");
    this.Qty = cloned.find("#Qty");
    this.Price = cloned.find("#Price");
    this.Description = cloned.find("#Desc");

    // Initialization
	this.InvId.select2({
		placeholderOption: "first",
		allowClear: false,
		formatResult: this.formatOptionList,
		formatSelection: this.formatOptionResult
	});
    this.Qty.autoNumeric();
    this.Price.autoNumeric();
    if (data != undefined) {
        this.AssignData(data);
    }

    // Event Registration
    var self = this;    // Diperlukan reference ke instance object MrDetail karena pada event handler this sudah refer ke object yang invoke event
    this.InvId.change(function(e) { self.InvId_Change(this, e); });
    cloned.find("#btnDelete").click(function() {
        cloned.slideUp("fast", function() {
            $(this).parent().remove();
        });
    });

    cloned.show();
}

// Member declaration using prototype technique
GnDetail.prototype.AssignData = function(data) {
    this.Id.val(data.Id);
    this.InvId.select2("val", data.ItemId);
    this.InvId_Change(this.InvId[0], null);
    this.Qty.autoNumericSet(data.Qty);
    this.Price.autoNumericSet(data.Price);
    this.Description.val(data.ItemDescription);

    if (data.Id != null) {
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
GnDetail.prototype.InvId_Change = function(sender, e) {
    var data = items[sender.value];
    if (data == undefined) {
        this.LblUnit.text("");
    } else {
        this.LblUnit.text(data.UomCode);
    }
};

// Select2 function
GnDetail.prototype.formatOptionList = function(state) {
	var item = items[state.id];
	if (item == undefined) {
		return "-- PILIH BARANG --";
	}

	var originalOption = $(state.element);
	return '<div class="colCategory blue">' + originalOption.data("category") + '</div><div class="colCode blue">' + item.Code + '</div><div class="colName blue">' + item.Name + '</div>';
};

GnDetail.prototype.formatOptionResult = function(state) {
	var item = items[state.id];
	if (item == undefined) {
		return "-- PILIH BARANG --";
	}

	var originalOption = $(state.element);
	return '<div class="colCategory blue bold">' + originalOption.data("category") + '</div><div class="colCode blue bold">' + item.Code + '</div><div class="colName blue bold">' + item.Name + '</div>';
};