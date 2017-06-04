<?php

namespace WeblaborMX\CrudTester;

trait CrudTester {

    use Actions;

    private $configuration;
    private $fields;

    public function addConfiguration(Array $configuration) {
        
        // Make default configuration
        $default_configuration = [
            'inside_store' => false,
            'validate_relationship' => true,
            'others_can_see' => false,
            'url_base' => 'admin/{module}/',
            'redirect_to_index' => true
        ];
        $configuration = array_merge($default_configuration, $configuration);

        // Validation
        if(
            !isset($configuration['module']) ||
            !isset($configuration['module_object']) ||
            ($configuration['validate_relationship'] && !isset($configuration['object_relationship'])) ||
            ($configuration['validate_relationship'] && !isset($configuration['function_relationship']))
        )
            throw new \Exception("Error, necessary fields were not added.", 1);
        
        if(!$configuration['others_can_see'] && !isset($this->another_user))
            throw new \Exception('You should create a $this->another_user variable to use "others_can_see" option', 1);

        if(!isset($configuration['url_link']))
            $configuration['url_link'] = $configuration['url_base'];
        
        // Saving    
        $this->configuration = $configuration;

    }

    public function addFields(Array $fields) {
        $this->fields = $fields;
    }

    public function commonStart($action) {
        $this->redirects_if_not_logged($action);
        if ($this->configuration['inside_store'])
            $this->redirects_if_isnt_store($action);
    }

    /* modules parts */

    public function add($user_case) {
        $action = 'add';
        $this->commonStart($action);
        $this->all_inputs_are_in_the_form($action);
        $this->validate_options_are_shown($action);
        $this->add_show_successfull_mesagge_and_is_saved($action, $user_case);
        $this->check_that_relationship_was_added_correctly($action, $user_case);
        $this->form_mark_error_if_empty($action);
        $this->if_a_required_field_is_empty_must_show_error($action, $user_case);
    }

    public function update($user_case) {
        $action = 'update';
        $this->commonStart($action);
        $this->all_inputs_are_in_the_form($action);
        $this->validate_options_are_shown($action); 
        $this->update_show_successfull_mesagge_and_is_saved($action, $user_case);
        $this->check_that_relationship_was_added_correctly($action, $user_case);
        $this->form_mark_error_if_empty($action, $user_case);
        $this->if_a_required_field_is_empty_must_show_error($action, $user_case);

        if (!$this->configuration['others_can_see']) {
            $this->check_if_another_user_cannot_enter($action);
        }
    }

    public function remove() {
        $action = 'remove';
        $this->remove_show_successfull_mesagge_and_is_saved($action);

        if (!$this->configuration['others_can_see']) {
            $this->check_if_another_user_cannot_enter($action);
        }
    }

    public function show($functions = []) {
        $action = 'show';
        $this->commonStart($action);
        $this->check_that_all_fields_are_shown($action);
        $this->check_that_a_specified_function_object_is_shown($action);

        if (!$this->configuration['others_can_see']) {
            $this->check_if_another_user_cannot_enter($action);
        }
    }

    public function index($columns= [], $actions = []) {
        if(is_array($actions) && count($actions)==0) {
            $actions = ['show', 'update', 'remove', 'add'];
        } elseif (!is_array($actions)) {
            $actions = [];
        }

        $action = 'index';
        $this->commonStart($action);
        $this->the_links_are_shown($action, $actions);
        $this->the_columns_exist($action, $columns);
        
        if (!$this->configuration['others_can_see']) {
            $this->another_user_cannot_see_the_object($action);
        }

    }
}

