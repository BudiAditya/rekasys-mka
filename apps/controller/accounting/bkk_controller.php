<?php

/**
 * BkkController class ini merupakan wrapper untuk CashbookController.
 * Pada module cashbook BKK dan BKM dijadikan satu sehingga untuk manajemen ACL-nya tidak dapat dipisah.
 * Agar ACL nya dapat dipisah maka controllernya pun harus dibedakan.
 *
 * Class ini akan selalu meng-execute class CashbookController dengan bantuan class Dispatcher
 */
class BkkController extends AppController {
	/** @var Dispatcher */
	private $dispatcher;
	/** @var Router */
	private $router;
	/** @var RouteData */
	private $routeData;
	private $namedParameters;

	protected function Initialize() {
		// Ini akan digunakan untuk execute Controller Cashbook.
		$this->dispatcher = Dispatcher::CreateInstance();
		// Sedangkan ini akan digunakan untuk mengambil parameter-parameter yang dikirim melalui URL
		$this->router = Router::GetInstance();
		$this->routeData = $this->router->GetRouteData();

		// Paksa Lock via Named Parameters. Pada CashBookController::Initialize() sudah ada script untuk mengambil nilai ini
		$this->namedParameters = $this->routeData->NamedParameters;
		$this->namedParameters["lockDocId"] = 2;

		// Tidak perlu re-mapping karena kita sudah tahu method mana yang akan di execute
		$this->dispatcher->Dispatch("cashbook", $this->routeData->MethodName, $this->routeData->Parameters, $this->namedParameters, "accounting", true);
	}
}

// End of file: bkk_controller.php
