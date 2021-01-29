<?php


namespace Rtpo\Core\Domain\Models;


use Semeru\Rtpo\Core\Domain\Models\Coordinate;
use Semeru\Rtpo\Core\Domain\Models\Date;
use Semeru\Rtpo\Core\Domain\Models\MobilePartnerId;
use Semeru\Rtpo\Core\Domain\Models\ZipCode;

class MobilePartner
{
    private MobilePartnerId $id;
    private string $name;
    private Address $address;
    private Date $dateCreated;
    private Date $dateVerified;
    private Date $dateModified;

    /**
     * MobilePartner constructor.
     * @param MobilePartnerId $id
     * @param string $name
     * @param ZipCode $zipCode
     * @param Coordinate $coordinate
     * @param Date $dateCreated
     * @param Date $dateVerified
     * @param Date $dateModified
     */
    public function __construct(MobilePartnerId $id, string $name, ZipCode $zipCode, Coordinate $coordinate, Date $dateCreated, Date $dateVerified, Date $dateModified)
    {
        $this->id = $id;
        $this->name = $name;
        $this->zipCode = $zipCode;
        $this->coordinate = $coordinate;
        $this->dateCreated = $dateCreated;
        $this->dateVerified = $dateVerified;
        $this->dateModified = $dateModified;
    }

    /**
     * @return MobilePartnerId
     */
    public function getId(): MobilePartnerId
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Date
     */
    public function getDateCreated(): Date
    {
        return $this->dateCreated;
    }

    /**
     * @return Date
     */
    public function getDateVerified(): Date
    {
        return $this->dateVerified;
    }

    /**
     * @return Date
     */
    public function getDateModified(): Date
    {
        return $this->dateModified;
    }


}