<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * WalletService handles all wallet-related business logic
 */
class WalletService
{
    /**
     * Create a new user account with zero balance
     *
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function createAccount(array $userData): User
    {
        try {
            return User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'), // Default password for demo
                'balance' => 0.00,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Process a wallet transaction (top-up or charge)
     *
     * @param int $userId User identifier
     * @param float $amount Transaction amount (positive value)
     * @param string $reference Unique transaction reference
     * @param string $type Transaction type (TYPE_TOP_UP or TYPE_CHARGE)
     * @param string|null $description Optional transaction description
     * @return array Contains transaction, user, and message
     * @throws \Exception On validation failure or database error
     */
    public function processTransaction(int $userId, float $amount, string $reference, string $type, ?string $description): array
    {       $is_top_up = $type === Transaction::TYPE_TOP_UP;
            $is_charge = $type === Transaction::TYPE_CHARGE;
        try {
            DB::beginTransaction();

            // Lock user record to prevent race conditions
            $user = User::lockForUpdate()->findOrFail($userId);

            // Ensure precise decimal calculations
            $amount = round($amount, 2);

            // Validate sufficient balance for charge operations
            if ($is_charge && $user->balance < $amount) {
                DB::rollBack();
                throw new \Exception(json_encode([
                    'type' => 'insufficient_balance',
                    'current_balance' => $user->balance,
                    'requested_amount' => $amount
                ]));
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'reference' => $reference,
                'description' => $description ?? ucfirst(str_replace('_', '-', $type)) . ' transaction',
            ]);

            // Update user balance
            if ($is_top_up) {
                $user->balance += $amount;
            } else {
                $user->balance -= $amount;
            }

            $user->save();
            DB::commit();

            return [
                'transaction' => $transaction,
                'user' => $user,
                'message' => $is_top_up ? Transaction::MSG_TOP_UP_SUCCESSFUL : Transaction::MSG_CHARGE_SUCCESSFUL
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user balance information
     *
     * @param int $userId
     * @return User
     * @throws \Exception
     */
    public function getUserBalance(int $userId): User
    {
        try {
            return User::findOrFail($userId);
        } catch (\Exception $e) {
            throw new \Exception('User not found');
        }
    }

    /**
     * Get paginated transaction history for a user
     *
     * @param int $userId
     * @param int $perPage
     * @return array
     * @throws \Exception
     */
    public function getUserTransactions(int $userId, int $perPage = 10): array
    {
        try {
            $user = User::findOrFail($userId);
            $transactions = $user->transactions()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return [
                'user' => $user,
                'transactions' => $transactions
            ];
        } catch (\Exception $e) {
            throw new \Exception('User not found');
        }
    }

}
