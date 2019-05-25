<?php

namespace Ucetnictvi\Entity;

class Contact
{
    public $id;

    public static function create(array $data): self
    {
        $contact = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($contact, $key)) {
                continue;
            }
            $contact->{$key} = $value;
        }
        return $contact;
    }
}
