<?php
namespace Magomogo\Persisted;

use Mockery as m;
use Magomogo\Persisted\Container\SqlDb;
use Magomogo\Persisted\Container\Memory;
use Magomogo\Persisted\Test\DbNames;
use Magomogo\Persisted\Test\ObjectMother;
use Magomogo\Persisted\Test\Person;

class AbstractPropertiesTest extends \PHPUnit_Framework_TestCase
{
    public function testIsIterable()
    {
        $names = array();
        $properties = array();
        foreach (self::properties() as $name => $property) {
            $names[] = $name;
            $properties[] = $property;
        }

        $this->assertEquals(array('title', 'description', 'object', 'nullDefault'), $names);
        $this->assertEquals(array('default title', 'default descr', new \stdClass(), null), $properties);
    }

    public function testIdIsNullInitially()
    {
        $this->assertNull(self::properties()->id(m::mock()));
    }

    public function testPersistedMessageSetsId()
    {
        $bag = self::properties();
        $bag->persisted('888', m::mock());
        $this->assertEquals('888', $bag->id(m::mock()));
    }

    public function testPropertiesCanHaveDifferentIdsInDifferentContainers()
    {
        $bag = self::properties();
        $container1 = new SqlDb(m::mock(), new DbNames());
        $container2 = new Memory();

        $bag->persisted('888', $container1);
        $bag->persisted('2342-klsjdf94', $container2);

        $this->assertEquals('888', $bag->id($container1));
        $this->assertEquals('2342-klsjdf94', $bag->id($container2));
    }

    public function testRejectsNotConfiguredProperties()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        self::properties()->not_configured = 12;
    }

    public function testAllowsDefineAPropertyHavingNullDefaultValue()
    {
        $properties = self::properties();
        $this->assertNull(self::properties()->nullDefault);
        $properties->nullDefault = 1;
        $this->assertEquals(1, $properties->nullDefault);
    }

    public function testPropertiesCanBeCopiedToAnotherProperties()
    {
        $properties = ObjectMother\Person::maximProperties();
        $properties->persisted('1', m::mock('Magomogo\\Persisted\\Container\\SqlDb'));
        $properties->persisted('123123132', m::mock('Magomogo\\Persisted\\Container\\Memory'));

        $anotherProperties = new Person\Properties();
        $properties->copyTo($anotherProperties);

        $this->assertNotSame($properties, $anotherProperties);
        $this->assertEquals($properties, $anotherProperties);
    }

    public function testCloneCreatesEqualProperties()
    {
        $bag = self::properties();
        $this->assertEquals($bag, clone $bag);
    }

    public function testIssetMagicMethod()
    {
        $this->assertTrue(isset(self::properties()->title));
        $this->assertTrue(isset(self::properties()->nullDefault));
        $this->assertFalse(isset(self::properties()->not_existing));
    }

    public function testPersistencyCanBeReset()
    {
        $bag = self::properties();
        $bag->persisted(88, new \stdClass());
        $this->assertEquals(88, $bag->id(new \stdClass()));
        $this->assertNull($bag->resetPersistency()->id(new \stdClass()));
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function properties()
    {
        return new TestProperties();
    }

}

//======================================================================================================================

class TestProperties extends AbstractProperties
{

    protected function properties()
    {
        return array(
            'title' => 'default title',
            'description' => 'default descr',
            'object' => new \stdClass(),
            'nullDefault' => null
        );
    }
}