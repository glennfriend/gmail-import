<?php

class BaseObject
{
    /**
     *  store variable
     */
    protected $store = array();

    /**
     *  construct
     */
    public function __construct()
    {
        $this->resetValue();
    }

    /**
     *  reset value
     */
    public function resetValue()
    {
        $this->__sleep();
        foreach( $this->getTableDefinition() as $key => $item ) {
            if( isset($item['value']) ) {
                $this->_baseSet( $key, $item['value'] );
            }
            else {
                $this->_baseSet( $key, null );
            }
        }
    }

    /**
     *  請依照 table 正確覆寫該 field 內容
     *  @return array()
     */
    public static function getTableDefinition()
    {
        return array(); // 必須覆寫
    }

    /**
     *  不予許呼叫的 method name
     *  @return array()
     */
    public static function getDisabledMethods()
    {
        return array(); // 選擇性覆寫
    }

    /**
     *  clear extends information
     */
    public function __sleep()
    {
        return array('store');
    }

    /**
     *  magic call getting and setting
     */
    public function __call($name, $args)
    {
        // disabled methods
        foreach( $this->getDisabledMethods() as $disabledMethodName ) {
            if ( $name === $disabledMethodName ) {
                throw new Exception("Disable {$disabledMethodName}() method");
            }
        }

        $tableDefinition = $this->getTableDefinition();

        // preserve methods
        switch ($name) {
            case 'getProperty':
                if (array_key_exists('properties', $tableDefinition)) {
                    return $this->_baseGetProperty($args);
                }
            break;
            case 'setProperty':
                if (array_key_exists('properties', $tableDefinition)) {
                    $this->_baseSetProperty($args);
                    return;
                }
            break;
        }

        // getting and setting
        if ( 'get'===substr($name,0,3) ) {

            if ( $args ) {
                throw new Exception("{$name}() getting arguments error at BaseObject");
            }
            foreach( $tableDefinition as $key => $item ) {
                if ( 'get'.ucfirst($key) === $name ) {
                    return $this->_baseGet($key);
                }
            }
            throw new Exception("{$name}() getting method error at BaseObject");

        }
        elseif ( 'set'===substr($name,0,3) ) {

            if ( 1 != count($args) ) {
                throw new Exception("{$name}() setting arguments error at BaseObject");
            }
            foreach( $tableDefinition as $key => $item ) {
                if ( 'set'.ucfirst($key) === $name ) {
                    $this->_baseSet($key, $args[0]);
                    return;
                }
            }
            throw new Exception("{$name}() setting method error at BaseObject");

        }

        throw new Exception("{$name}() method error at BaseObject");
        exit;

    }

    /**
     *  get method
     */
    private function _baseGet($key)
    {
        $fields = $this->getTableDefinition();
        $fieldName = $fields[$key]['field'];
        return $this->store[ $fieldName ];
    }

    /**
     *  set method
     */
    private function _baseSet($key, $value)
    {
        $fields = $this->getTableDefinition();
        $fieldName = $fields[$key]['field'];
        $this->store[ $fieldName ] = $value;

        foreach( $fields[$key]['filters'] as $functionName ) {
            $method = 'filter_' . $functionName;
            $this->store[ $fieldName ] = $this->$method( $this->store[ $fieldName ] );
        }
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        priveate filter methods
    ------------------------------------------------------------------------------------------------------------------------ */

    // trim
    private function filter_trim( $value )
    {
        return trim($value);
    }

    // strip_tags
    private function filter_strip_tags( $value )
    {
        return strip_tags($value);
    }

    // strtolower
    private function filter_strtolower( $value )
    {
        return strtolower($value);
    }

    // strtoupper
    private function filter_strtoupper( $value )
    {
        return strtoupper($value);
    }

    // intval
    private function filter_intval( $value )
    {
        return (int) $value;
    }

    // floatval
    private function filter_floatval( $value )
    {
        return (float) $value;
    }

    // filter date
    private function filter_dateval( $value )
    {
        $value = intval($value);
        if (!$value) {
            return 0;
        }
        return $value;
    }

    // 代入值如果不是 array, 會輸出 empty array, 否則就傳回原代入值
    private function filter_arrayval($value)
    {
        if ( is_array($value) ) {
            return $value;
        }
        return array();
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        priveate extened methods
    ------------------------------------------------------------------------------------------------------------------------ */

    /**
     *  properties extened
     *  set property
     *
     *  example:
     *      setProperty('name','guest') -> array['name'], 若無值, 則傳回 'guest' string
     *
     */
    private function _baseSetProperty( $args )
    {
        if ( !isset($args[0]) || !isset($args[1]) || isset($args[2]) ) {
            throw new Exception("getProperty() getting arguments error at BaseObject");
        }
        $key   = $args[0];
        $value = $args[1];

        if ( !preg_match('/^[a-zA-Z0-9\_]+$/i',$key) ) {
            return;
        }

        $properties = $this->getProperties();
        if ( null !== $value ) {
            $properties[$key] = $value;
        }
        else {
            unset( $properties[$key] );
        }
        $this->setProperties( $properties );
    }


    /**
     *  properties extened
     *  get property
     *
     *  example:
     *      getProperty('name')         -> array['name'], 若無值, 則傳回 null
     *      getProperty('name','guest') -> array['name'], 若無值, 則傳回 'guest' string
     *
     *  example:
     *      getProperty('vivian.age')   -> array['vivian']['age']
     *      getProperty('vivian.0')     -> array['vivian'][0]
     *
     *  @param string
     */
    private function _baseGetProperty( $args )
    {
        if ( !isset($args[0]) ) {
            throw new Exception("getProperty() getting arguments error at BaseObject");
        }
        $key = $args[0];

        if ( !isset($args[1]) ) {
            $defaultValue = null;
        }
        else {
            $defaultValue = $args[1];
        }

        if ( isset($args[2]) ) {
            throw new Exception("getProperty() getting arguments error at BaseObject");
        }

        $key = trim( (string) $key );
        $items = explode( '.', $key );
        if( !is_array($items) ) {
            return $defaultValue;
        }

        $result = $this->getProperties();
        foreach( $items as $index ) {
            if ( !preg_match('/^[a-z0-9\_]+$/i',$index) ) {
                return $defaultValue;
            }
            if ( !is_array($result) ) {
                return $defaultValue;
            }
            if ( !isset($result[$index]) ) {
                return $defaultValue;
            }
            $result = $result[$index];
        }
        return $result;
    }

}



