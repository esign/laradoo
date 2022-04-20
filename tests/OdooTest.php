<?php

use Esign\Laradoo\Odoo;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class OdooTest extends TestCase
{

    protected $odoo;

    /**
     * Demo credentials set.
     */
    protected $host;
    protected $db;
    protected $username;
    protected $password;

    public function __construct()
    {
        parent::__construct();

        $this->odoo = new Odoo();

        $this->setDemoCredentials();
        $this->createOdooInstance();
    }

    /**
     * Set odoo.com test credentials
     */
    protected function setDemoCredentials()
    {

        $info = $this->odoo->getClient('https://demo.odoo.com/start')->start();

        list($this->host, $this->db, $this->username, $this->password) =
            array($info['host'], $info['database'], $info['user'], $info['password']);

    }

    /**
     * Connect with the odoo and create the oddo instance.
     */
    protected function createOdooInstance()
    {
        $this->odoo = $this->odoo
            ->username($this->username)
            ->password($this->password)
            ->db($this->db)
            ->host($this->host)
            ->connect();
    }


    /** @test */
    public function get_odoo_version_as_collection()
    {
        $version = $this->odoo->version();

        $this->assertInstanceOf(Collection::class, $version);
    }

    /** @test */
    public function get_odoo_version_only_server_version()
    {
        $version = $this->odoo->version('server_version');

        $this->assertEquals('string', gettype($version));
    }



    /** @test */
    public function test_common_connection_odoo()
    {
        $this->assertEquals('integer', gettype($this->odoo->getUid()));

    }


    /**
     * @test
     */
    public function check_access_to_models()
    {
        $check = $this->odoo->can('read', 'res.partner');

        $this->assertTrue($check);
    }

    /**
     * @test
     */
    public function using_search_method()
    {
        $ids = $this->odoo
            ->where('is_company', '=', true)
            ->search('res.partner');

        $this->assertArrayNotHasKey('faultCode',$ids);
        $this->assertInstanceOf(Collection::class, $ids);
        $this->assertNotEmpty($ids);
    }

    /** @test */
    public function count_items()
    {
        $amount = $this->odoo->count('res.partner');

        $this->assertEquals('integer', gettype($amount));
    }

    /**
     * @test
     */
    public function get_limited_ids()
    {
        $ids = $this->odoo
            ->where('is_company', '=', true)
            ->limit(3)
            ->search('res.partner');

        $this->assertInstanceOf(Collection::class, $ids);
        $this->assertArrayNotHasKey('faultCode',$ids);
        $this->assertCount(3, $ids);
    }

    /** @test */
    public function retrieve_a_collection_only_with_field_name()
    {
        $models = $this->odoo
            ->where('is_company', true)
            ->limit(3)
            ->fields('name')
            ->get('res.partner');

        $this->assertArrayNotHasKey('email',$models->first());
        $this->assertArrayHasKey('name',$models->first());
        $this->assertInstanceOf(Collection::class, $models);
        $this->assertArrayNotHasKey('faultCode',$models);
        $this->assertCount(3, $models);

    }

    /** @test */
    public function get_fields_of_partner_model()
    {
        $fields = $this->odoo->fieldsOf('res.partner');

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertArrayNotHasKey('faultCode',$fields);
    }

    /**
     * @test
     */
    public function create_new_record()
    {
        $id = $this->odoo
            ->create('res.partner',['name' => 'John Odoo']);

        $this->assertEquals('integer', gettype($id));
    }

    /**
     * @test
     */
    public function delete_a_record()
    {
        $id = $this->odoo
            ->create('note.note',['name' => 'John Odoo']);

        $this->assertEquals('integer', gettype($id));

        $result = $this->odoo->deleteById('note.note',$id);

        $ids = $this->odoo
            ->where('id', $id)
            ->search('note.note');

        $this->assertTrue($ids->isEmpty());

        $this->assertEquals('boolean', gettype($result));
    }

    /** @test */
    public function delete_two_record()
    {
        $this->odoo
            ->create('note.note',['name' => 'John Odoo']);
        $this->odoo
            ->create('note.note',['name' => 'John Odoo']);

        $ids = $this->odoo
            ->where('name', 'John Odoo')
            ->search('note.note');

        $result = $this->odoo->deleteById('note.note',$ids);

        $ids = $this->odoo
            ->where('name', 'John Odoo')
            ->search('note.note');

        $this->assertTrue($ids->isEmpty());

        $this->assertEquals('boolean', gettype($result));
    }

    /** @test */
    public function delete_a_record_directly()
    {
        // Create a record
        $this->odoo->create('note.note',['name' => 'John Odoo']);

        // Delete it
        $result = $this->odoo->where('name', 'John Odoo')
            ->delete('note.note');

        $this->assertEquals('boolean', gettype($result));
    }

    /**
     * @test
     */
    public function update_record_with_new_name()
    {
        // Create a record
        $initId = $this->odoo->create('note.note',['name' => 'John Odoo']);

        //Update the name
        $updated = $this->odoo->where('name', 'John Odoo')
            ->update('note.note',['name' => 'John Odoo']);

        $this->assertTrue($updated);

        //Delete the record
        $result = $this->odoo->deleteById('note.note',$initId);

        $this->assertTrue($result);

    }

    /**
     * @test
     */
    public function using_call_directly()
    {
        $this->odoo
            ->create('note.note',['name' => 'John Odoo']);
        $this->odoo
            ->create('note.note',['name' => 'John Odoo 2']);
        $this->odoo
            ->create('note.note',['name' => 'John Odoo 3']);

        $amount = $this->odoo->call('note.note', 'search',[
            [
                ['name', 'LIKE', 'John Odoo%']
            ]
        ],[
            'offset'=>1,
            'limit'=>3
        ]);

        $this->assertCount(2,$amount);
    }
}