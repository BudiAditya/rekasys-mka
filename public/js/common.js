/**
 * Init semua basic function disini seperti:
 * - Automatic focus for first textbox element
 * - Change all anchor, button, submit element with button class into jQuery UI button
 * - Common dialog box(es)
 * - Handler next focus by enter (simplified calling for registering next focus by enter)
 * - Custom datepicker()
 */

var _lblRemaining;
var _timerHandler;

$(document).ready(function() {
	$(":text:first").focus();
	_lblRemaining = $("#lblRemaining");

	if (!$.ui) {
		// JQuery UI not loaded....
		return;
	}

	// Require jQuery UI to be loaded !
	//$("a.button").button();		// Anchor element
	//$("button").button();		// <button>
	//$(":submit").button();		// <input type="submit">

	$("#infoDialog").dialog({
		autoOpen: false
		, buttons: {
			"OK": function() { $(this).dialog("close"); }
		}
		, draggable: false
		, hide: "fade"
		, modal: true
		, resizable: false
		, show: "fade"
		, width: 600
	});

	$("#errorDialog").dialog({
		autoOpen: false
		, buttons: {
			"OK": function() { $(this).dialog("close"); }
		}
		, draggable: false
		, hide: "fade"
		, modal: true
		, resizable: false
		, show: "fade"
		, width: 600
	});

	$("#confirmDialog").dialog({
		autoOpen: false
		, buttons: { }	// Will dynamically created....
		, draggable: false
		, hide: "fade"
		, modal: true
		, resizable: false
		, show: "fade"
		, width: 600
	});

	$("#autoLogOutDialog").dialog({
		autoOpen: false
		, buttons: {
			"Keep Me Logged In": function(e) { $(this).dialog("close"); } // Ini akan otomatis memanggil KeepMeLoggedIn() karena ada bind saat dialog close
			, "Logout Now": function(e) { LogoutNow(); }
		}
		, closeOnEscape: false
		, draggable: false
		, hide: "fade"
		, modal: true
		, resizable: false
		, show: "fade"
		, title: "Auto Logout Confirmation"
		, width: 600

		, close: function() { KeepMeLoggedIn(); }
	});


	if (!$.fn.idleTimer) {
		return;
	}

	$(document).idleTimer({
		timeout: 300 * 1000 // 5 Menit...
		, onSleep: function(sender) {
			$(sender).idleTimer("stop"); // Matiin timer
			$("#autoLogOutDialog").dialog("open"); // Buka dialog
			LogoutCountdown(30); // countdown untuk auto logout (method ini recursive)
		}
	}).idleTimer("start");
});

function RegisterNextFocusByEnter(currentElementId, nextElementId) {
	if (document.getElementById(currentElementId) == undefined) {
		alert("RegisterNextFocusByEnter FAILED ! Source element not found ! Source: " + currentElementId);
		return;
	}
	if (document.getElementById(nextElementId) == undefined) {
		alert("RegisterNextFocusByEnter FAILED ! Destination element not found ! Destination: " + nextElementId);
		return;
	}

	$("#" + currentElementId).keypress(function(e) {
		if (e.keyCode != 13) {
			return true;
		}

		document.getElementById(nextElementId).focus();
		return false;
	});
}

function RegisterFormSubmitByEnter(elementId, formId) {
	if (document.getElementById(elementId) == undefined) {
		alert("RegisterNextFocusByEnter FAILED ! Source element not found ! Source: " + elementId);
		return;
	}
	if (document.getElementById(formId) == undefined) {
		alert("RegisterNextFocusByEnter FAILED ! FORM element not found ! FORM: " + formId);
		return;
	}

	$("#" + elementId).keypress(function(e) {
		if (e.keyCode == 13) {
			document.getElementById(formId).submit();
		}
	});
}

function BatchFocusRegister(elements) {
	var max = elements.length - 1;
	if (max < 1) {
		return;
	}
	for (var i = 0; i < max; i++) {
		RegisterNextFocusByEnter(elements[i], elements[i + 1]);
	}
}

function ConfirmDelete(anchor, text) {
	// Prepare fo fallback
	if ($.ui) {
		ShowConfirm("Konfirmasi Penghapusan Data", "Apakah anda yakin mau menghapus: " + text + " ?\n\nKlik 'OK' untuk mengkonfirmasi penghapusan data.", function() {
			window.location = anchor.href;
		});
		return false;
	} else {
		// Fallback to native confirm dialog
		return confirm("Apakah anda yakin mau menghapus: " + text + " ?\n\nKlik 'OK' untuk mengkonfirmasi penghapusan data.");
	}
}

function ConfirmApprove(anchor, text) {
	// Prepare fo fallback
	if ($.ui) {
		ShowConfirm("Konfirmasi Approval Data", "Apakah anda yakin mau meng-approve: " + text + " ?\n\nKlik 'OK' untuk mengkonfirmasi approval data.", function() {
			window.location = anchor.href;
		});
		return false;
	} else {
		// Fallback to native confirm dialog
		return confirm("Apakah anda yakin mau meng-approve: " + text + " ?\n\nKlik 'OK' untuk mengkonfirmasi approval data.");
	}
}

function LogoutCountdown(remainingTime) {
	var minutes = parseInt(remainingTime / 60);
	var seconds = remainingTime % 60;
	if (minutes < 10) {
		minutes = "0" + minutes;
	}
	if (seconds < 10) {
		seconds = "0" + seconds;
	}
	_lblRemaining.text(minutes + ":" + seconds);

	if (remainingTime == 0) {
		// Oppsss sudah habis countdownnya
		LogoutNow(true);
	} else {
		// Recursive...
		remainingTime--;
		_timerHandler = setTimeout("LogoutCountdown(" + remainingTime + ")", 1000);
	}
}

function KeepMeLoggedIn() {
	clearTimeout(_timerHandler);
	$(document).idleTimer("start");
}

function LogoutNow(auto) {
	window.location = "/hris-renewal/home/logout?auto=1";
}


function ShowInfo(title, message, afterClose) {
	if (!$.ui) {
		// JQuery UI not loaded....
		alert("jQuery UI not Loaded !");
		return;
	}

	var div = $("#infoDialog");
	// Hwee harus di unbind... kayanya si bind itu operatornya += bukan = (cape dee... jadi add terus)
	div.unbind("dialogclose");

	div.dialog("option", "title", title);
	message = message.replace(/\n|\\n/gi, "<br />");
	div.find("#infoMessage").html(message);
	div.dialog("open");


	if (afterClose == undefined) {
		div.bind("dialogclose", function(event, ui) { });
		return;		// Nothing to do after dialog close !
	}

	var type = typeof afterClose;
	switch (type) {
		case "function":
			div.bind("dialogclose", function(event, ui) {
				afterClose(ui, event);
			});
			break;
		case "string":
			div.bind("dialogclose", function(event, ui) {
				element = document.getElementById(afterClose);
				element.focus();
			});
			break;
		default:
			div.bind("dialogclose", function(event, ui) {
				alert("Fatal Script Error ! Unknown afterClose Type: " + type);
			});
			break;
	}
}

function ShowError(title, message, afterClose) {
	if (!$.ui) {
		// JQuery UI not loaded....
		alert("jQuery UI not Loaded !");
		return;
	}

	var div = $("#errorDialog");
	// Hwee harus di unbind... kayanya si bind itu operatornya += bukan = (cape dee... jadi add terus)
	div.unbind("dialogclose");

	div.dialog("option", "title", title);
	message = message.replace(/\n|\\n/gi, "<br />");
	div.find("#errorMessage").html(message);
	div.dialog("open");

	if (afterClose == undefined) {
		div.bind("dialogclose", function(event, ui) { });
		return;		// Nothing to do after dialog close !
	}

	var type = typeof afterClose;
	switch (type) {
		case "function":
			div.bind("dialogclose", function(event, ui) {
				afterClose(ui, event);
			});
			break;
		case "string":
			div.bind("dialogclose", function(event, ui) {
				element = document.getElementById(afterClose);
				element.focus();
			});
			break;
		default:
			div.bind("dialogclose", function(event, ui) {
				alert("Fatal Script Error ! Unknown afterClose Type: " + type);
			});
			break;
	}
}

function ShowConfirm(title, message, onConfirmed, onCancelled) {
	if (!$.ui) {
		// JQuery UI not loaded....
		alert("jQuery UI not Loaded !");
		return;
	}

	var temp = typeof onConfirmed;
	if (temp !== "function") {
		alert("Third parameter of ShowConfirm MUST BE instance of function !");
		return;
	}

	temp = typeof onCancelled;
	if (temp != "undefined") {
		if (temp !== "function") {
			alert("Fourth parameter of ShowConfirm MUST BE instance of function if value given !");
			return;
		}
	} else {
		onCancelled = null;
	}

	var div = $("#confirmDialog");
	div.dialog("option", "title", title);
	message = message.replace(/\n|\\n/gi, "<br />");
	div.find("#confirmMessage").html(message);

	// Set appropriate handler now....
	div.dialog("option", "buttons", {
		"OK": function() { $(this).dialog("close"); onConfirmed(); }
		, "Cancel": function() { $(this).dialog("close"); if (onCancelled != null) { onCancelled(); } }
	});

	div.dialog("open");
}

// Anonymous method for extending jQuery
// We want to add customDatepicker()
(function($) {
	var _customDatePickerCounter = 0;
	// HACK: Karena customDatePicker menggunakan hidden field intern dan ada kalanya si hidden fild itu terbuat secara dynamic maka
	//		 ada kemungkinan ketika set date object hidden fieldnya belum ada (belum ter-attach) pada body HTML tetapi kita sudah ada reference-nya
	$.extend($.datepicker, {
		__setDate: $.datepicker._setDate,
		_setDate: function(inst, date, noChange) {
			this.__setDate(inst, date, noChange);
			console.log("[hacked] $.datepicker.__setDate() called ! Now injecting date value into hidden field");
			var textDate = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
			inst.settings.hiddenField.val(textDate);
		}
	});

	$.fn.customDatePicker = function(options) {
		var defaults = {
			// jQueryUi DatePicker
			altFormat: "yy-mm-dd"
			, altField: null	// Will be dynamically created
			//, buttonImage: "/mega-parking-server/public/images/calendar.png"
			, buttonImageOnly: false
			, changeMonth: true
			, changeYear: true
			, dateFormat: "dd-mm-yy"
			, showOn: "both"
			, yearRange: "c-2:c+2"
			// Custom Properties
			, phpDate: null
			, hiddenField: null
		};

		$(this).each(function (idx, ele) {
			var settings = $.extend({}, defaults, options);
			var txt = $(ele);

			_customDatePickerCounter++;
			// Gw tetep mau hack agar ada hidden input yang dikirim ke server
			if (settings.altField == null) {
				var id = "dateFor_" + txt.attr("id") + "_rnd_" + _customDatePickerCounter;
				settings.hiddenField = $('<input type="hidden" />').attr("name", txt.attr("name")).attr("id", id);
				txt.after(settings.hiddenField).removeAttr("name");
				// Paksa agar pakai hidden field
				settings.altField = "#" + id;
			} else {
				settings.hidden = $(settings.altField);
			}

			txt.datepicker(settings);
			var temp = ele.value.trim();
			if (settings.phpDate != null) {
				txt.datepicker("setDate", new Date(settings.phpDate * 1000));
			} else if (temp != "") {
				temp = temp.replace(/-/gi, "");
				if (temp.length != 8 && temp.length != 6) {
					this.value = "";
					return;
				}

				// OK I assume everything is GREEN
				var dd = parseInt(temp.substr(0, 2), 10);
				var mm = parseInt(temp.substr(2, 2), 10);
				var yy = parseInt(temp.substr(4), 10);

				if (yy < 100) {
					yy += 2000;
				}

				this.value = (dd < 10 ? "0" + dd : dd) + "-" + (mm < 10 ? "0" + mm : mm)  + "-" + yy;
				settings.hiddenField.val(yy + "-" + mm + "-" + dd);
			}
		});

		// allow jQuery chaining...
		return this;
	}
})(jQuery);

// -- new function add by budi - 20120702

// JScript source code
/*  How to use
 1. Include datediff library by adding following line in head section of your HTML file
 <script src="datediff.js"></script>

 2. Place following code right after first one
 <script>
 var curDate=new Date();
 startDate="2006-01-17";
 endDate="2006-03-11";
 curDate.DateDiff({interval:"d",date1:startDate,date2:endDate});
 alert(curDate.difference);
 </script>
 */

var DayName=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];


var oneMinute=1000*60;

var intervalObject=new Object();
intervalObject["yyyy"]={units:1000*60*60*24*365,measure:"year"};
intervalObject["m"]={units:1000*60*60*24*30,measure:"month"};
intervalObject["d"]={units:1000*60*60*24,measure:"day"};
intervalObject["Q"]={units:intervalObject["m"].units*3,measure:"quarter"};
intervalObject["H"]={units:oneMinute*60,measure:"hour"};
intervalObject["N"]={units:oneMinute,measure:"minute"};
intervalObject["S"]={units:1000,measure:"second"};


function DateDiff(dateAddObj){
	this.interval=dateAddObj.interval;
	this.date1=dateAddObj.date1;
	this.date2=dateAddObj.date2;
	this.calculate=calculate;
	this.calculate();
}

Date.prototype.DateDiff=DateDiff;




function calculate(){
	var paramDate1=new String(this.date1);
	splitDate1=paramDate1.split("-");
	paramDateYear1=splitDate1[0];
	paramDateMonth1=splitDate1[1]-1;
	paramDateDay1=splitDate1[2];
	if(paramDateMonth1>12){
		alert("Invalid Month!");
		return false;
	}
	if(paramDateDay1>31){
		alert("Invalid Day!");
		return false;
	}


	var paramDate2=new String(this.date2);
	splitDate2=paramDate2.split("-");
	paramDateYear2=splitDate2[0];
	paramDateMonth2=splitDate2[1]-1;
	paramDateDay2=splitDate2[2];
	if(paramDateMonth2>12){
		alert("Invalid Month!");
		return false;
	}
	if(paramDateDay2>31){
		alert("Invalid Day!");
		return false;
	}


	var paramDate1Object=new Date(paramDateYear1,paramDateMonth1,paramDateDay1);
	paramDate1Object.setHours(0);
	paramDate1Object.setMinutes(0);
	paramDate1Object.setSeconds(0);
	//paramDate1Object.getTimezoneOffset() * oneMinute;
	var paramDate1ObjectTime=paramDate1Object.getTime();


	var paramDate2Object=new Date(paramDateYear2,paramDateMonth2,paramDateDay2);
	paramDate2Object.setHours(0);
	paramDate2Object.setMinutes(0);
	paramDate2Object.setSeconds(0);
	var paramDate2ObjectTime=paramDate2Object.getTime();

	if(paramDate2Object>paramDate1Object){
		DSTAdjust=(paramDate2Object.getTimezoneOffset() - paramDate1Object.getTimezoneOffset()) * oneMinute;

	}
	else{
		DSTAdjust=(paramDate1Object.getTimezoneOffset() - paramDate2Object.getTimezoneOffset()) * oneMinute;
	}

	if(typeof intervalObject[this.interval]!="undefined"){
		if(typeof intervalObject[this.interval].units=="undefined"){
			alert("Interval is invalid!");
			return false;
		}

		var diff=Math.abs(paramDate2ObjectTime-paramDate1ObjectTime) - DSTAdjust;
		var timeDiff=Math.floor(diff/intervalObject[this.interval].units);
		if(timeDiff>1){
			var rname=intervalObject[this.interval].measure + "s";
		}
		else{
			var rname=intervalObject[this.interval].measure;
		}

		this.difference=parseInt(timeDiff);
	}
	else{
		this.difference="Wrong format of interval!";

	}

}


/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */
// Declaring valid date character, minimum year and maximum year
var dtCh= "-";
var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
	for (i = 0; i < s.length; i++){
		// Check that current character is number.
		var c = s.charAt(i);
		if (((c < "0") || (c > "9"))) return false;
	}
	// All characters are numbers.
	return true;
}

function stripCharsInBag(s, bag){
	var i;
	var returnString = "";
	// Search through string's characters one by one.
	// If character is not in bag, append to returnString.
	for (i = 0; i < s.length; i++){
		var c = s.charAt(i);
		if (bag.indexOf(c) == -1) returnString += c;
	}
	return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
	// EXCEPT for centurial years which are not also divisible by 400.
	return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
	}
	return this
}

function isDate(dtStr){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strDay=dtStr.substring(0,pos1)
	var strMonth=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert("The date format should be : dd-mm-yyyy")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert("Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert("Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert("Please enter a valid date")
		return false
	}
	return true
}

// -- eof new functions
