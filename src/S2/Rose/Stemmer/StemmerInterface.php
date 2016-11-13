<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

/**
 * Interface StemmerInterface
 */
interface StemmerInterface
{
	/**
	 * @param string $word
	 *
	 * return string
	 */
	public function stemWord($word);
}
