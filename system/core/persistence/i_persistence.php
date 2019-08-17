<?php
interface IPersistence {
	/**
	 * Return persistence Unique Identifier. Should be different between user request
	 *
	 * @abstract
	 * @return string
	 */
	public function GetPersistenceId();

	/**
	 * Destroying the whole persistence (only affecting current request)
	 *
	 * @abstract
	 * @return void
	 */
	public function DestroyPersistence();

	/**
	 * Removing data from persistence storage
	 *
	 * @abstract
	 * @param string $name
	 * @return void
	 */
	public function DestroyState($name);

	/**
	 * Load data from persistence storage
	 *
	 * @abstract
	 * @param string $name
	 * @return mixed
	 */
	public function LoadState($name);

	/**
	 * Save data into persistence storage
	 *
	 * @abstract
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function SaveState($name, $value);

	/**
	 * @abstract
	 * @param string $name
	 * @return bool
	 */
	public function StateExists($name);
}

// EOF: ./system/core/persistence/i_persistence.php
