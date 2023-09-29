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
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

   /**
     * Get a list of jobs based on the user or user type.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = $request->__authenticatedUser;

        // Delegate the responsibility of determining the action
        $response = $this->determineAction($request, $user);

        return response()->json($response);
    }

    /**
     * Determine the appropriate action based on the request and user.
     *
     * @param Request $request
     * @param User $user
     * @return mixed
     */
    private function determineAction(Request $request, User $user)
    {
        $userId = $request->get('user_id');
        $userType = $user->user_type;

        if ($userId) {
            return $this->repository->getUsersJobs($userId);
        }

        if ($this->isAdminOrSuperAdmin($userType)) {
            return $this->repository->getAll($request);
        }

        // Handle other cases if needed
        return null; // Or set a default response
    }

    /**
     * Check if the user is an admin or superadmin.
     *
     * @param string $userType
     * @return bool
     */
    private function isAdminOrSuperAdmin($userType)
    {
        // Retrieve the admin and superadmin role IDs from the configuration
        $adminRoleId = config('yourconfig.admin_role_id');
        $superAdminRoleId = config('yourconfig.superadmin_role_id');

        // Compare the user's role with the configured role IDs
        return $userType == $adminRoleId || $userType == $superAdminRoleId;
    }


    /**
     * Display the details of a specific job.
     *
     * @param Job $job
     * @return JsonResponse
     */
    public function show(Job $job)
    {
        try {
            // The $job parameter is already resolved by Laravel, so no need to find by ID
            // Eager load the specified relationships
            $job->load('translatorJobRel.user');

            return response()->json($job);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the job is not found
            return response()->json(['error' => 'Job not found'], 404);
        } catch (\Exception $e) {
            // Handle any other exceptions or errors
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

     /**
     * Store a new job.
     *
     * @param StoreJobRequest $request
     * @return JsonResponse
     */
    public function store(StoreJobRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $request->__authenticatedUser;

            // Store the job and get the response
            $response = $this->repository->store($user, $data);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Update a specific job.
     *
     * @param int $id
     * @param UpdateJobRequest $request
     * @return JsonResponse
     */
    public function update($id, UpdateJobRequest $request)
    {
        try {
            $data = $request->validated();
            $cuser = $request->__authenticatedUser;

            // Update the job and get the response
            $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

            return response()->json($response);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Job not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    
}
