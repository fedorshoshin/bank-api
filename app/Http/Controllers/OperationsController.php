<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    public function transfer(Request $request)
    {
        try {
            $data = $this->validateTransferRequest($request);
            $this->withdraw($data['from_user_id'], $data['amount']);
            echo "transfer_out\n";
            $this->deposit($data['to_user_id'], $data['amount']);
            echo "transfer_in\n";
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
        return response()->json(['message' => 'success'], 200);
    }

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
                    throw new \Exception("Incorrect Operation type", code: 400);
            }
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function balance($user_id)
    {
        try {
            return User::find($user_id)->balance;
        } catch (\Exception $e) {
            return $this->returnError($e);
        }
    }

    private function deposit($recipient, $amount)
    {
        DB::transaction(function () use ($recipient, $amount) {
            $user = User::findOrFail($recipient);
            $user->balance = $amount;
            $user->save();
        });
        echo "deposit\n";
    }

    private function withdraw($payer, $amount)
    {
        DB::transaction(function () use ($payer, $amount) {
            $user = User::findOrFail($payer);
            $user->balance -= $amount;
            if ($user->balance < 0) {
                throw new \Exception("Operation denied. Insufficient balance.", code: 409);
            }
            $user->save();
        });
        echo "withdraw\n";
    }

    private function validateTransactionRequest($request)
    {
        return $request->validate(['user_id' => 'required|integer', 'type' => 'required', 'amount' => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/']);
    }

    private function validateTransferRequest($request)
    {
        return $request->validate(['from_user_id' => 'required',
            'to_user_id' => 'required',
            'amount' => 'required|numeric|min:0.01|regex:/^\d+(\.\d{1,2})?$/']);
    }

    private function returnError($exception)
    {
        return response(['error' => $exception->getMessage()], $exception->getCode() != null ? $exception->getCode() : 400);
    }
}
