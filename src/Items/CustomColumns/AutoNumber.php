<?php

namespace Exceedone\Exment\Items\CustomColumns;

use Exceedone\Exment\Items\CustomItem;
use Exceedone\Exment\Form\Field;
use Exceedone\Exment\Model\Define;
use Carbon\Carbon;

class AutoNumber extends CustomItem 
{
    protected $required = false;

    protected function getAdminFieldClass(){
        return Field\Display::class;
    }

    public function getAutoNumber(){
        // already set value, break
        if(isset($this->value)){
            return null;
        }
        
        $options = $this->custom_column->options;
        if (!isset($options)) {
            return null;
        }
        
        if (array_get($options, 'auto_number_type') == 'format') {
            return $this->createAutoNumberFormat($options);
        }
        // if auto_number_type is random25, set value
        elseif (array_get($options, 'auto_number_type') == 'random25') {
            return make_licensecode();
        }
        // if auto_number_type is UUID, set value
        elseif (array_get($options, 'auto_number_type') == 'random32') {
            return make_uuid();
        }

        return null;
    }
    
    /**
     * Create Auto Number value using format.
     */
    protected function createAutoNumberFormat($options)
    {
        // get format
        $format = array_get($options, "auto_number_format");
        // get value
        $value = getModelName($this->custom_column->custom_table)::find($this->id);
        $auto_number = replaceTextFromFormat($format, $value);
        return $auto_number;
    }
}
