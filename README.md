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
- *url_link* (String): If the links in index should use another url format put it here. (Default: Same as url_base)
- *must_be_logged* (Boolean): If true the module is accessable only by logged users (Default: true)
- *links* (Array): If you want to customize the url of the CRUD use this option, available options are: add, update, remove, show, index. You can add customizable options too.
Default options are: 
```php
[
	'add' => 'create', 			// GET
	'update' => '{id}/edit',	// GET
	'remove' => '{id}',			// REMOVE
	'show' => '{id}',			// GET
	'index' => '',				// GET
	'default' => '{id}/'.$action,	// GET
]
```
#### Others Can See
To configurate this option will be needed to create a $this->another_user in the test.

### Example code
```php
use WeblaborMX\CrudTester\CrudTester;

class ClientTest
{
	use CrudTester;

	public function setUp() {

		parent::setUp();

		// Create the elements to use and the environment
		$this->client = $this->create_client();
		$this->account = $this->tm_account;
		$this->user = $this->account->user;
		$this->another_user = $this->create_user();

		// Add configuration
		$array = [
			'module' => 'client',
			'module_object' => $this->client,
			'object_relationship' => 'account',
			'function_relationship' => 'clients'
		];
		$this->addConfiguration($array);

		// Add fields configuration
		$this->addFields($this->getFields());
	}

	public function getFields() {
		return [
			'name' => [
				'type' => 'text',
				'required' =>  true ,
				'title' => 'Nombre',
			],
			'phone' => [
				'type' => 'text',
				'required' =>  false ,
				'title' => 'Celular',
			],
			'business' => [
				'type' => 'text',
				'required' =>  false ,
				'title' => 'Negocio',
			],
			'email' => [
				'type' => 'text',
				'required' =>  false ,
				'title' => 'Correo Electrónico',
			]
		];
	}

	/** @test */
	public function i_can_see_the_list_of_my_clients()
	{
		$columns = ['Nombre','Negocio','Correo Electrónico']; // Columns to show
		$actions = ['add', 'update']; // What links should it has?
		$this->index($columns, $actions);
	}

	/** @test */
	public function you_can_add_clients()
	{
		// Example data to add
		$user_case = [
			'name' => 'Carlos Escobar',
			'business' => 'Weblabor',
			'email' => 'skalero01@gmail.com',
		];
		$this->add($user_case);
	}

	/** @test */
	public function i_can_see_my_clients()
	{
		$this->show();
	}

	/** @test */
	public function i_can_edit_a_client()
	{
		// Example data to edit
		$user_case = [
			'name' => 'Carlos Eli Escobar Ruiz',
			'email' => 'skalero02@gmail.com',
			'phone' => '061234923'
		];
		$this->update($user_case);
	}

	/** @test */
	public function i_can_remove_a_client()
	{
		$this->remove();
	}

}
```