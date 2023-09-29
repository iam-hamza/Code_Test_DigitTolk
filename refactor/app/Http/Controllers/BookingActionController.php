<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

   /**
     * Send an immediate job email notification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function immediateJobEmail(Request $request)
    {
        try {
            $adminSenderEmail = config('app.adminemail');
            $data = $request->validate([
                // Define your validation rules here
            ]);

            $response = $this->repository->storeJobEmail($data);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Get the job history for a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getHistory(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            
            if (!$user_id) {
                return response()->json(['message' => 'User ID is required'], 400);
            }

            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }


    /**
 * Accept a job.
    *
    * @param Request $request
    * @return JsonResponse
    */
    public function acceptJob(Request $request)
    {
        try {
            $data = $request->validate([
                // Define your validation rules here
            ]);

            $user = $request->__authenticatedUser;

            $response = $this->repository->acceptJob($data, $user);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }


    /**
     * Accept a job with a specified job ID.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function acceptJobWithId(Request $request)
    {
        try {
            $jobId = $request->input('job_id');

            if (!$jobId) {
                return response()->json(['message' => 'Job ID is required'], 400);
            }

            $user = $request->__authenticatedUser;

            $response = $this->repository->acceptJobWithId($jobId, $user);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }


   /**
 * Cancel a job.
 *
 * @param Request $request
 * @return JsonResponse
 */
public function cancelJob(Request $request)
{
    try {
        $data = $request->validate([
            // Define your validation rules here
        ]);

        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred'], 500);
    }
}


    /**
 * Cancel a job.
 *
 * @param Request $request
 * @return JsonResponse
 */
public function endJob(Request $request)
{
    try {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred'], 500);
    }
}


    /**
 * Cancel a job.
 *
 * @param Request $request
 * @return JsonResponse
 */
public function customerNotCall(Request $request)
{
    try {
        $data = $request->validate([
            // Define your validation rules here
        ]);

        $response = $this->repository->customerNotCall($data);

        return response()->json($response);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred'], 500);
    }
}


    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    /**
     * Update job distance and related attributes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function distanceFeed(DistanceFeedRequest $request)
    {
        try {
            $data = $request->validated()

            $jobid = $data['jobid'];

            $this->updateJobDistance($jobid, $data);
            $this->updateJobAttributes($jobid, $data);

            return response()->json(['message' => 'Record updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Update job distance if provided.
     *
     * @param int $jobid
     * @param array $data
     */
    private function updateJobDistance($jobid, $data)
    {
        if (isset($data['distance']) && $data['distance'] !== "") {
            Distance::where('job_id', '=', $jobid)->update(['distance' => $data['distance']]);
        }

        if (isset($data['time']) && $data['time'] !== "") {
            Distance::where('job_id', '=', $jobid)->update(['time' => $data['time']]);
        }
    }

    /**
     * Update job attributes if provided.
     *
     * @param int $jobid
     * @param array $data
     */
    private function updateJobAttributes($jobid, $data)
    {
        $attributesToUpdate = [
            'admin_comments' => $data['admincomment'] ?? "",
            'flagged' => $data['flagged'] ? 'yes' : 'no',
            'session_time' => $data['session_time'] ?? "",
            'manually_handled' => $data['manually_handled'] ? 'yes' : 'no',
            'by_admin' => $data['by_admin'] ? 'yes' : 'no',
        ];

        Job::where('id', '=', $jobid)->update($attributesToUpdate);
    }


    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
