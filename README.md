# Crud-Tester
Easy make a validator for cruds in Laravel

## Configuration
### Parameters
- *module* (Necessary) (String): Name of the module
- *module_object* (Necessary) (Object): The object of the module
- *validate_relationship* (Boolean): If you want to validate relationship (Default: True)
- *others_can_see* (Boolean): If you want to be validated that another users cannot see the information (Default: false)
- *url_base* (URL): Url to check the CRUD (Default: 'admin/{module}')
- *object_relationship* (Necessary if validate_relationship is true) (String): To validate that object was successful changed
- *function_relationship* (Necessary if validate_relationship is true) (String): Function to review if object exists
- *redirect_to_index* (Boolean): If enabled when saving should redirect to index page (Default: True)

#### Others Can See
To configurate this option will be needed to create a $this->another_user in the test.
