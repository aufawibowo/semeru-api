<?php


namespace Semeru\Rtpo\Core\Domain\Models;


class Rtpo
{
    private RtpoNik $rtpoNik;
    private string $rtpoCn;
    private SikNo $sikNo;
    private Date $last_updated;

    /**
     * Rtpo constructor.
     * @param RtpoNik $rtpoNik
     * @param string $rtpoCn
     * @param SikNo $sikNo
     * @param Date $last_updated
     */
    public function __construct(RtpoNik $rtpoNik, string $rtpoCn, SikNo $sikNo, Date $last_updated)
    {
        $this->rtpoNik = $rtpoNik;
        $this->rtpoCn = $rtpoCn;
        $this->sikNo = $sikNo;
        $this->last_updated = $lastUpdated;
    }

    /**
     * @return RtpoNik
     */
    public function rtpoNik(): RtpoNik
    {
        return $this->rtpoNik;
    }

    /**
     * @return string
     */
    public function rtpoCn(): string
    {
        return $this->rtpoCn;
    }

    /**
     * @return SikNo
     */
    public function sikNo(): SikNo
    {
        return $this->sikNo;
    }

    /**
     * @return Date
     */
    public function lastUpdated(): Date
    {
        return $this->lastUpdated;
    }


}