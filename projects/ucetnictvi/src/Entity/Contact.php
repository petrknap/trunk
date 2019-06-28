<?php

namespace Ucetnictvi\Entity;

class Contact
{
    private $id;
    private $name;
    private $addressLine1;
    private $addressLine2;
    private $city;
    private $zipOrPostalCode;
    private $stateOrProvinceOrRegion;
    private $country;
    private $email;
    private $ban;
    private $iban;
    private $identificationNumber;
    private $vatIdentificationNumber;
    private $registrationNumberInCompanyRegister;

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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZipOrPostalCode(): string
    {
        return $this->zipOrPostalCode;
    }

    public function getStateOrProvinceOrRegion(): ?string
    {
        return $this->stateOrProvinceOrRegion;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getBan(): string
    {
        return $this->ban;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function getIdentificationNumber(): ?string
    {
        return $this->identificationNumber;
    }

    public function getVatIdentificationNumber(): ?string
    {
        return $this->vatIdentificationNumber;
    }

    public function getRegistrationNumberInCompanyRegister(): ?string
    {
        return $this->registrationNumberInCompanyRegister;
    }
}
