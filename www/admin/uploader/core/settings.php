<?php

class NeuSettings{
	static private $options = array();

	public static function write( $options ){
		if (func_num_args()===2 && is_string(func_get_arg(0))) {
			$key = func_get_arg(0);
			$val = func_get_arg(1);

			self::$options[$key] = $val;
		} else {
			self::$options = $options;
		}
	}

	public static function read( $key ){
		if( array_key_exists( $key, self::$options ) ) return self::$options[$key];
		else return null;
	}
}