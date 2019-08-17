<?php
/**
 * Concrete class for Mysqli_Reader. This class will directly call native mysqli method
 */
class MysqliReader extends ReaderBase {

	/**
	 * Total record(s)
	 *
	 * @return int
	 */
	public function GetNumRows() {
		return $this->nativeReader->num_rows;
	}

	/**
	 * Total field(s)
	 *
	 * @return int
	 */
	public function GetFieldCount() {
		return $this->nativeReader->field_count;
	}

	/**
	 * Return field cursor position
	 *
	 * @return int
	 */
	public function GetCurrentField() {
		return $this->nativeReader->current_field;
	}

	/**
	 * Fetch current row data as indexed and associative array
	 *
	 * @return array
	 */
	public function FetchArray() {
		return $this->nativeReader->fetch_array();
	}

	/**
	 * Fetch current row data as indexed array
	 *
	 * @return array
	 */
	public function FetchRow() {
		return $this->nativeReader->fetch_row();
	}

	/**
	 * Fetch current row data as associative array
	 *
	 * @return array
	 */
	public function FetchAssoc() {
		return $this->nativeReader->fetch_assoc();
	}

	/**
	 * Fetching current row into anonymous class object and move reader cursor forward
	 *
	 * @return mixed
	 */
	public function FetchObject() {
		return $this->nativeReader->fetch_object();
	}

	/**
	 * Fetching current field / column data. Result array may vary depend on native driver implementation
	 *
	 * @return array
	 */
	public function FetchField() {
		return $this->nativeReader->fetch_field();
	}

	/**
	 * Closing current reader and free memory usage
	 *
	 * @return void
	 */
	public function CloseReader() {
		$this->nativeReader->close();
	}
}

// EOF: ./system/core/connector/mysqli/reader.php
