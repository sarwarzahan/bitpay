<?php

/**
 * Invoice class to create mock invoice object.
 */
class invoice
{
    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
            $this->total = 10.50;
            // To maintain invoice status for draft, sent etc.
            $this->status = 3;
        }
    }
}