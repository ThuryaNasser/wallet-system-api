<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserAccountRequest;
use App\Http\Requests\TopUpRequest;
use App\Http\Requests\ChargeRequest;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;

/**
 * WalletController handles HTTP requests for wallet operations
 * Delegates business logic to WalletService for clean separation of concerns
 */
class WalletController extends Controller
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a new user account with wallet functionality
     *
     * @param CreateUserAccountRequest $request Validated request data
     * @return JsonResponse
     */
    public function createAccount(CreateUserAccountRequest $request): JsonResponse
    {
        try {
            $user = $this->walletService->createAccount($request->validated());

            return response()->json([
                'success' => true,
                'message' => Transaction::MSG_ACCOUNT_CREATED,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance' => number_format($user->balance, 2),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add balance to user account (Top-up operation)
     *
     * @param TopUpRequest $request Validated request data
     * @return JsonResponse
     */
    public function topUp(TopUpRequest $request): JsonResponse
    {
        try {
            $result = $this->walletService->processTransaction(
                $request->user_id,
                $request->amount,
                $request->reference,
                Transaction::TYPE_TOP_UP,
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'transaction_id' => $result['transaction']->id,
                    'user_id' => $result['user']->id,
                    'type' => $result['transaction']->type,
                    'amount' => number_format($result['transaction']->amount, 2),
                    'new_balance' => number_format($result['user']->balance, 2),
                    'reference' => $result['transaction']->reference,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Top-up failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deduct balance from user account (Charge operation)
     *
     * @param ChargeRequest $request Validated request data
     * @return JsonResponse
     */
    public function charge(ChargeRequest $request): JsonResponse
    {
        try {
            $result = $this->walletService->processTransaction(
                $request->user_id,
                $request->amount,
                $request->reference,
                Transaction::TYPE_CHARGE,
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'transaction_id' => $result['transaction']->id,
                    'user_id' => $result['user']->id,
                    'type' => $result['transaction']->type,
                    'amount' => number_format($result['transaction']->amount, 2),
                    'new_balance' => number_format($result['user']->balance, 2),
                    'reference' => $result['transaction']->reference,
                ]
            ], 200);

        } catch (\Exception $e) {
            // Handle insufficient balance exception specially
            if (str_contains($e->getMessage(), 'insufficient_balance')) {
                $data = json_decode($e->getMessage(), true);
                return response()->json([
                    'success' => false,
                    'message' => WalletService::MSG_INSUFFICIENT_BALANCE,
                    'data' => [
                        'current_balance' => number_format($data['current_balance'], 2),
                        'requested_amount' => number_format($data['requested_amount'], 2),
                    ]
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => 'Charge failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current balance for a user
     *
     * @param int $userId User identifier
     * @return JsonResponse
     */
    public function getBalance(int $userId): JsonResponse
    {
        try {
            $user = $this->walletService->getUserBalance($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance' => number_format($user->balance, 2),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get paginated transaction history for a user
     *
     * @param int $userId User identifier
     * @return JsonResponse
     */
    public function getTransactions(int $userId): JsonResponse
    {
        try {
            $result = $this->walletService->getUserTransactions($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $result['user']->id,
                    'transactions' => $result['transactions']->items(),
                    'pagination' => [
                        'current_page' => $result['transactions']->currentPage(),
                        'per_page' => $result['transactions']->perPage(),
                        'total' => $result['transactions']->total(),
                        'last_page' => $result['transactions']->lastPage(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
