<?php

namespace WeblaborMX\CrudTester;

trait Helper {

    public function getUrl($action, $id =  null, $store_id = null) {
        if(is_null($id))
            $id = $this->configuration['module_object']->id;
        if(is_null($store_id) && $this->configuration['inside_store'])
            $store_id = $this->store->id;

        $url = $this->configuration['url_base'];
        $url = str_replace('{module}', $this->configuration['module'], $url);
        $url = str_replace('{store}', $store_id, $url);
        
        switch ($action) {
            case 'add':
                $url .= 'create';
                break;
            case 'update':
                $url .= '{id}/edit';
                break;
            case 'remove':
                $url .= '{id}';
                break;
            case 'show':
                $url .= '{id}';
                break;
            case 'index':
                $url .= '';
                break;
        }
        $url = str_replace('{id}', $id, $url);
        return $url;
    }

    public function getInputMethod($key, $value, $empty = false) {
        $array = $this->fields[$key];
        if(!is_null($value))
            $array['value'] = $value;

        if(!isset($array['value']) && !$empty)
            return;
        if($empty && $array['type'] == 'text')
            $array['value'] = '';

        if($array['type']=='text') {
            $this->type($array['value'], $key);    
        } else if($array['type']=='select') {
            $this->select($array['value'], $key);    
        } else if($array['type']=='checkbox') {
            if ($array['value']) {
                $this->check($key);    
            } else {
                $this->uncheck($key);    
            }
        }
    }

    public function get_required_fields() {
        $required_fields = [];
        foreach ($this->fields as $key => $array) {
            if($array['type']=='select')
                continue;
            if($array['required'])
                $required_fields[$key] = $array;
        }
        return $required_fields;
    }

    public function get_functions_of_relationships() {
        $functions = [];
        foreach ($this->fields as $key => $array) {
            if(!isset($array['relationship_function']))
                continue;

            $atributes = $array['relationship_attributes'];
            if (!is_array($atributes))
                $atributes = [$atributes];
            
            $functions[$array['relationship_function']] = $atributes;
        }
        return $functions;
    }

    public function add_relationship_objects() {
        foreach ($this->fields as $key => $array) {
            if(!isset($array['relationship_function']))
                continue;

            // Add objects in function
            $function = ucfirst($array['relationship_module']);
            $function = 'create'.$function;
            $object = $this->$function();

            $this->configuration['module_object']->{$array['relationship_function']}()->save($object);
        }
    }

    public function get_fields_with_function_of_relationships() {
        $functions = [];
        foreach ($this->fields as $key => $array) {
            if(!isset($array['relationship_function']))
                continue;
            
            $functions[$key] = $array['relationship_function'];
        }
        return $functions;
    }

    public function exist_required_fields_that_arent_selects() {
        foreach ($this->fields as $key => $array) {
            if(!$array['required'])
                continue;
            if($array['type']=='select')
                continue;
            return true;
        }
        return false;
    }

    public function get_user_cases_that_arent_selects($user_cases){
        $user_new = [];
        foreach ($user_cases as $key => $value) {
            if($this->fields[$key]['type'] == 'select')
                continue;
            $user_new[$key] = $value;
        }
        return $user_new;
    }

}

