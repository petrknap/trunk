<?php

namespace PetrKnap\Symfony\ShoppingBasket\Model;

class Customer extends \ArrayObject
{
    /**
     * @return string|null
     */
    public function getForename()
    {
        return $this->offsetGet('forename');
    }

    /**
     * @param string|null $forename
     */
    public function setForename($forename)
    {
        $this->offsetSet('forename', $forename);
    }

    /**
     * @return string|null
     */
    public function getSurname()
    {
        return $this->offsetGet('surname');
    }

    /**
     * @param string|null $surname
     */
    public function setSurname($surname)
    {
        $this->offsetSet('surname', $surname);
    }

    /**
     * @return string|null
     */
    public function getEMail()
    {
        return $this->offsetGet('e-mail');
    }

    /**
     * @param string|null $eMail
     */
    public function setEMail($eMail)
    {
        $this->offsetSet('e-mail', $eMail);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->offsetGet('address');
    }

    /**
     * @param string|null $address
     */
    public function setAddress($address)
    {
        $this->offsetSet('address', $address);
    }
}
