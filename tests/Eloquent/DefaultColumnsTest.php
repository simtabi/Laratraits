<?php

namespace Tests\Eloquent;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use DarkGhostHunter\Laratraits\Eloquent\DefaultColumns;

class DefaultColumnsTest extends TestCase
{
    protected function setUp() : void
    {
        $this->afterApplicationCreated(function () {
            Schema::create('test_table', function (Blueprint $blueprint) {
                $blueprint->increments('id');
                $blueprint->string('foo');
                $blueprint->string('bar');
                $blueprint->string('quz');
                $blueprint->string('qux');
                $blueprint->timestamps();
            });

            for ($i = 0; $i < 10; ++$i) {
                DB::table('test_table')->insert([
                    'foo' => $i *2,
                    'bar' => $i *3,
                    'quz' => $i *4,
                    'qux' => $i *5,
                ]);
            }
        });

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function test_adds_default_columns()
    {
        $model = new class extends Model {
            use DefaultColumns;

            protected $table = 'test_table';

            protected static $defaultColumns = ['bar', 'quz'];
        };

        $model->all()->each(function ($model) {
            $this->assertNull($model->foo);
            $this->assertNull($model->qux);
            $this->assertNotNull($model->bar);
            $this->assertNotNull($model->quz);
        });
    }

    public function test_overrides_select()
    {
        $model = new class extends Model {
            use DefaultColumns;

            protected $table = 'test_table';

            protected static $defaultColumns = ['bar', 'quz'];
        };

        $model->select('qux')->get()->each(function ($model) {
            $this->assertNull($model->foo);
            $this->assertNotNull($model->qux);
            $this->assertNull($model->bar);
            $this->assertNull($model->quz);
        });
    }

    public function test_doesnt_adds_defaults_if_empty()
    {
        $model = new class extends Model {
            use DefaultColumns;

            protected $table = 'test_table';
        };

        $model->all()->each(function ($model) {
            $this->assertNotNull($model->foo);
            $this->assertNotNull($model->qux);
            $this->assertNotNull($model->bar);
            $this->assertNotNull($model->quz);
        });
    }

    public function test_without_default_columns()
    {
        $model = new class extends Model {
            use DefaultColumns;

            protected $table = 'test_table';
        };

        $model->withoutDefaultColumns()->get()->each(function ($model) {
            $this->assertNotNull($model->foo);
            $this->assertNotNull($model->qux);
            $this->assertNotNull($model->bar);
            $this->assertNotNull($model->quz);
        });
    }
}
