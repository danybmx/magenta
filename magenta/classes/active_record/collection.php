<?php
/**
 * Class for make collections with some models and can export it to JSON or ARRAY
 *
 * @package ActiveRecord
 * @author danybmx <dany@dpstudios.es>
 */
class ActiveRecord_Collection implements Iterator, ArrayAccess
{
	protected $position = 0;
	protected $items = array();
	protected $array_items = array();
	protected $json_string = '';

	/** toString */
	public function __toString() {
		return $this->toJson();
	}

	/** Iterator **/
	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return key_exists($this->position, $this->items);
	}

	public function getIterator() {
		return new ArrayIterator($this->items);
	}

	/** ArrayAccess **/
	public function offsetSet($offset, $value) {
		if (is_null($offset))
			$this->items[] = $value;
		else
			$this->items[$offset] = $value;
	}

	public function offsetExists($offset) {
		return key_exists($offset, $this->items);
	}

	public function offsetUnset($offset) {
		unset($this->items[$offset]);
	}

	public function offsetGet($offset) {
		return key_exists($offset, $this->items) ? $this->items[$offset] : null;
	}

	/**
	 * Function for add items to the collection
	 *
	 * @param array $items items for add to collection
	 */
	public function addItems($items) {
		if ( ! is_array($items)) $items = array($items);
		$this->items = array_merge($this->items, $items);
	}

	/**
	 * Function for count items of collection
	 *
	 * @return int
	 */
	public function length() {
		return count($this->items);
	}

	/**
	 * Function for export to json the complete collection
	 *
	 * @param string $relations for export relations too
	 * @param string $key for export inside a key
	 * @return string
	 */
	public function toJson($relations = null, $key = null) {
		if ( ! $this->json_string) {
			if ( ! $this->array_items) $this->toArray($relations);
			$this->json_string = $this->array_items;
		}
		if ($key)
			$this->json_string = array($key => $this->json_string);

		return json_encode($this->json_string);
	}

	/**
	 * Function for export to array the complete collection
	 *
	 * @return array
	 */
	public function toArray($relations = array()) {
		$array_items = array();
		foreach ($this->items as $k => $i) {
			$array_items[$k] = $i->toArray($relations);
		}
		$this->array_items = $array_items;

		return $this->array_items;
	}
}
