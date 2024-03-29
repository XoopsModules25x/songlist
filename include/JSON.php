<?php declare(strict_types=1);

/*
Module: Document

Version: 2.01

Description: Multilingual Content Module with tags and lists with search functions

Author: Written by Simon Roberts aka. Wishcraft (simon@chronolabs.coop)

Owner: Chronolabs

License: See /docs - GPL 2.0
*/

/*
* @link        https://pear.php.net/pepr/pepr-proposal-show.php?id=198
*/

/**
 * Marker constant for ServicesJSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_SLICE', 1);

/**
 * Marker constant for ServicesJSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_STR', 2);

/**
 * Marker constant for ServicesJSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_ARR', 3);

/**
 * Marker constant for ServicesJSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_OBJ', 4);

/**
 * Marker constant for ServicesJSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_CMT', 5);

/**
 * Behavior switch for ServicesJSON::decode()
 */
define('SERVICES_JSON_LOOSE_TYPE', 16);

/**
 * Behavior switch for ServicesJSON::decode()
 */
define('SERVICES_JSON_SUPPRESS_ERRORS', 32);

/**
 * Converts to and from JSON format.
 *
 * Brief example of use:
 *
 * <code>
 * // create a new instance of ServicesJSON
 * $json = new ServicesJSON();
 *
 * // convert a complexe value to JSON notation, and send it to the browser
 * $value = array('foo', 'bar', array(1, 2, 'baz'), array(3, array(4)));
 * $output = $json->encode($value);
 *
 * print($output);
 * // prints: ["foo","bar",[1,2,"baz"],[3,[4]]]
 *
 * // accept incoming POST data, assumed to be in JSON notation
 * $input = file_get_contents('php://input', 1000000);
 * $value = $json->decode($input);
 * </code>
 */
class ServicesJSON
{
    /**
     * constructs a new JSON instance
     *
     * @param int $use object behavior flags; combine with boolean-OR
     *
     *                           possible values:
     *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
     *                                   "{...}" syntax creates associative arrays
     *                                   instead of objects in decode().
     *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
     *                                   Values which can't be encoded (e.g. resources)
     *                                   appear as NULL instead of throwing errors.
     *                                   By default, a deeply-nested resource will
     *                                   bubble up with an error, so all return values
     *                                   from encode() should be checked with isError()
     */
    public function __construct($use = 0)
    {
        $this->use = $use;
    }

    /**
     * convert a string from one UTF-16 char to one UTF-8 char
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     *
     * @param string $utf16 UTF-16 character
     * @return   string  UTF-8 character
     */
    public function utf162utf8($utf16): string
    {
        // oh please oh please oh please oh please oh please
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
        }

        $bytes = (ord($utf16[0]) << 8) | ord($utf16[1]);

        switch (true) {
            case ((0x7F & $bytes) == $bytes):
                // this case should never be reached, because we are in ASCII range
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x7F & $bytes);
            case (0x07FF & $bytes) == $bytes:
                // return a 2-byte UTF-8 character
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xC0 | (($bytes >> 6) & 0x1F)) . chr(0x80 | ($bytes & 0x3F));
            case (0xFFFF & $bytes) == $bytes:
                // return a 3-byte UTF-8 character
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0xE0 | (($bytes >> 12) & 0x0F)) . chr(0x80 | (($bytes >> 6) & 0x3F)) . chr(0x80 | ($bytes & 0x3F));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

    /**
     * convert a string from one UTF-8 char to one UTF-16 char
     *
     * Normally should be handled by mb_convert_encoding, but
     * provides a slower PHP-only method for installations
     * that lack the multibye string extension.
     *
     * @param string $utf8 UTF-8 character
     * @return   string  UTF-16 character
     */
    public function utf82utf16($utf8): string
    {
        // oh please oh please oh please oh please oh please
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        switch (mb_strlen($utf8)) {
            case 1:
                // this case should never be reached, because we are in ASCII range
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;
            case 2:
                // return a UTF-16 character from a 2-byte UTF-8 char
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8[0]) >> 2)) . chr((0xC0 & (ord($utf8[0]) << 6)) | (0x3F & ord($utf8[1])));
            case 3:
                // return a UTF-16 character from a 3-byte UTF-8 char
                // see: https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8[0]) << 4)) | (0x0F & (ord($utf8[1]) >> 2))) . chr((0xC0 & (ord($utf8[1]) << 6)) | (0x7F & ord($utf8[2])));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }

    /**
     * encodes an arbitrary variable into JSON format (and sends JSON Header)
     *
     * @param mixed $var         any number, boolean, string, array, or object to be encoded.
     *                           see argument 1 to ServicesJSON() above for array-parsing behavior.
     *                           if var is a strng, note that encode() always expects it
     *                           to be in ASCII or UTF-8 format!
     *
     * @return   mixed   JSON string representation of input var or an error if a problem occurs
     */
    public function encode($var)
    {
        header('Document-type: application/json');

        return $this->encodeUnsafe($var);
    }

    /**
     * encodes an arbitrary variable into JSON format without JSON Header - warning - may allow CSS!!!!)
     *
     * @param mixed $var         any number, boolean, string, array, or object to be encoded.
     *                           see argument 1 to ServicesJSON() above for array-parsing behavior.
     *                           if var is a strng, note that encode() always expects it
     *                           to be in ASCII or UTF-8 format!
     *
     * @return   mixed   JSON string representation of input var or an error if a problem occurs
     */
    public function encodeUnsafe($var)
    {
        // see bug #16908 - regarding numeric locale printing
        $lc = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, 'C');
        $ret = $this->_encode($var);
        setlocale(LC_NUMERIC, $lc);

        return $ret;
    }

    /**
     * PRIVATE CODE that does the work of encodes an arbitrary variable into JSON format
     *
     * @param mixed $var         any number, boolean, string, array, or object to be encoded.
     *                           see argument 1 to ServicesJSON() above for array-parsing behavior.
     *                           if var is a strng, note that encode() always expects it
     *                           to be in ASCII or UTF-8 format!
     *
     * @return   mixed   JSON string representation of input var or an error if a problem occurs
     */
    public function _encode($var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'integer':
                return (int)$var;
            case 'double':
            case 'float':
                return (float)$var;
            case 'string':
                // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                $ascii      = '';
                $strlen_var = mb_strlen($var);

                /*
                 * Iterate over every character in the string,
                 * escaping with a slash or encoding to UTF-8 where necessary
                 */
                for ($c = 0; $c < $strlen_var; ++$c) {
                    $ord_var_c = ord($var[$c]);

                    switch (true) {
                        case 0x08 == $ord_var_c:
                            $ascii .= '\b';
                            break;
                        case 0x09 == $ord_var_c:
                            $ascii .= '\t';
                            break;
                        case 0x0A == $ord_var_c:
                            $ascii .= '\n';
                            break;
                        case 0x0C == $ord_var_c:
                            $ascii .= '\f';
                            break;
                        case 0x0D == $ord_var_c:
                            $ascii .= '\r';
                            break;
                        case 0x22 == $ord_var_c:
                        case 0x2F == $ord_var_c:
                        case 0x5C == $ord_var_c:
                            // double quote, slash, slosh
                            $ascii .= '\\' . $var[$c];
                            break;
                        case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                            // characters U-00000000 - U-0000007F (same as ASCII)
                            $ascii .= $var[$c];
                            break;
                        case (0xC0 == ($ord_var_c & 0xE0)):
                            // characters U-00000080 - U-000007FF, mask 110SONGLIST
                            // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            if ($c + 1 >= $strlen_var) {
                                ++$c;
                                $ascii .= '?';
                                break;
                            }

                            $char = pack('C*', $ord_var_c, ord($var[$c + 1]));
                            ++$c;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        case (0xE0 == ($ord_var_c & 0xF0)):
                            if ($c + 2 >= $strlen_var) {
                                $c     += 2;
                                $ascii .= '?';
                                break;
                            }
                            // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                            // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char  = pack('C*', $ord_var_c, @ord($var[$c + 1]), @ord($var[$c + 2]));
                            $c     += 2;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        case (0xF0 == ($ord_var_c & 0xF8)):
                            if ($c + 3 >= $strlen_var) {
                                $c     += 3;
                                $ascii .= '?';
                                break;
                            }
                            // characters U-00010000 - U-001FFFFF, mask 11110XXX
                            // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char  = pack('C*', $ord_var_c, ord($var[$c + 1]), ord($var[$c + 2]), ord($var[$c + 3]));
                            $c     += 3;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        case (0xF8 == ($ord_var_c & 0xFC)):
                            // characters U-00200000 - U-03FFFFFF, mask 111110XX
                            // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            if ($c + 4 >= $strlen_var) {
                                $c     += 4;
                                $ascii .= '?';
                                break;
                            }
                            $char  = pack('C*', $ord_var_c, ord($var[$c + 1]), ord($var[$c + 2]), ord($var[$c + 3]), ord($var[$c + 4]));
                            $c     += 4;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                        case (0xFC == ($ord_var_c & 0xFE)):
                            if ($c + 5 >= $strlen_var) {
                                $c     += 5;
                                $ascii .= '?';
                                break;
                            }
                            // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                            // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char  = pack('C*', $ord_var_c, ord($var[$c + 1]), ord($var[$c + 2]), ord($var[$c + 3]), ord($var[$c + 4]), ord($var[$c + 5]));
                            $c     += 5;
                            $utf16 = $this->utf82utf16($char);
                            $ascii .= sprintf('\u%04s', bin2hex($utf16));
                            break;
                    }
                }

                return '"' . $ascii . '"';
            case 'array':
                /*
                 * As per JSON spec if any array key is not an integer
                 * we must treat the the whole array as an object. We
                 * also try to catch a sparsely populated associative
                 * array with numeric keys here because some JS engines
                 * will create an array with empty indexes up to
                 * max_index which can cause memory issues and because
                 * the keys, which may be relevant, will be remapped
                 * otherwise.
                 *
                 * As per the ECMA and JSON specification an object may
                 * have any string as a property. Unfortunately due to
                 * a hole in the ECMA specification if the key is a
                 * ECMA reserved word or starts with a digit the
                 * parameter is only accessible using ECMAScript's
                 * bracket notation.
                 */

                // treat as a JSON object
                if (is_array($var) && count($var) && (array_keys($var) !== range(0, count($var) - 1))) {
                    $properties = array_map([$this, 'name_value'], array_keys($var), array_values($var));

                    foreach ($properties as $property) {
                        if ($this->isError($property)) {
                            return $property;
                        }
                    }

                    return '{' . implode(',', $properties) . '}';
                }

                // treat it like a regular array
                $elements = array_map([$this, '_encode'], $var);

                foreach ($elements as $element) {
                    if ($this->isError($element)) {
                        return $element;
                    }
                }

                return '[' . implode(',', $elements) . ']';
            case 'object':
                $vars = get_object_vars($var);

                $properties = array_map([$this, 'name_value'], array_keys($vars), array_values($vars));

                foreach ($properties as $property) {
                    if ($this->isError($property)) {
                        return $property;
                    }
                }

                return '{' . implode(',', $properties) . '}';
            default:
                return ($this->use & SERVICES_JSON_SUPPRESS_ERRORS) ? 'null' : new ServicesJSON_Error(gettype($var) . ' can not be encoded as JSON string');
        }
    }

    /**
     * array-walking function for use in generating JSON-formatted name-value pairs
     *
     * @param string $name  name of key to use
     * @param mixed  $value reference to an array element to be encoded
     *
     * @return   string  JSON-formatted name-value pair, like '"name":value'
     */
    public function name_value($name, $value)
    {
        $encoded_value = $this->_encode($value);

        if ($this->isError($encoded_value)) {
            return $encoded_value;
        }

        return $this->_encode((string)$name) . ':' . $encoded_value;
    }

    /**
     * reduce a string by removing leading and trailing comments and whitespace
     *
     * @param string $str string value to strip of comments and whitespace
     *
     * @return   string  string value stripped of comments and whitespace
     */
    public function reduce_string($str): string
    {
        $str = preg_replace(
            [
                // eliminate single line comments in '// ...' form
                '#^\s*//(.+)$#m',

                // eliminate multi-line comments in '/* ... */' form, at start of string
                '#^\s*/\*(.+)\*/#Us',

                // eliminate multi-line comments in '/* ... */' form, at end of string
                '#/\*(.+)\*/\s*$#Us',
            ],
            '',
            $str
        );

        // eliminate extraneous space
        return trim($str);
    }

    /**
     * decodes a JSON string into appropriate variable
     *
     * @param string $str JSON-formatted string
     *
     * @return   array|bool|float|int|\stdClass|string|void|null   number, boolean, string, array, or object
     *                   corresponding to given JSON input string.
     *                   See argument 1 to ServicesJSON() above for object-output behavior.
     *                   Note that decode() always returns strings
     *                   in ASCII or UTF-8 format!
     */
    public function decode($str)
    {
        $str = $this->reduce_string($str);

        switch (mb_strtolower($str)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            default:
                $m = [];

                if (is_numeric($str)) {
                    // Lookie-loo, it's a number

                    // This would work on its own, but I'm trying to be
                    // good about returning integers where appropriate:
                    // return (float)$str;

                    // Return float or int, as appropriate
                    return ((float)$str == (int)$str) ? (int)$str : (float)$str;
                }

                if (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                    // STRINGS RETURNED IN UTF-8 FORMAT
                    $delim       = mb_substr($str, 0, 1);
                    $chrs        = mb_substr($str, 1, -1);
                    $utf8        = '';
                    $strlen_chrs = mb_strlen($chrs);

                    for ($c = 0; $c < $strlen_chrs; ++$c) {
                        $substr_chrs_c_2 = mb_substr($chrs, $c, 2);
                        $ord_chrs_c      = ord($chrs[$c]);

                        switch (true) {
                            case '\b' === $substr_chrs_c_2:
                                $utf8 .= chr(0x08);
                                ++$c;
                                break;
                            case '\t' === $substr_chrs_c_2:
                                $utf8 .= chr(0x09);
                                ++$c;
                                break;
                            case '\n' === $substr_chrs_c_2:
                                $utf8 .= chr(0x0A);
                                ++$c;
                                break;
                            case '\f' === $substr_chrs_c_2:
                                $utf8 .= chr(0x0C);
                                ++$c;
                                break;
                            case '\r' === $substr_chrs_c_2:
                                $utf8 .= chr(0x0D);
                                ++$c;
                                break;
                            case '\\"' === $substr_chrs_c_2:
                            case '\\\'' === $substr_chrs_c_2:
                            case '\\\\' === $substr_chrs_c_2:
                            case '\\/' === $substr_chrs_c_2:
                                if (('"' === $delim && '\\\'' !== $substr_chrs_c_2)
                                    || ("'" === $delim && '\\"' !== $substr_chrs_c_2)) {
                                    $utf8 .= $chrs[++$c];
                                }
                                break;
                            case preg_match('/\\\u[0-9A-F]{4}/i', mb_substr($chrs, $c, 6)):
                                // single, escaped unicode character
                                $utf16 = chr(hexdec(mb_substr($chrs, $c + 2, 2))) . chr(hexdec(mb_substr($chrs, $c + 4, 2)));
                                $utf8  .= $this->utf162utf8($utf16);
                                $c     += 5;
                                break;
                            case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                $utf8 .= $chrs[$c];
                                break;
                            case 0xC0 == ($ord_chrs_c & 0xE0):
                                // characters U-00000080 - U-000007FF, mask 110SONGLIST
                                //see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= mb_substr($chrs, $c, 2);
                                ++$c;
                                break;
                            case 0xE0 == ($ord_chrs_c & 0xF0):
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= mb_substr($chrs, $c, 3);
                                $c    += 2;
                                break;
                            case 0xF0 == ($ord_chrs_c & 0xF8):
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= mb_substr($chrs, $c, 4);
                                $c    += 3;
                                break;
                            case 0xF8 == ($ord_chrs_c & 0xFC):
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= mb_substr($chrs, $c, 5);
                                $c    += 4;
                                break;
                            case 0xFC == ($ord_chrs_c & 0xFE):
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see https://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $utf8 .= mb_substr($chrs, $c, 6);
                                $c    += 5;
                                break;
                        }
                    }

                    return $utf8;
                }

                if (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                    // array, or object notation

                    if ('[' === $str[0]) {
                        $stk = [SERVICES_JSON_IN_ARR];
                        $arr = [];
                    } elseif ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                            $stk = [SERVICES_JSON_IN_OBJ];
                            $obj = [];
                        } else {
                            $stk = [SERVICES_JSON_IN_OBJ];
                            $obj = new stdClass();
                    }

                    array_push(
                        $stk,
                        [
                            'what'  => SERVICES_JSON_SLICE,
                            'where' => 0,
                            'delim' => false,
                        ]
                    );

                    $chrs = mb_substr($str, 1, -1);
                    $chrs = $this->reduce_string($chrs);

                    if ('' == $chrs) {
                        if (SERVICES_JSON_IN_ARR == reset($stk)) {
                            return $arr;
                        }

                        return $obj;
                    }

                    //print("\nparsing {$chrs}\n");

                    $strlen_chrs = mb_strlen($chrs);

                    for ($c = 0; $c <= $strlen_chrs; ++$c) {
                        $top             = end($stk);
                        $substr_chrs_c_2 = mb_substr($chrs, $c, 2);

                        if (($c == $strlen_chrs) || ((',' === $chrs[$c]) && (SERVICES_JSON_SLICE == $top['what']))) {
                            // found a comma that is not inside a string, array, etc.,
                            // OR we've reached the end of the character list
                            $slice = mb_substr($chrs, $top['where'], $c - $top['where']);
                            array_push($stk, ['what' => SERVICES_JSON_SLICE, 'where' => $c + 1, 'delim' => false]);
                            //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            if (SERVICES_JSON_IN_ARR == reset($stk)) {
                                // we are in an array, so just push an element onto the stack
                                $arr[] = $this->decode($slice);
                            } elseif (SERVICES_JSON_IN_OBJ == reset($stk)) {
                                // we are in an object, so figure
                                // out the property name and set an
                                // element in an associative array,
                                // for now
                                $parts = [];

                                if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // "name":value pair
                                    $key = $this->decode($parts[1]);
                                    $val = $this->decode($parts[2]);

                                    if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    // name:value pair, where name is unquoted
                                    $key = $parts[1];
                                    $val = $this->decode($parts[2]);

                                    if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
                                        $obj[$key] = $val;
                                    } else {
                                        $obj->$key = $val;
                                    }
                                }
                            }
                        } elseif ((('"' === $chrs[$c]) || ("'" === $chrs[$c])) && (SERVICES_JSON_IN_STR != $top['what'])) {
                            // found a quote, and we are not inside a string
                            array_push($stk, ['what' => SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs[$c]]);
                            //print("Found start of string at {$c}\n");
                        } elseif (($chrs[$c] == $top['delim'])
                                  && (SERVICES_JSON_IN_STR == $top['what'])
                                  && (1 != (mb_strlen(mb_substr($chrs, 0, $c)) - mb_strlen(rtrim(mb_substr($chrs, 0, $c), '\\'))) % 2)) {
                            // found a quote, we're in a string, and it's not escaped
                            // we know that it's not escaped becase there is _not_ an
                            // odd number of backslashes at the end of the string so far
                            array_pop($stk);
                            //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");
                        } elseif (('[' === $chrs[$c])
                                  && in_array($top['what'], [SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ], true)) {
                            // found a left-bracket, and we are in an array, object, or slice
                            array_push($stk, ['what' => SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false]);
                            //print("Found start of array at {$c}\n");
                        } elseif ((']' === $chrs[$c]) && (SERVICES_JSON_IN_ARR == $top['what'])) {
                            // found a right-bracket, and we're in an array
                            array_pop($stk);
                            //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        } elseif (('{' === $chrs[$c])
                                  && in_array($top['what'], [SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ], true)) {
                            // found a left-brace, and we are in an array, object, or slice
                            array_push($stk, ['what' => SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false]);
                            //print("Found start of object at {$c}\n");
                        } elseif (('}' === $chrs[$c]) && (SERVICES_JSON_IN_OBJ == $top['what'])) {
                            // found a right-brace, and we're in an object
                            array_pop($stk);
                            //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        } elseif (('/*' === $substr_chrs_c_2)
                                  && in_array($top['what'], [SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ], true)) {
                            // found a comment start, and we are in an array, object, or slice
                            array_push($stk, ['what' => SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false]);
                            ++$c;
                            //print("Found start of comment at {$c}\n");
                        } elseif (('*/' === $substr_chrs_c_2) && (SERVICES_JSON_IN_CMT == $top['what'])) {
                            // found a comment end, and we're in one now
                            array_pop($stk);
                            ++$c;

                            for ($i = $top['where']; $i <= $c; ++$i) {
                                $chrs = substr_replace($chrs, ' ', $i, 1);
                            }
                            //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
                        }
                    }

                    if (SERVICES_JSON_IN_ARR == reset($stk)) {
                        return $arr;
                    }

                    if (SERVICES_JSON_IN_OBJ == reset($stk)) {
                        return $obj;
                    }
                }
        }
    }

    /**
     * @param      $data
     * @param null $code
     * @return bool
     * @todo Ultimately, this should just call PEAR::isError()
     */
    public function isError($data, $code = null): bool
    {
        if (class_exists('pear')) {
            return PEAR::isError($data, $code);
        }

        if (is_object($data)
            && ($data instanceof \services_json_error
                || $data instanceof \services_json_error)) {
            return true;
        }

        return false;
    }
}

if (class_exists('PEAR_Error')) {
    /**
     * Class ServicesJSON_Error
     */
    class ServicesJSON_Error extends PEAR_Error
    {
        /**
         * ServicesJSON_Error constructor.
         * @param string $message
         * @param null   $code
         * @param null   $mode
         * @param null   $options
         * @param null   $userinfo
         */
        public function __construct(
            $message = 'unknown error',
            $code = null,
            $mode = null,
            $options = null,
            $userinfo = null
        ) {
            parent::__construct($message, $code, $mode, $options, $userinfo);
        }
    }
} else {
    /**
     * @todo Ultimately, this class shall be descended from PEAR_Error
     */
    class ServicesJSON_Error
    {
        /**
         * ServicesJSON_Error constructor.
         * @param string $message
         * @param null   $code
         * @param null   $mode
         * @param null   $options
         * @param null   $userinfo
         */
        public function __construct(
            $message = 'unknown error',
            $code = null,
            $mode = null,
            $options = null,
            $userinfo = null
        ) {
        }
    }
}
