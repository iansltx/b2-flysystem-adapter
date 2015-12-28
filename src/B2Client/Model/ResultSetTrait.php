<?php

namespace iansltx\B2Client\Model;

trait ResultSetTrait // implements \Iterator, \Countable, \ArrayAccess
{
    protected $items = [];
    protected $isDoneIterating;

    public function toArray()
    {
        return $this->items;
    }

    public function count()
    {
        return count($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function next()
    {
        $next = next($this->items);
        if ($next === false) {
            $this->isDoneIterating = true;
        }
        return $next;
    }

    public function key()
    {
        return key($this->items);
    }

    public function valid()
    {
        return !$this->isDoneIterating;
    }

    public function rewind()
    {
        $this->isDoneIterating = !count($this->items);
        return reset($this->items);
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('Offset ' . $offset . ' does not exist');
        }
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Result sets are immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Result sets are immutable');
    }
}
