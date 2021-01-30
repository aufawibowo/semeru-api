<?php


namespace Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK;


use Semeru\Rtpo\Core\Domain\Exceptions\ValidationException;
use Semeru\Rtpo\Core\Domain\Models\Rtpo;
use Semeru\Rtpo\Core\Domain\Models\SikNo;
use Semeru\Rtpo\Core\Domain\Repositories\RtpoRepository;
use Semeru\Rtpo\Core\Domain\Repositories\SikRepository;

class ApproveRescheduleSIKService
{
    private SikRepository $sikRepository;
    private RtpoRepository $rtpoRepository;

    /**
     * ApproveRescheduleSIKService constructor.
     * @param SikRepository $sikRepository
     * @param RtpoRepository $rtpoRepository
     */
    public function __construct(SikRepository $sikRepository, RtpoRepository $rtpoRepository)
    {
        $this->sikRepository = $sikRepository;
        $this->rtpoRepository = $rtpoRepository;
    }

    public function execute(ApproveRescheduleSIKRequest $request)
    {
        $errors = $request->validate();

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

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