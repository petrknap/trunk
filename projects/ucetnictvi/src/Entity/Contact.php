<?php

namespace Ucetnictvi\Entity;

class Contact
{
    public $id;
    public $name;
    public $addressLine1;
    public $addressLine2;
    public $city;
    public $zipOrPostalCode;
    public $stateOrProvinceOrRegion;
    public $country;
    public $email;
    public $iban;
    public $identificationNumber;
    public $registrationNumberInCompanyRegister;

    public static function create(array $data): self
    {
        $contact = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($contact, $key)) {
                continue;
            }
            $contact->{$key} = $value ?: null;
        }
        return $contact;
    }
}
