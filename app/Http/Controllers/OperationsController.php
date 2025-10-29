<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    /**
     * Transfer money between two users.
     *
     * @group Transactions
     *
     * Transfers a specified amount from one user to another.
     *
     * @bodyParam from_user_id int required The ID of the user sending money. Example: 1
     * @bodyParam to_user_id int required The ID of the user receiving money. Example: 2
     * @bodyParam amount number required The amount to transfer (up to 2 decimal places). Example: 25.50
     *
     * @response 200 {
     *   "message": "success"
     * }
     * @response 409 {
     *   "error": "Operation denied. Insufficient balance."
     * }
     * @response 400 {
     *   "error": "Invalid request"
     * }
     */
    public function transfer(Request $request)
    {
        try {
            $data = $this->validateTransferRequest($request);
            $this->withdraw($data['from_user_id'], $data['amount']);
            $this->deposit($data['to_user_id'], $data['amount']);
        } catch (\Exception $e) {
            return $this->returnError($e);
        }

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Perform a single transaction (deposit or withdraw).
     *
     * @group Transactions
     *
     * Use this endpoint to deposit or withdraw funds from a user account.
     *
     * @bodyParam user_id int required The ID of the user performing the transaction. Example: 1
     * @bodyParam type string required The type of transaction. Must be either "deposit" or "withdraw". Example: deposit
     * @bodyParam amount number required The amount to deposit or withdraw (up to 2 decimal places). Example: 100.00
     *
     * @response 200 {
     *   "message": "success"
     * }
     * @response 409 {
     *   "error": "Operation denied. Insufficient balance."
     * }
     * @response 400 {
     *   "error": "Incorrect Operation type"
     * }
     */
    public function transaction(Request $request)
    {
        try {
            $data = $this->validateTransactionRequest($request);

            switch ($data['type']) {
                case 'deposit':
                    $this->deposit($data['user_id'], $data['amount']);
                    break;
                case 'withdraw':
                    $this->withdraw($data['user_id'], $data['amount']);
                    break;
                default:
                    throw new \Exception("Incorrect Operation type", 400);
            }
        } catch (\Exception $e) {
            return $this->returnError($e);
        }

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Get the current balance of a user.
     *
     * @group Balance
     *
     * Retrieve the current balance of a specific user by their ID.
     *
     * @urlParam user_id int required The ID of the user whose balance you want to check. Example: 1
     *
     * @response 200 "100.00"
     * @response 404 {
     *   "error": "User not found"
     * }
     */
    public function balance($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            return response()->json($user->balance, 200);
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }

    private function deposit($recipient, $amount)
    {
        DB::transaction(function () use ($recipient, $amount) {
            $user = User::findOrFail($recipient);
            $user->balance += $amount; // changed from = $amount to +=
            $user->save();
        });
    }

    private function withdraw($payer, $amount)
    {
        DB::transaction(function () use ($payer, $amount) {
            $user = User::findOrFail($payer);
            $user->balance -= $amount;
            if ($user->balance < 0) {
                throw new \Exception("Operation denied. Insufficient balance.", 409);
            }
            $user->save();
        });
    }

    private function validateTransactionRequest($request)
    {
        return $request->validate([
            'user_id' => 'required|integer',
            'type' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/'
        ]);
    }

    private function validateTransferRequest($request)
    {
        return $request->validate([
            'from_user_id' => 'required|integer',
            'to_user_id' => 'required|integer|different:from_user_id',
            'amount' => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/'
        ]);
    }

    private function returnError($exception)
    {
        return response([
            'error' => $exception->getMessage()
        ], $exception->getCode() ?: 400);
    }
}
