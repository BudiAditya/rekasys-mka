<?php
require_once(MODEL . "ar/billing_schedule.php");
require_once(MODEL . "common/tax_rate_detail.php");
require_once(MODEL . "ar/trx_type.php");
require_once(MODEL . "master/lot.php");

class ArCalculator {
	const SPS_FILTER_DISABLE = -1;
	const SPS_FILTER_USER_SIGNED = 1;
	const SPS_FILTER_PROCESS_BANK = 2;

	/**
	 * Untuk meng-generate Service Rate daripada Tenant. Ini akan digunakan baik pemayaran berdasarkan rental (LOT) ataupun Payment Plan
	 * Service charge akan selalu ditagihkan apapun yang terjadi
	 *
	 * @param TenantMaster $tenantMaster
	 * @param bool $flagProporsional
	 * @return BillingSchedule[]
	 */
	public function GenerateByServiceRate(TenantMaster $tenantMaster, $flagProporsional = true) {
		$result = array();

		$lotSizes = array();
		foreach ($tenantMaster->TenantLotAssigns as $lotAssign) {
			$lotSizes[$lotAssign->LotId] = $lotAssign->LotSize;
		}

		foreach ($tenantMaster->TenantServiceCharges as $serviceCharge) {
			$freq = $serviceCharge->FreqNum;
			$day = date("d", $serviceCharge->StartDate);
			$month = date("n", $serviceCharge->StartDate);
			$year = date("Y", $serviceCharge->StartDate);
			$buffProporsional = 0;
			if ($flagProporsional && $day > 1) {
				$freq++;
			}

			// Persiapan untuk perhitungan ulang
			$buffSchedules = BillingSchedule::LoadByDebtorAndTransactionId($tenantMaster->Id, $serviceCharge->TrxId);
			$startFrom = 0;
			if ($flagProporsional && $day > 1 && count($buffSchedules) > 0) {
				$buffProporsional = $buffSchedules[0]->BaseAmount;
			}
			foreach ($buffSchedules as $schedule) {
				// Hanya akan proses billing schedule yang statusnya BILLED (=4), MANUAL (=5)
				if (!in_array($schedule->BillingStatusCode, array(4, 5))) {
					continue;
				}
				$startFrom = $schedule->SequenceNo + 1;
				$result[] = $schedule;
			}

			for ($i = $startFrom; $i < $freq; $i++) {
				$trxType = new \Ar\TrxType();
				$trxType->FindById($serviceCharge->TrxId);
				$lot = new Lot();
				$lot->FindById($serviceCharge->LotId);

				if ($flagProporsional && $day > 1) {
					$billDate = mktime(0, 0, 0, $month + $i, ($i == 0 ? $day : 1), $year);
					if ($i == 0) {
						// Bulan pertama pasti ada proporsional
						$jmlHari = date("t", $billDate);
						$selisihHari = ($jmlHari - $day) + 1;
						$baseAmount = $buffProporsional = round($selisihHari / $jmlHari * $serviceCharge->ServiceRate);
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					} else if ($i == ($freq - 1)) {
						// Bulan terakhir habiskan sisanya
						$baseAmount = $serviceCharge->ServiceRate - $buffProporsional;
						// Penagihan terakhir berhenti pada tanggal 1 hari sebelum selesai
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
					} else {
						// Normal rate
						$baseAmount = $serviceCharge->ServiceRate;
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					}
				} else {
					$billDate = mktime(0, 0, 0, $month + $i, $day, $year);
					$baseAmount = $serviceCharge->ServiceRate;
					$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
				}

				$billSchedule = new BillingSchedule();
				$billSchedule->DebtorTransactionId = $tenantMaster->Id;
				$billSchedule->LotId = $serviceCharge->LotId;
				$billSchedule->BillingDate = $billDate;
				// Untuk size lot harus gunakan yang dari lot assign karena ada kemungkinan berubah. Perubahan size lot akan di proses pada tahap berikutnya
				$billSchedule->Description = $serviceCharge->TrxCd . " - " . $trxType->Description . " " . $lot->LotNo . " (" . $lotSizes[$lot->Id] . " m2), Period : " . date("d-M-Y", $billDate) . " to " . date("d-M-Y", $billEndDate);
				$billSchedule->BaseAmount = $baseAmount;
				$billSchedule->TaxSchemeId = $serviceCharge->TaxschId;
				$billSchedule->SequenceNo = $i;
				$billSchedule->StartDate = $billDate;
				$billSchedule->EndDate = $billEndDate;
				$billSchedule->TrxId = $serviceCharge->TrxId;
				$billSchedule->TrxCd = $serviceCharge->TrxCd;

				// hitung pajaknya
				$this->TaxBillCalc($billSchedule, $serviceCharge->TaxschId);

				// Simpan ke buffer
				$result[] = $billSchedule;
			}
		}

		return $result;
	}

	/**
	 * Untuk generate billing schedule tenant berdasarkan Rental LOT.
	 * Ini hanya diproses jika mode pembayaran tenant adalah 2 (Rental MODE)
	 *
	 * @param TenantMaster $tenantMaster
	 * @param bool $flagProporsional
	 * @throws Exception
	 * @return BillingSchedule[]
	 */
	public function GenerateByRentalRate(TenantMaster $tenantMaster, $flagProporsional = true) {
		// generate billing schedule berdasarkan rental charges
		if ($tenantMaster->PaymentBase != 2) {
			throw new Exception("Generate by rental rate tapi kode bukan 2");
		}

		$result = array();

		foreach ($tenantMaster->TenantRentalCharges as $rentalCharge) {
			$freq = $rentalCharge->FreqNum;
			$day = date("d", $rentalCharge->StartDate);
			$month = date("n", $rentalCharge->StartDate);
			$year = date("Y", $rentalCharge->StartDate);
			$buffProporsional = 0;
			if ($flagProporsional && $day > 1) {
				$freq++;
			}

			for ($i = 0; $i < $freq; $i++) {
				$trxType = new \Ar\TrxType();
				$trxType->FindById($rentalCharge->TrxId);
				$lotS = new Lot();
				$lotS->FindById($rentalCharge->LotId);

				if ($flagProporsional && $day > 1) {
					$billDate = mktime(0, 0, 0, $month + $i, ($i == 0 ? $day : 1), $year);
					if ($i == 0) {
						// Bulan pertama pasti ada proporsional
						$jmlHari = date("t", $billDate);
						$selisihHari = $jmlHari - $day;
						$baseAmount = $buffProporsional = round($selisihHari / $jmlHari * $rentalCharge->RentRate);
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					} else if ($i == ($freq - 1)) {
						// Bulan terakhir habiskan sisanya
						$baseAmount = $rentalCharge->RentRate - $buffProporsional;
						// Penagihan terakhir berhenti pada tanggal 1 hari sebelum selesai
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
					} else {
						// Normal rate
						$baseAmount = $rentalCharge->RentRate;
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					}
				} else {
					$billDate = mktime(0, 0, 0, $month + $i, $day, $year);
					$baseAmount = $rentalCharge->RentRate;
					$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
				}

				$billSchedule = new BillingSchedule();
				$billSchedule->DebtorTransactionId = $tenantMaster->Id;
				$billSchedule->LotId = $rentalCharge->LotId;
				$billSchedule->BillingDate = $billDate;
				$billSchedule->Description = $rentalCharge->TrxCd . " - " . $trxType->Description . " " . $lotS->LotNo . ", Period : " . date("d-M-Y", $billDate) . " to " . date("d-M-Y", $billEndDate);
				$billSchedule->BaseAmount = $baseAmount;
				$billSchedule->TaxSchemeId = $rentalCharge->TaxschId;
				$billSchedule->SequenceNo = $i;
				$billSchedule->StartDate = $billDate;
				$billSchedule->EndDate = $billEndDate;
				$billSchedule->TrxId = $rentalCharge->TrxId;
				$billSchedule->TrxCd = $rentalCharge->TrxCd;

				// hitung pajaknya
				$this->TaxBillCalc($billSchedule, $rentalCharge->TaxschId);

				// buffer data
				$result[] = $billSchedule;
			}
		}

		return $result;
	}

	/**
	 * Untuk generate billing schedule berdasarkan payment plan yang sudah dibuat oleh user.
	 * Untuk payment plan jika ada 2 lot / lebih maka akan digabung sehingga LOT akan selalu NULL
	 *
	 * @param TenantMaster $tenantMaster
	 * @throws Exception
	 * @return BillingSchedule[]
	 */
	public function GenerateByPaymentPlan(TenantMaster $tenantMaster) {
		// generate billing schedule berdasarkan payment plan
		if ($tenantMaster->PaymentBase != 1) {
			throw new Exception("Generate by payment plan tapi kode bukan 1");
		}

		$result = array();

		// Untuk perhitungan pajak selalu menggunakan data dari rental LOT yang pertama
		// ToDo: Jika ada perbedaan pajak antara lot #1 dan #2 bagaimana ?
		$rentalCharge = $tenantMaster->TenantRentalCharges[0];
		$taxId = $rentalCharge->TaxschId;

		$userId = AclManager::GetInstance()->GetCurrentUser()->Id;
		foreach ($tenantMaster->TenantPaymentPlans as $paymentPlan) {
			$flagProporsional = $paymentPlan->IsProporsional;

			$freq = $paymentPlan->FreqNum;
			$day = date("d", $paymentPlan->StartDate);
			$month = date("n", $paymentPlan->StartDate);
			$year = date("Y", $paymentPlan->StartDate);
			$buffProporsional = 0;
			$buffTransactions = array();
			if ($flagProporsional && $day > 1) {
				$freq++;
			}

			// Persiapan untuk perhitungan ulang
			$totalAmount = $paymentPlan->TotalAmount;
			$buffSchedules = BillingSchedule::LoadByDebtorAndTransactionId($tenantMaster->Id, $paymentPlan->TrxId);
			$startFrom = 0;
			foreach ($buffSchedules as $schedule) {
				// Hanya akan proses billing schedule yang statusnya BILLED (=4), MANUAL (=5)
				// Proses akan mengurangi total amount
				if (!in_array($schedule->BillingStatusCode, array(4, 5))) {
					continue;
				}

				// Dikarenakan billing schedule tidak menyertakan jumlah pajak maka perhitungan ini juga tanpa pajak
				$totalAmount -= $schedule->BaseAmount;
				$startFrom = $schedule->SequenceNo + 1;
				$result[] = $schedule;
			}
			if ($paymentPlan->FreqNum == $startFrom) {
				// Tidak perlu hitung karena semua sudah selesai / 'tertagih' (baik manual / invoiced / pemutihan)
				continue;
			}
			$monthlyAmount = ceil($totalAmount / ($paymentPlan->FreqNum - $startFrom));

			for ($i = $startFrom; $i < $freq; $i++) {
				if ($flagProporsional && $day > 1) {
					$billDate = mktime(0, 0, 0, $month + $i, ($i == 0 ? $day : 1), $year);
					if ($i == 0) {
						// Bulan pertama pasti ada proporsional
						$jmlHari = date("t", $billDate);
						$selisihHari = $jmlHari - $day + 1;
						$baseAmount = $buffProporsional = round($selisihHari / $jmlHari * $monthlyAmount);
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					} else if ($i == ($freq - 1)) {
						// Bulan terakhir habiskan sisanya
						// Untuk kasus proporsional yang generate ulang tidak masalah jika yang proposional pertama sudah lunas karena $monthlyAmount akan terbagi rata
						$baseAmount = $monthlyAmount - $buffProporsional;
						// Penagihan terakhir berhenti pada tanggal 1 hari sebelum selesai
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
					} else {
						// Normal rate
						$baseAmount = $monthlyAmount;
						// Bulan kerikutnya tanggal 0 (trick buat cari pada bulan berjalan tanggal terakhirnya)
						$billEndDate = mktime(0, 0, 0, $month + $i + 1, 0, $year);
					}
				} else {
					$billDate = mktime(0, 0, 0, $month + $i, $day, $year);
					$baseAmount = $monthlyAmount;
					$billEndDate = mktime(0, 0, 0, $month + $i + 1, $day - 1, $year);
				}

				$billSchedule = new BillingSchedule();
				$billSchedule->CreatedById = $userId;
				$billSchedule->DebtorTransactionId = $tenantMaster->Id;
				$billSchedule->LotId = null;
				$billSchedule->BillingDate = $billDate;
				$suffix = $freq > 1 ? " Ke-" . ($i + 1) : "";
				if ($paymentPlan->AmtType == 2) {
					$billSchedule->Description = $paymentPlan->TrxCd . " - " . $paymentPlan->TrxDesc . " " . $paymentPlan->Persentase . " %" . $suffix;
				} else {
					$billSchedule->Description = $paymentPlan->TrxCd . " - " . $paymentPlan->TrxDesc . $suffix;
				}
				$billSchedule->BaseAmount = $baseAmount;
				$billSchedule->TaxSchemeId = $taxId;
				$billSchedule->SequenceNo = $i;
				$billSchedule->StartDate = $billDate;
				$billSchedule->EndDate = $billEndDate;
				$billSchedule->TrxId = $paymentPlan->TrxId;
				$billSchedule->TrxCd = $paymentPlan->TrxCd;

				// hitung pajaknya jika billing typenya bukan Deposit
				$key = $billSchedule->TrxId;
				if (array_key_exists($key, $buffTransactions)) {
					$transaction = $buffTransactions[$key];
				} else {
					$transaction = new \Ar\TrxType($key);
					$buffTransactions[$key] = $transaction;
				}
				if ($transaction->BillTypeId != 14) {
					$this->TaxBillCalc($billSchedule, $taxId);
				}

				// buffer data
				$result[] = $billSchedule;
			}
		}

		return $result;
	}

	/**
	 * Digunakan untuk menghitung secara lump sum untuk semua rental dan service charge
	 *
	 * @param TenantMaster $tenantMaster
	 * @return BillingSchedule[]
	 * @throws Exception
	 */
	public function GenerateByLumpSum(TenantMaster $tenantMaster) {
		// generate billing schedule berdasarkan lump sum
		if ($tenantMaster->PaymentBase != 3) {
			throw new Exception("Generate by lump sum tapi kode bukan 3");
		}

		$result = array();
		foreach ($tenantMaster->TenantRentalCharges as $rentalCharge) {
			$trxType = new \Ar\TrxType();
			$trxType->FindById($rentalCharge->TrxId);
			$lotS = new Lot();
			$lotS->FindById($rentalCharge->LotId);

			$billSchedule = new BillingSchedule();
			$billSchedule->DebtorTransactionId = $tenantMaster->Id;
			$billSchedule->LotId = $rentalCharge->LotId;
			$billSchedule->BillingDate = $rentalCharge->StartDate;
			$billSchedule->Description = $rentalCharge->TrxCd . " - " . $trxType->Description . " " . $lotS->LotNo . ", Period : " . date("d-M-Y", $rentalCharge->StartDate) . " to " . date("d-M-Y", $rentalCharge->EndDate);
			$billSchedule->BaseAmount = $rentalCharge->RentTotal;
			$billSchedule->TaxSchemeId = $rentalCharge->TaxschId;
			$billSchedule->SequenceNo = 1;
			$billSchedule->StartDate = $rentalCharge->StartDate;
			$billSchedule->EndDate = $rentalCharge->EndDate;
			$billSchedule->TrxId = $rentalCharge->TrxId;
			$billSchedule->TrxCd = $rentalCharge->TrxCd;

			// hitung pajaknya
			$this->TaxBillCalc($billSchedule, $rentalCharge->TaxschId);

			// buffer data
			$result[] = $billSchedule;
		}

		foreach ($tenantMaster->TenantServiceCharges as $serviceCharge) {
			$trxType = new \Ar\TrxType();
			$trxType->FindById($serviceCharge->TrxId);
			$lot = new Lot();
			$lot->FindById($serviceCharge->LotId);

			$billSchedule = new BillingSchedule();
			$billSchedule->DebtorTransactionId = $tenantMaster->Id;
			$billSchedule->LotId = $serviceCharge->LotId;
			$billSchedule->BillingDate = $serviceCharge->StartDate;
			$billSchedule->Description = $serviceCharge->TrxCd . " - " . $trxType->Description . " " . $lot->LotNo . ", Period : " . date("d-M-Y", $serviceCharge->StartDate) . " to " . date("d-M-Y", $serviceCharge->EndDate);
			$billSchedule->BaseAmount = $serviceCharge->ServiceTotal;
			$billSchedule->TaxSchemeId = $serviceCharge->TaxschId;
			$billSchedule->SequenceNo = 1;
			$billSchedule->StartDate = $serviceCharge->StartDate;
			$billSchedule->EndDate = $serviceCharge->EndDate;
			$billSchedule->TrxId = $serviceCharge->TrxId;
			$billSchedule->TrxCd = $serviceCharge->TrxCd;

			// hitung pajaknya
			$this->TaxBillCalc($billSchedule, $serviceCharge->TaxschId);

			// Simpan ke buffer
			$result[] = $billSchedule;
		}

		return $result;
	}

	public function TaxBillCalc(BillingSchedule $BillSchedule, $TaxSchId) {
		// hitung pajak
		$tarRateDetail = new TaxRateDetail();
		$details = $tarRateDetail->LoadByTaxRateId($TaxSchId);
		foreach ($details as $taxDetail) {
			if ($taxDetail->Deductable == 0) {
				//  mungkin PPN - penambah nilai
				$BillSchedule->TaxAmount += $BillSchedule->BaseAmount * ($taxDetail->TaxTarif / 100);
			} elseif ($taxDetail->Deductable == 1) {
				//  mungkin PPh - pengurang nilai
				$BillSchedule->DeductionAmount += $BillSchedule->BaseAmount * ($taxDetail->TaxTarif / 100);
			}
		}
	}

	/**
	 * Serupa dengan GenerateByPaymentPlan() tetapi ini untuk Surat Perjanjian Penjualan.
	 * Digabung pada ar_billing_schedule. Ternyata sudah ada flag pembedanya dan untuk melihat detailnya sudah dibedakan juga.
	 * Untuk Filter lihat ArCalculator::SPS_FILTER_XXX
	 *
	 * @param SpsMaster $spsMaster
	 * @param int $filterCode
	 * @return BillingSchedule[]
	 */
	public function GenerateBySpsPaymentPlan(SpsMaster $spsMaster, $filterCode) {
		// generate billing schedule berdasarkan payment plan SP Penjualan

		$result = array();
		foreach ($spsMaster->SpsPaymentPlans as $paymentPlan) {
			// Proses Filter terlebih dahulu....
			// PHP aneh... continue nya harus 2x... (continue pertama kayanya kena ke switch... jadi klo mau kena sampai foreach harus 2x)
			switch ($filterCode) {
				case ArCalculator::SPS_FILTER_USER_SIGNED:
					if (strtoupper($paymentPlan->TrxCd) != "JF") {
						continue 2;
					}
					break;
				case ArCalculator::SPS_FILTER_PROCESS_BANK:
					if (strtoupper($paymentPlan->TrxCd) == "JF") {
						continue 2;
					}
					break;
				case ArCalculator::SPS_FILTER_DISABLE:
					break;
			}

			// get total billing line by frequent
			$lFreq = $paymentPlan->FreqNum;
			$freq = $lFreq;

			$year = date("Y", $paymentPlan->StartDate);
			$month = date("n", $paymentPlan->StartDate);
			$day = date("j", $paymentPlan->StartDate);

			$planAmount = $paymentPlan->Amount;
			$netAmount = $paymentPlan->TotalAmount;

			// start generate billing schedule
			for ($i = 1; $i <= $freq; $i++) {
				$baseAmount = null;
				$baseAmount = $planAmount;

				$billSchedule = new BillingSchedule();
				$billSchedule->DebtorTransactionId = $spsMaster->Id;
				$billSchedule->LotId = null;
				$billSchedule->BillingDate = mktime(0, 0, 0, $month + $i - 1, $day, $year);
				if ($paymentPlan->AmtType == 2) {
					$billSchedule->Description = $paymentPlan->TrxCd . " - " . $paymentPlan->TrxDesc . " " . $paymentPlan->Persentase . "% Ke-" . $i;
				} else {
					$billSchedule->Description = $paymentPlan->TrxCd . " - " . $paymentPlan->TrxDesc;
				}
				$billSchedule->BaseAmount = $baseAmount;
				//$billSchedule->TaxAmount = 0;
				$billSchedule->DeductionAmount = 0;
				$billSchedule->TaxSchemeId = $paymentPlan->TaxSchId;
				$billSchedule->SequenceNo = $i;
				$billSchedule->StartDate = $billSchedule->BillingDate;
				$billSchedule->EndDate = mktime(0, 0, 0, $month + $i, $day - 1, $year);
				$billSchedule->IsDeleted = 0;
				$billSchedule->TrxId = $paymentPlan->TrxId;
				$billSchedule->TrxCd = $paymentPlan->TrxCd;
				$billSchedule->IsSalesTrx = true;

                // hitung pajaknya
                $this->TaxBillCalc($billSchedule, $paymentPlan->TaxSchId);

				// insert to table
				$result[] = $billSchedule;

				// Sedikit berbeda dari yang tenant maka disini untuk tanggal hari tidak di force ke tgl 1 bulan berikutnya
				$month++;
				$billDate = mktime(0, 0, 0, $month, $day, $year);
				$netAmount = $netAmount - $baseAmount;
			}
		}

		return $result;
	}
}
