<?php

/**
 * Untuk semua Accounting Controller Harus memanggil parent::Initialize() jika akan menggunakan Initialize();
 * HAL DIATAS WAJIB !!! AGAR validasi berjalan... Hal ini harus repot karena di PHP constructor tidak otomatis memanggil parent constructor. Itu berbahaya... jadi aja di declare final dan dibuat method pengganti yang berfungsi mirip
 *
 * Menggunakan class ini dapat memastikan jika user ybs harus mengeset periode akun maka data-data sudah ada dan apabila tidak ada akan di kick ke halaman set periode
 */
class AccountingController extends AppController {
	protected $forcePeriode;
	protected $accMonth;
	protected $accMonthName;
	protected $accYear;

	protected function Initialize() {
		$this->forcePeriode = $this->persistence->LoadState("force_periode");
		$this->accMonth = $this->persistence->LoadState("acc_month");
		$this->accYear = $this->persistence->LoadState("acc_year");
		if ($this->accMonth != null) {
			$monthNames = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			$this->accMonthName = $monthNames[$this->accMonth - 1];
		}

		if ($this->forcePeriode) {
			// Harus ada periode akuntansinya
			if ($this->accMonth == null || $this->accYear == null) {
				$this->persistence->SaveState("error", "Maaf anda harus memilih periode akuntansi terlebih dahulu.");
				redirect_url("main/set_periode");
				// Kill request...
				return;
			}
		}
	}
}

// End of file: accounting_controller.php
