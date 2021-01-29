<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


use Semeru\Rtpo\Core\Domain\Models\Rtpo;
use Semeru\Rtpo\Core\Domain\Models\SikNo;
use Semeru\Rtpo\Core\Domain\Repositories\RtpoRepository;
use Semeru\Rtpo\Core\Domain\Repositories\SikRepository;

class Service
{
    private SikRepository $sikRepository;
    private RtpoRepository $rtpoRepository;

    /**
     * Service constructor.
     * @param SikRepository $sikRepository
     * @param RtpoRepository $rtpoRepository
     */
    public function __construct(SikRepository $sikRepository, RtpoRepository $rtpoRepository)
    {
        $this->sikRepository = $sikRepository;
        $this->rtpoRepository = $rtpoRepository;
    }

    public function execute(Request $request)
    {
        $rtpo = new Rtpo(
            $this->rtpoRepository->getRtpoId(),
            $this->rtpoRepository->getRtpoUserData(),
            new SikNo($request->sik_no),
            new \DateTime('now')
        );

        $waitingForApprovalAccepted = $this->sikRepository->setWaitingForApproval($rtpo);

        if($waitingForApprovalAccepted)
        {
            return 'OK';
        }
        else{
            $this->sikRepository->rejectByRtpo($rtpo);
            return 'Rejected';
        }
    }
}