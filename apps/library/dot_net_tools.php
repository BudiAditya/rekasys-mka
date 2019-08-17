<?php
/**
 * Class DotNetTools
 *
 * Contains any concept / method(s) that ported from microsoft .net. Thanks microsoft for some useful method or concept ^_^
 */
class DotNetTools {
	/**
	 * PORTED from LINQ concept Enumerator.Find()
	 *
	 * Hmm... gw ga tau konsep ini di PHP ada atau tidak... yang pasti ini gw bawa dari .net (Find Predicate)
	 * Berfungsi untuk mencari data dalam kumpulan array yang mana kriteria pencariannya fleksibel.
	 *
	 * Teori :
	 * 1. Parameter pertama (array) akan di looping
	 * 2. Masing-masing datanya akan di check dengan fungsi / anonymous function daripada parameter 2
	 * 3. Jika function tersebut return true maka itu adalah data yang diminta dan langsung return
	 *
	 * @param array $objects
	 * @param Closure $method
	 * @return null|mixed
	 */
	public static function FindInArray(array &$objects, $method) {
		foreach ($objects as $object) {
			if ($method($object)) {
				// Data ketemu lsg return.
				return $object;
			}
		}

		// Data tidak ketemu
		return null;
	}

	/**
	 * Digunakan untuk meng-sum field tertentu dari suatu object yang terdapat pada array
	 * PORTED from .net concept ^_^
	 *
	 * @param array $objects
	 * @param Closure $method
	 * @return int|float
	 */
	public static function ArraySum(array &$objects, $method) {
		$sum = 0;

		foreach ($objects as $object) {
			$sum += $method($object);
		}

		return $sum;
	}
}