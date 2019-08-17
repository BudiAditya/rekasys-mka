<?php
class BkmController extends AppController {
	/** @var Dispatcher */
	private $dispatcher;
	/** @var Router */
	private $router;
	/** @var RouteData */
	private $routeData;
	private $namedParameters;

	protected function Initialize() {
//		parent::Initialize();
//		// Paksa Lock ke dokumen BKM
//		$this->lockDocId = 3;

		// Ini akan digunakan untuk execute Controller Cashbook.
		$this->dispatcher = Dispatcher::CreateInstance();
		// Sedangkan ini akan digunakan untuk mengambil parameter-parameter yang dikirim melalui URL
		$this->router = Router::GetInstance();
		$this->routeData = $this->router->GetRouteData();

		// Paksa Lock via Named Parameters. Pada CashBookController::Initialize() sudah ada script untuk mengambil nilai ini
		$this->namedParameters = $this->routeData->NamedParameters;
		$this->namedParameters["lockDocId"] = 3;

		// Tidak perlu re-mapping karena kita sudah tahu method mana yang akan di execute
		$this->dispatcher->Dispatch("cashbook", $this->routeData->MethodName, $this->routeData->Parameters, $this->namedParameters, "accounting", true);
	}

	// Semua method tidak perlu di re-declare sudah ada pada base class
	// Berbeda dari BKK yang tidak extends sehingga harus re-declare semua fungsi-fungsi yang ada dan di mapping pakai Dispatcher.
	// Benefit Tehnik Ini:
	// * Tidak perlu redeclare nama function
	// * Jika ada penambahan fungsi pada CashBookController secara otomatis ada disini
	// Drawback Tehnik Ini:
	// * Pada CashBookController harus memaksakan menggunakan VIEW CashBookController dan SuppressNextSequence agar aman
	//   NOTE: Jika tidak memaksakan VIEW maka akan menggunakan VIEW BkkController yang tidak ada sehingga hasilnya BLANK
	//
	// Karena ada perubahan framework maka tehnik extend tidak bisa digunakan sementara waktu karena RenderView memerlukan controller yang sudah terload
	// Perubahan framework : Dispatcher::CreateInstance() akan membuat instance baru yang kosong sehingga tidak ada controller yang terload pasti ERROR !
}

// End of file: bkm_controller.php
