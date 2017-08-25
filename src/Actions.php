<?php

namespace WeblaborMX\CrudTester;

trait Actions {

    use Helper;

    /**
     * Tests in order of appearence
     */

    // Using in all
    public function redirects_if_not_logged($action) {
        if(!$this->configuration['must_be_logged'])
            return;

        $url = $this->getUrl($action);
        $this->visit($url);
        try {
            $this->seePageIs('login');
        } catch (\Exception $e) {
            $this->fail("Must redirect if is not logged");
        }
        
        echo "\n- redirects_if_not_logged in $url checked\n";
    }

    // Using in add and update
    public function all_inputs_are_in_the_form($action) {
        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $this->visit($url);

        foreach ($this->fields as $key => $array) {
            if(!in_array($action, $array['show']))
                continue;
            try {
                $this->see('name="'.$key.'"');
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("$key field doesnt exist in $url");
            }
            echo "\n-- '$key' exists in $url ready";
        }
        echo "\n\n- all_inputs_are_in_the_form in $url checked\n";
    }

    // Using in add and update
    public function validate_options_are_shown($action) {
        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);
        foreach ($this->fields as $key => $array) {
            if(isset($array['options']) && is_array($array['options'])) {
                foreach ($array['options'] as $key => $option) {
                    try {
                        $this->see('option value="'.$key.'"');
                        $this->see($option.'</option>');
                    } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                        $this->fail("$key need to have $option as option in $url");
                    }
                   
                    echo "\n-- check that '$option' is a posible value of $key ready";
                }
            }
        }
        echo "\n\n- validate_options_are_shown in $url checked\n";
    }

    // Using in add
    public function add_show_successfull_mesagge_and_is_saved($action, $user_case) {
        if( $this->configuration['validate_relationship']) {
            $count = $this->{$this->configuration['object_relationship']}->{$this->configuration['function_relationship']}()->count();
            $count++;
        }

        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);
        foreach ($user_case as $key => $value) {
            $this->getInputMethod($key, $value);
        }
        $this->press('Crear');
        $this->see('Creado correctamente');
       
        
        if(
            $this->configuration['redirect_to_index']) 
        {
            try {
               $this->seePageIs($this->getUrl('index'));
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("When saving should redirect to index page");
            }
        }
            
             
        echo "\n-- successfull message is shown ready";

        if( $this->configuration['validate_relationship'])
            $this->assertEquals($count, $this->{$this->configuration['object_relationship']}->{$this->configuration['function_relationship']}()->count());
        
        echo "\n-- cheking that object was created successfully ready";

        echo "\n\n- add_show_successfull_mesagge_and_is_saved in $url checked\n";
    }

    // Using in add and update
    public function form_mark_error_if_empty($action, $user_cases = []) {
        if(!$this->exist_required_fields_that_arent_selects())
            return;
        $button = $action=='add' ? 'Crear' : 'Editar';

        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);

        if($action=='update') {
            foreach ($user_cases as $key => $array) {
                $this->getInputMethod($key, $array, true);
            }
        }

        $this->press($button);

        try {
           $this->see('Los siguientes errores fueron encontrados');
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->fail("'Los siguientes errores fueron encontrados' text should appear if the fields are empty in $url");
        }

        echo "\n- form_mark_error_if_empty in $url checked\n";
    }

    // Using in add and update
    public function if_a_required_field_is_empty_must_show_error($action, $user_case) {
        if(!$this->exist_required_fields_that_arent_selects())
            return;
        $button = $action=='add' ? 'Crear' : 'Editar';

        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $required_fields = $this->get_required_fields();

        foreach ($required_fields as $key_parent => $value_parent) {    
            $this->visit($url);
            foreach ($required_fields as $key => $array) {
                $value = isset($user_case[$key]) ? $user_case[$key] : null;
                if($key != $key_parent) {
                    $this->getInputMethod($key, $value);
                } else if($action=='update') {
                    $this->getInputMethod($key, $value, true);
                }
            }
            // Show error message
            $this->press($button);
            try {
                $this->see('Los siguientes errores fueron encontrados');
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("'Los siguientes errores fueron encontrados' text should be shown without $key_parent field in $url");
            }

            echo "\n-- the form mark error without $key_parent in $url ready";

            try {
                $this->see($value_parent['title'].' es requerido');
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("'".$value_parent['title']." es requerido' text should be shown in $url");
            }

            echo "\n-- the form shows that $key_parent is required in $url ready";
        }

        echo "\n\n- if_a_required_field_is_empty_must_show_error in $url checked\n";
    }

    // Using in update
    public function check_if_another_user_cannot_enter($action) {
        
        if ($this->configuration['others_can_see'])
            return;

        $user = $this->another_user;
        
        if($this->configuration['inside_store']) {
            $user = $this->createUser(['store_id' => $this->store->id]);
        }

        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($user);

        $this->get($url);
        try {
            $this->assertFalse($this->response->isOk());
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->fail("another user must not access to $url");
        }
            

         echo "\n- check_if_another_user_cannot_enter in $url checked\n";
    }

    // Using in update
    public function update_show_successfull_mesagge_and_is_saved($action, $user_cases) {
        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $this->visit($url);
        foreach ($user_cases as $key => $value) {
            $this->getInputMethod($key, $value);
        }

        $this->press('Editar');

        try {
           $this->see('Editado correctamente');
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->fail("'Editado correctamente' text should appear after submit in $url");
        }
        echo "\n-- successfull message is shown ready";

        $this->seePageIs($url);

        $new_user_cases = $this->get_user_cases_that_arent_selects($user_cases);
        foreach ($new_user_cases as $key => $value) {
            try {
               $this->see($value);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("'$value' was not shown in $url ");
            }
        }
        echo "\n-- cheking that object was created successfully ready";

        echo "\n\n- update_show_successfull_mesagge_and_is_saved in $url checked\n";
    }

    // Using in remove
    public function remove_show_successfull_mesagge_and_is_saved($action) {
        if( !$this->configuration['validate_relationship'])
            return;
        $count = $this->{$this->configuration['object_relationship']}->{$this->configuration['function_relationship']}()->count();
        $count--;

        $this->call('DELETE', $action, []);

        /*$url = $this->getUrl($action);
        $this->actingAs($this->user);
        $this->visit($url);
        $this->press('Eliminar')
             ->see('Eliminado correctamente');
        echo "\n-- successfull message is shown ready";*/

        $this->assertEquals($count, $this->{$this->configuration['object_relationship']}->{$this->configuration['function_relationship']}()->first()->active);
        echo "\n-- cheking that object was created successfully ready";

        echo "\n\n- remove_show_successfull_mesagge_and_is_saved checked\n";
    }

    // Using in show
    public function check_that_all_fields_are_shown($action) {
        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $this->visit($url);

        foreach ($this->fields as $field => $array) {
            if(!$array['required'])
                continue;
            $value = $this->configuration['module_object']->$field;
            if(isset($array['options'][$value]))
                $value = $array['options'][$value];
            try {
                $this->see($value);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                if(!is_numeric($value))
                    $this->fail("'{$value}' text ({$field}) should appear in $url");
                try {
                    $value = number_format($value, 0);
                    $this->see($value);
                } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                    $this->fail("'{$value}' text ({$field}) should appear in $url");
                }
            }
            echo "\n-- checking that $field is in $url ready";
        }

        echo "\n\n- check_that_all_fields_are_shown in $url checked\n";
    }

    // Using in show
    public function check_attributes_are_used($action, $attributes) {
        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $this->visit($url);

        foreach ($attributes as $attribute) {
            try {
                $this->see($this->configuration['module_object']->$attribute);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                if(!is_numeric($attribute))
                    $this->fail("'{$attribute}' value should appear in $url");
                try {
                    $attribute = number_format($attribute, 0);
                    $this->see($attribute);
                } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                    $this->fail("'{$attribute}' value should appear in $url");
                }
            }
            echo "\n-- checking that $attribute is in $url ready";
        }

        echo "\n\n- check_attributes_are_used in $url checked\n";
    }

    // Using in show
    public function check_relationships_on_view($action, $relationship) {
        $url = $this->getUrl($action);
        
        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);
        
        $this->visit($url);

        foreach ($relationship as $relationship => $fields) {
            if(!is_array($fields)) {
                $relationship = $fields;
                $fields = [];
            }
            if($this->configuration['module_object']->$relationship()->count() <= 0)
                $this->fail("'{$relationship}' relationship need to have data to test fields");
            try {
                $this->see('<div role="tabpanel" class="tab-pane" id="'.$relationship.'">');
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                try {
                    $this->see('<div role="tabpanel" class="tab-pane active" id="'.$relationship.'">');
                } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                    $this->fail("'{$relationship}' relationship should be added in $url");
                }
            }
            foreach ($fields as $field) {
                $value = $this->configuration['module_object']->$relationship()->first()->$field;
                $this->see($value);
            }
            echo "\n-- checking that $relationship relationship is in $url ready";
        }

        echo "\n\n- check_relationships_on_view in $url checked\n";
    }

    // Using in show
    public function check_that_a_specified_function_object_is_shown($action) {
        $functions = $this->get_functions_of_relationships();
        $this->add_relationship_objects();

        if(count($functions) == 0)
            return;

        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);
    
        foreach ($functions as $function => $fields) {
            foreach ($this->configuration['module_object']->$function()->get() as $object) {
                foreach ($fields as $field) {
                    try {
                       $this->see($object->$field);
                    } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                        $this->fail("'".$object->$field."' text ({$function}->{$field}) should appear in $url");
                    }
                    echo "\n-- checking that $field of $function is in $url ready";
                }
            }
        }

        echo "\n\n- check_that_a_specified_function_object_is_shown in $url checked\n";
    }

    // Using in index
    public function the_links_are_shown($action, $actions) {
        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);
    
        foreach ($actions as $action) {
            try {
               $this->see($this->getUrl($action, null, null, true));
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("'".$this->getUrl($action)."' url ({$action}) should appear in $url");
            }           
            echo "\n-- checking that $action link is in $url ready";
        }

        echo "\n\n- the_links_are_shown in $url checked\n";
    }

    // Using in index
    public function the_columns_exist($action, $columns) {
        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->user);

        $this->visit($url);
    
        foreach ($columns as $column) {
            try {
               $this->see($column);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("'{$column}' text should appear in $url");
            } 
            echo "\n-- checking that $column column is in $url ready";
        }

        echo "\n\n- the_columns_exist in $url checked\n";
    }

    // Using in index (?? review)
    public function another_user_cannot_see_the_object($action) {
        
        if ($this->configuration['others_can_see'])
            return;

        $url = $this->getUrl($action);

        if($this->configuration['must_be_logged'])
            $this->actingAs($this->another_user);

        $this->visit($url);
        try {
            $this->dontSee($this->getUrl('show'));    
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->fail("Another user should not see the object.");
        } 
        

        echo "\n- another_user_cannot_see_the_object in $url checked\n";
    }

    // Using in add
    public function check_that_relationship_was_added_correctly($action, $user_cases) {
        $url = $this->getUrl($action);
        $functions = $this->get_fields_with_function_of_relationships();
        if(count($functions)==0)
            return;

        if( !$this->configuration['validate_relationship'])
            return; 

        $objects = $this->{$this->configuration['object_relationship']}->{$this->configuration['function_relationship']}();
        $last = $objects->orderBy('id', 'DESC')->first();
        
        foreach ($user_cases as $name => $value) {
            if (!isset($functions[$name]))
                continue;

            $function = $functions[$name];
            if(!is_array($value))
                $value = [$value];

            try {
                $this->assertEquals($value, $last->$function()->pluck($function.'.id')->toArray());
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->fail("The values of '{$name}' should be saved in $url");
            } 
            
            echo "\n-- checking that $name object is correct ready";
        }
            
        echo "\n\n- check_that_relationship_was_added_correctly checked\n";
    }

}

