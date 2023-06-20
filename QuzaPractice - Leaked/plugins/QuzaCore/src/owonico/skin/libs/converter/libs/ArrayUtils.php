<?php

namespace owonico\skin\libs\converter\libs;

use ArrayObject;
use BadMethodCallException;
use Exception;

use function array_chunk;
use function array_column;
use function array_combine;
use function array_count_values;
use function array_diff;
use function array_diff_assoc;
use function array_diff_key;
use function array_fill_keys;
use function array_flip;
use function array_intersect;
use function array_intersect_assoc;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function array_pad;
use function array_pop;
use function array_push;
use function array_replace_recursive;
use function array_reverse;
use function array_shift;
use function array_splice;
use function array_sum;
use function array_unique;
use function array_unshift;
use function array_values;
use function count;
use function implode;
use function is_array;
use function ksort;
use function max;
use function method_exists;
use function min;
use function random_int;
use function sort;
use function str_ends_with;
use function strpos;
use function substr;
use function substr_replace;
use function uksort;
use function usort;

class ArrayUtils extends ArrayObject{
    /** Creates a new, shallow-copied ArrayUtils instance from an iterable */
    public function __construct(iterable $iterable, int $flags = 0, string $iteratorClass = "ArrayIterator"){
        parent::__construct((array) $iterable, $flags, $iteratorClass);
    }

    /**
     * Creates a new, shallow-copied ArrayUtils instance from an iterable
     *
     * @param iterable  $iterable
     * @param ?callable $mapFn = null
     *
     * @link https://arrayutils.docs.present.kim/methods/s/from
     */
    public static function from(iterable $iterable, ?callable $mapFn = null) : ArrayUtils{
        $instance = new self($iterable);
        if($mapFn !== null){
            $instance = $instance->map($mapFn);
        }
        return $instance;
    }

    /**
     * Creates a new, ArrayUtils instance from variadic function arguments
     *
     * @param mixed ...$elements
     *
     * @link https://arrayutils.docs.present.kim/methods/s/of
     */
    public static function of(...$elements) : ArrayUtils{
        return new self($elements);
    }

    /**
     * Cast all elements of the iterable to an array
     *
     * @link https://arrayutils.docs.present.kim/methods/s/maptoarray
     */
    public static function mapToArray(iterable $iterables) : array{
        return self::__map((array) $iterables, static function($iterable){ return (array) $iterable; });
    }

    /** Exchange the array for another one */
    public function exchange(array $array) : ArrayUtils{
        $this->exchangeArray($array);
        return $this;
    }

    /**
     * Split an array into chunks
     *
     * @link https://arrayutils.docs.present.kim/methods/c/chunk
     */
    protected static function __chunk(array $from, int $size, bool $preserveKeys = false) : array{
        return array_chunk($from, $size, $preserveKeys);
    }

    /**
     * Returns the values from a single column in the input array
     *
     * @param array           $from
     * @param int|string      $valueKey
     * @param int|string|null $indexKey = null
     *
     * @link https://arrayutils.docs.present.kim/methods/c/column
     */
    protected static function __column(array $from, int|string $valueKey, int|string|null $indexKey = null) : array{
        return array_column($from, $valueKey, $indexKey);
    }

    /**
     * Creates an array by using one array for keys and another for its values
     *
     * @param iterable|null $valueArray If is null, Use self clone
     *
     * @link https://arrayutils.docs.present.kim/methods/c/combine
     */
    protected static function __combine(array $from, ?iterable $valueArray = null) : array{
        return array_combine($from, (array) ($valueArray ?? $from));
    }

    /**
     * Merge one or more arrays
     *
     * @params mixed ...$value
     *
     * @link https://arrayutils.docs.present.kim/methods/c/concat
     */
    protected static function __concat(...$values) : array{
        return array_merge(...self::mapToArray($values));
    }

    /**
     * All similar to @see ArrayUtils::__concat(), but not overwrite existing keys
     *
     * @params mixed ...$value
     *
     * @link https://arrayutils.docs.present.kim/methods/c/concat/soft
     */
    protected static function __concatSoft(...$values) : array{
        $array = [];
        foreach($values as $value){
            $array += (array) $value;
        }
        return $array;
    }

    /**
     * Counts all the values of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/count-values
     */
    protected static function __countValues(array $from) : array{
        return array_count_values($from);
    }

    /**
     * Computes the difference of arrays
     *
     * @link https://arrayutils.docs.present.kim/methods/c/diff
     */
    protected static function __diff(array $from, iterable ...$iterables) : array{
        return array_diff($from, ...self::mapToArray($iterables));
    }

    /**
     * All similar to @see ArrayUtils::__diff(), but this applies with additional index check
     *
     * @link https://arrayutils.docs.present.kim/methods/c/diff/assoc
     */
    protected static function __diffAssoc(array $from, iterable ...$iterables) : array{
        return array_diff_assoc($from, ...self::mapToArray($iterables));
    }

    /**
     * All similar to @see ArrayUtils::__diff(), but this applies to keys
     *
     * @link https://arrayutils.docs.present.kim/methods/c/diff/key
     */
    protected static function __diffKey(array $from, iterable ...$iterables) : array{
        return array_diff_key($from, ...self::mapToArray($iterables));
    }

    /**
     * Tests whether all elements pass the $callback function
     *
     * @link https://arrayutils.docs.present.kim/methods/c/fill
     */
    protected static function _every(array $from, callable $callback) : bool{
        foreach($from as $key => $value){
            if(!$callback($value, $key, $from)){
                return false;
            }
        }
        return true;
    }

    /**
     * Changes all elements in an array to a provided value, from a start index to an end index
     *
     * @link https://arrayutils.docs.present.kim/methods/c/fill/keys
     */
    protected static function __fill(array $from, $value, int $start = 0, int $end = null) : array{
        $count = count($from);
        $end = $end ?? $count;

        $i = $start < 0 ? max($count + $start, 0) : min($start, $count);
        $max = $end < 0 ? max($count + $end, 0) : min($end, $count);
        for(; $i < $max; ++$i){
            $from[$i] = $value;
        }

        return $from;
    }

    /**
     * Fill an array with values, specifying keys
     *
     * @param mixed $value
     *
     * @link https://arrayutils.docs.present.kim/methods/c/fill/keys
     */
    protected static function __fillKeys(array $from, mixed $value) : array{
        return array_fill_keys($from, $value);
    }

    /**
     * Returns a new array with all elements that pass the $callback function
     *
     * @link https://arrayutils.docs.present.kim/methods/c/filter
     */
    protected static function __filter(array $from, callable $callback) : array{
        $array = [];
        foreach($from as $key => $value){
            if($callback($value, $key, $from)){
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * Returns a new array with all sub-array elements concatenated into it recursively up to the specified depth
     *
     * @link https://arrayutils.docs.present.kim/methods/c/flat
     */
    protected static function __flat(array $from, int $dept = 1) : array{
        if($dept === 0){
            return $from;
        }
        return self::_reduce($from,
            static function($currentValue, $value) use ($dept){
                return self::__concat(
                    $currentValue,
                    is_array($value) ? self::__flat($value, $dept - 1) : $value
                );
            }, []);
    }

    /**
     * Returns a new array formed by applying $callback function and then flattening the result by one level
     *
     * @link https://arrayutils.docs.present.kim/methods/c/flat/map
     */
    protected static function __flatMap(array $from, callable $callback) : array{
        return self::__concat(...self::__map($from, $callback));
    }

    /**
     * Exchanges all keys with their associated values in an array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/flip
     */
    protected static function __flip(array $from) : array{
        return array_flip($from);
    }

    /**
     * Executes a $callback function once for each array element.
     *
     * @link https://arrayutils.docs.present.kim/methods/c/for-each
     */
    protected static function __forEach(array $from, callable $callback) : array{
        foreach($from as $key => $value){
            $callback($value, $key, $from);
        }
        return $from;
    }

    /**
     * Computes the intersection of arrays
     *
     * @link https://arrayutils.docs.present.kim/methods/c/intersect
     */
    protected static function __intersect(array $from, iterable ...$iterables) : array{
        return array_intersect($from, ...self::mapToArray($iterables));
    }

    /**
     * All similar to @see ArrayUtils::__intersect(), but this applies to both keys and values
     *
     * @link https://arrayutils.docs.present.kim/methods/c/intersect/assoc
     */
    protected static function __intersectAssoc(array $from, iterable ...$iterables) : array{
        return array_intersect_assoc($from, ...self::mapToArray($iterables));
    }

    /**
     * All similar to @see ArrayUtils::__intersect(), but this applies to keys
     *
     * @link https://arrayutils.docs.present.kim/methods/c/intersect/key
     */
    protected static function __intersectKey(array $from, iterable ...$iterables) : array{
        return array_intersect_key($from, ...self::mapToArray($iterables));
    }

    /**
     * Returns all the keys of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/keys
     */
    protected static function __keys(array $from) : array{
        return array_keys($from);
    }

    /**
     * Applies the callback to the values of the given arrays
     *
     * @link https://arrayutils.docs.present.kim/methods/c/map
     */
    protected static function __map(array $from, callable $callback) : array{
        $array = [];
        foreach($from as $key => $value){
            $array[$key] = $callback($value, $key, $from);
        }
        return $array;
    }

    /**
     * All similar to @see ArrayUtils::__map(), but this applies to both keys and values
     *
     * @link https://arrayutils.docs.present.kim/methods/c/map/assoc
     */
    protected static function __mapAssoc(array $from, callable $callback) : array{
        $array = [];
        foreach($from as $key => $value){
            [$newKey, $newValue] = $callback($value, $key, $from);
            $array[$newKey] = $newValue;
        }
        return $array;
    }

    /**
     * All similar to @see ArrayUtils::__map(), but this applies to keys
     *
     * @link https://arrayutils.docs.present.kim/methods/c/map/key
     */
    protected static function __mapKey(array $from, callable $callback) : array{
        $array = [];
        foreach($from as $key => $value){
            $array[$callback($value, $key, $from)] = $value;
        }
        return $array;
    }

    /**
     * Pad array to the specified length with a value
     *
     * @link https://arrayutils.docs.present.kim/methods/c/pad
     */
    protected static function __pad(array $from, int $size, $value) : array{
        return array_pad($from, $size, $value);
    }

    /**
     * Push elements onto the end of array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/push
     */
    protected static function __push(array $from, ...$values) : array{
        array_push($from, ...$values);
        return $from;
    }

    /**
     * Replaces elements from passed arrays into the first array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/replace
     */
    protected static function __replace(array $from, iterable ...$iterables) : array{
        return array_replace_recursive($from, ...self::mapToArray($iterables));
    }

    /**
     * Returns an array with elements in reverse order
     *
     * @link https://arrayutils.docs.present.kim/methods/c/reverse
     */
    protected static function __reverse(array $from, bool $preserveKeys = false) : array{
        return array_reverse($from, $preserveKeys);
    }

    /**
     * Returns an array with selected from start to end
     * Extract a slice of the array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/slice
     */
    protected static function __slice(array $from, int $start = 0, int $end = null, bool $preserveKeys = false) : array{
        $array = [];
        $keys = array_keys($from);
        $values = array_values($from);
        $count = count($from);
        $end = $end ?? $count;

        $i = $start < 0 ? max($count + $start, 0) : min($start, $count);
        $max = $end < 0 ? max($count + $end, 0) : min($end, $count);
        for(; $i < $max; ++$i){
            if($preserveKeys){
                $array[$keys[$i]] = $values[$i];
            }else{
                $array[] = $values[$i];
            }
        }

        return $array;
    }

    /**
     * Sort an array by values using a $call function or default sort function
     *
     * @param callable|null $callback if is null, run sort(), else run usort()
     *
     * @link https://arrayutils.docs.present.kim/methods/c/sort
     */
    protected static function __sort(array $from, ?callable $callback = null) : array{
        if($callback === null){
            sort($from);
        }else{
            usort($from, $callback);
        }
        return $from;
    }

    /**
     * Sort an array by keys using a $call function or default sort function
     *
     * @param callable|null $callback if is null, run ksort(), else run uksort()
     *
     * @link https://arrayutils.docs.present.kim/methods/c/sort/key
     */
    protected static function __sortKey(array $from, ?callable $callback = null) : array{
        if($callback === null){
            ksort($from);
        }else{
            uksort($from, $callback);
        }
        return $from;
    }

    /**
     * Removes duplicate values from an array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/unique
     */
    protected static function __unique(array $from, int $sortFlags = SORT_STRING) : array{
        return array_unique($from, $sortFlags);
    }

    /**
     * Push elements onto the start of array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/unshift
     */
    protected static function __unshift(array $from, ...$values) : array{
        array_unshift($from, ...$values);
        return $from;
    }

    /**
     * Returns all the values of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/c/values
     */
    protected static function __values(array $from) : array{
        return array_values($from);
    }

    /**
     * Join array elements with a string. You can specify a suffix and prefix
     *
     * @link https://arrayutils.docs.present.kim/methods/g/join
     */
    protected static function _join(array $from, string $glue = ",", string $prefix = "", string $suffix = "") : string{
        return $prefix . implode($glue, $from) . $suffix;
    }

    /**
     * Tests whether an array includes a $needle
     *
     * @link https://arrayutils.docs.present.kim/methods/g/includes
     */
    protected static function _includes(array $from, $needle, int $start = 0) : bool{
        $values = array_values($from);
        $count = count($from);
        $i = $start < 0 ? max($count + $start, 0) : min($start, $count);
        for(; $i < $count; ++$i){
            if($needle === $values[$i]){
                return true;
            }
        }

        return false;
    }

    /**
     * Alias of @see offsetExists()
     *
     * @link https://arrayutils.docs.present.kim/methods/g/key-exists
     */
    protected static function _keyExists(array $from, $key) : bool{
        return isset($from[$key]);
    }

    /**
     * Returns the first index at which a given element can be found in the array
     *
     * @link https://arrayutils.docs.present.kim/methods/g/index-of
     */
    protected static function _indexOf(array $from, $needle, int $start = 0) : int|string|null{
        $keys = array_keys($from);
        $values = array_values($from);
        $count = count($from);
        $i = $start < 0 ? max($count + $start, 0) : min($start, $count);
        for(; $i < $count; ++$i){
            if($needle === $values[$i]){
                return $keys[$i];
            }
        }

        return null;
    }

    /**
     * Returns the value of the first element that that pass the $callback function
     *
     * @link https://arrayutils.docs.present.kim/methods/g/find
     */
    protected static function _find(array $from, callable $callback) : mixed{
        foreach($from as $key => $value){
            if($callback($value, $key, $from)){
                return $value;
            }
        }
        return null;
    }

    /**
     * Returns the key of the first element that that pass the $callback function
     *
     * @link https://arrayutils.docs.present.kim/methods/g/find/index
     */
    protected static function _findIndex(array $from, callable $callback) : int|string|null{
        foreach($from as $key => $value){
            if($callback($value, $key, $from)){
                return $key;
            }
        }
        return null;
    }

    /**
     * Returns the value at the result of _keyFirst()
     *
     * @link https://arrayutils.docs.present.kim/methods/g/first
     */
    protected static function _first(array $from) : mixed{
        return $from[self::_keyFirst($from)];
    }

    /**
     * Gets the first key of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/g/first/key
     */
    protected static function _keyFirst(array $from) : int|string|null{
        return array_keys($from)[0] ?? null;
    }

    /**
     * Returns the value at the result of @see ArrayUtils::_keyLast()
     *
     * @link https://arrayutils.docs.present.kim/methods/g/last
     */
    protected static function _last(array $from) : mixed{
        return $from[self::_keyLast($from)];
    }

    /**
     * Gets the last key of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/g/last/key
     */
    protected static function _keyLast(array $from) : int|string|null{
        return array_keys($from)[count($from) - 1] ?? null;
    }

    /**
     * Returns the value at the result of @see ArrayUtils::_keyRandom()
     *
     * @link https://arrayutils.docs.present.kim/methods/g/random
     */
    protected static function _random(array $from) : mixed{
        return $from[self::_keyRandom($from)];
    }

    /**
     * Gets the random key of an array
     *
     * @link https://arrayutils.docs.present.kim/methods/g/random/key
     */
    protected static function _keyRandom(array $from) : int|string|null{
        try{
            return ($keys = array_keys($from))[random_int(0, count($keys) - 1)] ?? null;
        }catch(Exception){
            return null;
        }
    }

    /**
     * Removes the last element and returns that element
     *
     * @link https://arrayutils.docs.present.kim/methods/g/pop
     */
    protected static function _pop(array &$from){
        return array_pop($from);
    }

    /**
     * Executes a reducer function on each element of the array, resulting in single output value
     *
     * @link https://arrayutils.docs.present.kim/methods/g/reduce
     */
    protected static function _reduce(array $from, callable $callback, $initialValue = null){
        $currentValue = $initialValue;
        foreach($from as $key => $value){
            $currentValue = $callback($currentValue, $value, $key, $from);
        }
        return $currentValue;
    }

    /**
     * All similar to @see ArrayUtils::_reduce(), but reverse order
     *
     * @link https://arrayutils.docs.present.kim/methods/g/reduce/right
     */
    protected static function _reduceRight(array $from, callable $callback, $initialValue = null){
        $currentValue = $initialValue;
        foreach(array_reverse($from) as $key => $value){
            $currentValue = $callback($currentValue, $value, $key, $from);
        }
        return $currentValue;
    }

    /**
     * Removes the first element and returns that element
     *
     * @link https://arrayutils.docs.present.kim/methods/g/shift
     */
    protected static function _shift(array &$from){
        return array_shift($from);
    }

    /**
     * Tests whether least one element pass the $callback function
     *
     * @link https://arrayutils.docs.present.kim/methods/g/some
     */
    protected static function _some(array $from, callable $callback) : bool{
        foreach($from as $key => $value){
            if($callback($value, $key, $from)){
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a portion of the array and replace it with something else
     *
     * @param int|null $length = count($this)
     *
     * @link https://arrayutils.docs.present.kim/methods/g/splice
     */
    protected static function _splice(array &$from, int $offset, ?int $length = null, ...$replacement) : array{
        return array_splice($from, $offset, $length ?? count($from), $replacement);
    }

    /**
     * Calculate the sum of values in an array
     *
     * @link https://arrayutils.docs.present.kim/methods/g/sum
     */
    protected static function _sum(array $from) : int|float{
        return array_sum($from);
    }

    /** Alias of @see ArrayUtils::__concat() */
    protected static function __merge(...$values) : array{
        return self::__concat(...$values);
    }

    /** Alias of @see ArrayUtils::__concatSoft() */
    protected static function __mergeSoft(...$values) : array{
        return self::__concatSoft(...$values);
    }

    /** Alias of @see ArrayUtils::_indexOf() */
    protected static function _search(array $from, $needle, int $start = 0) : int|string|null{
        return self::_indexOf($from, $needle, $start);
    }

    /** @throws BadMethodCallException */
    public function __call(string $name, array $arguments){
        if($raw = str_ends_with($name, "As")){
            $name = substr($name, 0, -2);
        }

        if(method_exists(self::class, $method = "__$name")){
            //Mapping method calls omitting "__" (It is meaning result is array)
            $result = self::$method((array) $this, ...$arguments);
            return $raw || !is_array($result) ? $result : $this->exchange($result);
        }

        if(method_exists(self::class, $method = "_$name")){
            //Mapping method calls omitting "_" (It is meaning result is not array)
            $array = (array) $this;
            $result = self::$method($array, ...$arguments);
            $this->exchangeArray($array);
            return $result;
        }

        throw new BadMethodCallException("Call to undefined method " . self::class . "::$name()");
    }

    /**
     * Process static accessing to use ArrayUtils quickly
     *
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments){
        if(($pos = strpos($name, "From")) !== false){
            $name = substr_replace($name, "", $pos, 4);
        }
        return self::from(array_shift($arguments))->$name(...$arguments);
    }
}