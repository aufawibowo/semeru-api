<?php


namespace Semeru\Rtpo\Presentation\Controllers;


use Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK\ApproveRescheduleSIKRequest;
use Semeru\Rtpo\Core\Application\Services\ApproveRescheduleSIK\ApproveRescheduleSIKService;

class Controller extends BaseController
{
    public function requestMBPToSiteDownAction()
    {
        $sik_no = $this->request->get('sik_no');
        $username = $this->request->get('username');
        $reason = $this->request->get('reason');
        $is_approved = $this->request->get('is_approved');

        $request = new ApproveRescheduleSIKRequest(
            $sik_no,
            $username,
            $reason,
            $is_approved
        );

        $service = new ApproveRescheduleSIKService(
            $this->di->get('sikRepository'),
            $this->di->get('rtpoRepository')
        );

        try {
            $result = $service->execute($request);

            $this->sendData($result);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    public function approveRescheduleSIKAction()
    {

    }

    public function cancelRequestMBPAction()
    {

    }

    public function getDetailRescheduleSIKAction()
    {

    }

    public function getListRescheduleSIKAction()
    {

    }

    public function getTiketMBPTidakDikerjakanAction()
    {

    }

    public function updateRescheduleSIKAction()
    {

    }
}