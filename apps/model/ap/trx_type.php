<?php
namespace Ap;

use TrxTypeBase;

require_once(MODEL . "trx_type_base.php");
/**
 * Concrete Class TrxType yang ter-locked module code nya
 * LOCKED MODULE CODE: 'AP'
 */
class TrxType extends \TrxTypeBase {
	// Kita sudah siapkan untuk meng-lock module di constructor
	public function __construct() {
		parent::__construct();

		$this->lockModuleId = 2;
	}

	/**
	 * Karena class ini maka untuk membuat instance nya akan dikerjakan specific per derived classnya.
	 * Agar pasti maka method ini akan di define dan return type nya adalah derived class
	 *
	 * @return TrxType
	 */
	protected function CreateInstance() {
		// Ingat walau disini sama seperti yang lain hanya return new TrxType()
		// Yang ini class sudah berada pada namespace Ap;
		return new TrxType();
	}
}

// End of File: trx_type.php
