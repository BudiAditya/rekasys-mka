<?php

class PostgreSqlReader extends ReaderBase {

	/**
	 * Should return total rows of current reader / result set
	 *
	 * @return int
	 */
	public function GetNumRows() {
		return pg_num_rows($this->nativeReader);
	}

	/**
	 * Should return total field / column of current reader / result set
	 *
	 * @return int
	 */
	public function GetFieldCount() {
		return pg_num_fields($this->nativeReader);
	}

	/**
	 * Should return field cursor position
	 *
	 * @return int
	 */
	public function GetCurrentField() {
		// TODO: Implement GetCurrentField() method.
		throw new SqlException("PostgreSql", "PostgreSqlReader::GetCurrentField() not supported yet !", -1);
	}

	/**
	 * Fetching current row into index based + associative array and move reader cursor forward
	 *
	 * @return array
	 */
	public function FetchArray() {
		return pg_fetch_array($this->nativeReader);
	}

	/**
	 * Fetching current row into index based array and move reader cursor forward
	 *
	 * @return array
	 */
	public function FetchRow() {
		return pg_fetch_row($this->nativeReader);
	}

	/**
	 * Fetching current row into associative array and move reader cursor forward
	 *
	 * @return array
	 */
	public function FetchAssoc() {
		return pg_fetch_assoc($this->nativeReader);
	}

	/**
	 * Fetching current row into anonymous class object and move reader cursor forward
	 *
	 * @return mixed
	 */
	public function FetchObject() {
		return pg_fetch_object($this->nativeReader);
	}

	/**
	 * Fetching current field / column data. Result array may vary depend on native driver implementation
	 *
	 * @return array
	 */
	public function FetchField() {
		// TODO: Implement FetchField() method.
		throw new SqlException("PostgreSql", "PostgreSqlReader::FetchField() not supported yet !", -1);
	}

	/**
	 * Closing current reader and free memory usage
	 *
	 * @return void
	 */
	public function CloseReader() {
		pg_free_result($this->nativeReader);
	}
}
