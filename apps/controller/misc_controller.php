<?php

class MiscController extends AppController {
	protected function Initialize() {
		// TODO: Implement Initialize() method.
	}

	public function scan_controller() {
		$parents = scandir(CONTROLLER);

		$parentIdx = 0;
		foreach ($parents as $parent) {
			if ($parent == "." || $parent == "..") {
				continue;
			}

			$target = CONTROLLER . "/" . $parent;
			if (!is_dir($target)) {
				continue;
			}

			$parentIdx++;
			$menuIdx = 0;
			$contents = scandir($target);
			foreach ($contents as $file) {
				if ($file == "." || $file == "..") {
					continue;
				}

				$menuIdx++;
				$controller = str_replace("_controller.php", "", $file);

				$path = sprintf("%s.%s", $parent, $controller);
				printf("INSERT INTO sys_resource(menu_name, menu_seq, resource_name, resource_seq, resource_path) VALUES('$parent', $parentIdx, '$path', $menuIdx, '$path');");
				//print($path);
				print("<br />\n");
			}

		}
	}

	public function aho() {
		$buff = "94,95
97,98
99,100
101,102,103
104,105
107,108
120,121
116,117
118,119
124,125,126
127,128
132,133
134,135
138,139
140,141
142,143
144,145,146
147,148
149,150,151
158,159,160,161
162,163
164,165
167,168,169,170
172,173
174,175
176,177,178
181,182
183,184
191,192,193
194,195
196,197
199,200,201
211,212
213,214,215
216,217,218
226,230
231,232
242,243
249,250
251,252
254,255,256
264,265
276,285
304,305
314,315
318,319
323,324
325,326,327
338,339
340,341
342,343
345,346
347,348
351,362
352,361
408,425
409,410
416,417
450,451
457,458
462,463
477,478
483,484
497,498";
		$expectedIds = explode("\n", $buff);
		foreach ($expectedIds as $expectedId) {
			$tokens = explode(",", $expectedId);
			for ($i = 0; $i < count($tokens); $i++) {
				$tokens[$i] = $tokens[$i] - 89;
			}

			printf("%s<br />", implode(", ", $tokens));
		}

	}

	public function _trim() {
		$invalidTokens = array("PT.", "PT", "CV.", "CV", "BAPAK", "BPK.", "BPK", "BP.", "BP", "DR.", "DR", "DRA.", "IBU", "RM.", "TOKO.", "TOKO", "UD.", "UD", "YAYASAN", "IR.", "IR", "H.", "HI.", "HJ.");
		$raw = "";

		$names = explode("\n", $raw);
		foreach ($names as $name) {
			$name = strtoupper(trim($name));
			foreach ($invalidTokens as $token) {
				if (strpos($name, $token) === 0) {
					$name = trim(str_replace($token, "", $name));
				}
			}
			printf("%s<br />", $name);
		}
	}

	public function make_schedule_sps() {
		set_time_limit(3000);

		require_once(MODEL . "master/lot.php");
		require_once(LIBRARY . "ar_calculator.php");
		require_once(MODEL . "sm/sps_master.php");

		$sp = new SpsMaster();
		//$sps[] = $sp->LoadById(171);
		$sps = $sp->LoadAll();
		$lot = new Lot();
		$calc = new ArCalculator();

		foreach ($sps as $sp) {
			/** @var $sp SpsMaster */
			if ($sp->SpStatus != 2) {
				continue;
			}

			$sp->LoadLotAssign();
			$sp->LoadPaymentPlan();

			foreach ($sp->SpsLotAssign as $lotAssign) {
				$rs = $lot->UpdateStatus($lotAssign->LotId, $sp->DebtorId, 3);
				if ($rs != 1) {
					printf("Gagal update lot id: %s. Detail SPS: %s. rs = %s. Message: %s<br />", $lotAssign->LotId, $lotAssign->Id, $rs);
				}

			}

			$schedules = $calc->GenerateBySpsPaymentPlan($sp, ArCalculator::SPS_FILTER_DISABLE);
			foreach ($schedules as $billingSchedule) {
				$billingSchedule->CreatedById = 1;
				$rs = $billingSchedule->Insert();
				if ($rs != 1) {
					printf("Gagal insert schedule SPS: %s. rs = %s. Message: %s<br />", $sp->Id, $rs, $this->connector->GetErrorMessage());
				}
			}

			printf("Completed for SP: '%s'<br /><br />", $sp->SpNo);
		}

	}

	public function list_duplicate() {
		$raw =
"3,542,-1
3,4,58
3,216,-1
3,-1,279
3,-1,4
3,48,-1
3,4,55
3,4,248
3,541,44
3,219,-1
3,4,56
3,4,-1
3,415,-1
3,179,-1
5,-1,307
5,-1,289
5,-1,279
5,-1,294
4,-1,294
4,-1,290
4,-1,279";
		$duplicates = explode("\n", $raw);
		foreach ($duplicates as $duplicate) {
			$tokens = explode(",", $duplicate);
			$this->connector->CommandText = "SELECT a.* FROM cm_acc_detail AS a WHERE a.id = ?id";

			if ($tokens[1] != -1) {
				$this->connector->AddParameter("?id", $tokens[1]);
				$rs = $this->connector->ExecuteQuery();
				$debet = $rs->FetchAssoc();
			} else {
				$debet = null;
			}

			if ($tokens[2] != -1) {
				$this->connector->AddParameter("?id", $tokens[2]);
				$rs = $this->connector->ExecuteQuery();
				$kredit = $rs->FetchAssoc();
			} else {
				$kredit = null;
			}

			$this->connector->CommandText =
"SELECT a.*
FROM sys_trx_type AS a
WHERE a.entity_id = ?sbu
	AND IFNULL(a.acc_debit_id, -1) = ?debit
	AND IFNULL(a.acc_credit_id, -1) = ?credit";
			$this->connector->AddParameter("?sbu", $tokens[0]);
			$this->connector->AddParameter("?debit", $tokens[1]);
			$this->connector->AddParameter("?credit", $tokens[2]);

			$rs = $this->connector->ExecuteQuery();
			printf("Debet: %s, Kredit: %s<br />", $debet == null ? "[KAS/BANK]" : $debet["acc_no"], $kredit == null ? "[KAS/BANK]" : $kredit["acc_no"]);
			while ($row = $rs->FetchAssoc()) {
				printf("Kode: %s, Deskripsi: %s<br />", $row["code"], $row["description"]);
			}
			print("<br />");

		}
	}

	public function datepicker_test() {}
}

// End of file: misc_controller.php
