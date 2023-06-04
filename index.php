<?php
use BadMethodCallException as wut;
use function array_key_exists as exists;
abstract class Proto {
    protected $members = ['props' => [], 'func' => []];
    function __construct() { $this->init(); }
    final protected function init() {
        foreach ($this->doInit() as $name => $member) {
            is_callable($member) ? ($pointer = &$this->members['func']) : ($pointer = &$this->members['props']);
            $pointer[$name] = $member;
        }
        unset($pointer);
    }
    abstract protected function doInit();
    function __call($name, $arguments) {
        if (!isset($this->members['func'][$name])) {
            throw new wut("Undefined method {$name}.");
        }
        return call_user_func_array($this->members['func'][$name], $arguments);
    }
    function __get($name) {
        if (!exists($name, $this->members['props'])) {
            throw new wut("Undefined property {$name}");
        }
        return $this->members['props'][$name];
    }
    function __set($name, $value) {
        if (!exists($name, $this->members['props'])) {
            throw new wut("Undefined property {$name}");
        }
        $this->members['props'][$name] = $value;
    }
}
class Item extends Proto {
    protected function doInit() {
        return [
                'id' => null,
                'name' => null,
                'getId' => fn() => $this->id,
                'getName' => fn() => $this->name,
                'setId' => function ($id) { $this->id = $id; },
                'setName' => function ($name) { $this->name = $name; }
        ];
    }
}
class Collection extends Proto {
    protected function doInit() {
        return [
                'items' => new ArrayObject,
                'attach' => function ($item) { $this->items[] = $item; },
                'map' => fn ($mapper) => array_map($mapper, $this->items->getArrayCopy())
        ];
    }
}
$c = new Collection();
for ($i = 0; $i < 3; $i += 0) {
    $item = new Item();
    $item->setId(++$i);
    $item->setName("Name #{$item->getId()}");
    $c->attach($item);
}

foreach ($c->map(fn ($i) => $i->getName()) as $name) {
    var_dump($name);
}
