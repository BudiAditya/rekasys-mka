<?php
require_once(MODEL . "trx_type_base.php");
/**
 * Concrete Class TrxType yang TIDAK ter-locked module code nya
 * BISA MENGAKSES SEMUA MODULE CODE
 */
class TrxType extends TrxTypeBase {
	// Semua Implementasi sama dengan yang sudah ada pada base class
	// Jadi tidak perlu tambahan apa-apa disini. Hanya membuat concrete class

	/**
	 * Karena class ini maka untuk membuat instance nya akan dikerjakan specific per derived classnya.
	 * Agar pasti maka method ini akan di define dan return type nya adalah derived class
	 *
	 * @return TrxType
	 */
	protected function CreateInstance() {
		return new TrxType();
	}
}

// End of File: trx_type.php
