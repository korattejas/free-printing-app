<?php

namespace App\Http\Controllers;

use App\Models\PremiumAdd;
use App\Traits\CommonFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PremiumAddController extends Controller
{
    use CommonFunction;

    protected mixed $error_message, $validator_error_code, $backend_error_code, $success_status_code, $controller_name;


    public function __construct()
    {
        $this->error_message = config('constants.error_responses.message');
        $this->validator_error_code = config('constants.error_responses.validator_error_code');
        $this->backend_error_code = config('constants.error_responses.backend_error_code');
        $this->success_status_code = config('constants.error_responses.success_status_code');
        $this->controller_name = "App\Http\Controllers\AuthController";
    }

    public function premiumAddList(): \Illuminate\Http\JsonResponse
    {
        $function_name = "premiumAddList";
        try {
            $get_premium_add = PremiumAdd::leftJoin('categories', 'categories.id', '=', 'premium_adds.category_id')
                ->where('premium_adds.status', 1)
                ->where('premium_adds.end_date', '>', date('y-m-d'))
                ->select(
                    'premium_adds.category_id',
                    'categories.name as category_name',
                    'premium_adds.image',
                    DB::raw("DATE_FORMAT(premium_adds.start_date, '%d-%m-%y') as start_date"),
                    DB::raw("DATE_FORMAT(premium_adds.end_date, '%d-%m-%y') as end_date"),
                    'premium_adds.type',
                    'premium_adds.description'
                )
                ->get();

            return $this->sendResponse($this->success_status_code, "Premium add list", $get_premium_add);
        } catch (\Exception $e) {
            logger()->error("$this->controller_name:$function_name:Exception occurred", ['error_message' => $e->getMessage()]);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }
}
