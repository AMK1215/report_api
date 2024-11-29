<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserTree;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SyncUsersFromAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from the external API to the local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Fetch users from the external API
            $response = Http::get('https://agdashboard.pro/api/transferdata/getallusers'); // Replace with the actual API URL

            if ($response->failed()) {
                $this->error('Failed to fetch users from API.');

                return;
            }

            $apiUsers = $response->json('data');

            // Chunk the API data into smaller batches
            $chunkSize = 100; // Adjust chunk size based on your system's capacity
            $chunks = array_chunk($apiUsers, $chunkSize);

            foreach ($chunks as $chunk) {
                // Process each chunk
                foreach ($chunk as $apiUser) {
                    // Check if user exists in the local database
                    $user = User::where('user_name', $apiUser['user_name'])->first();

                    if ($user) {
                        // If user exists, no need to update
                        continue;
                    }

                    // If user does not exist, create a new user
                    User::updateOrCreate(
                        ['user_name' => $apiUser['user_name']], // Match condition
                        [
                            'id' => $apiUser['id'],
                            'name' => $apiUser['name'],
                            'phone' => $apiUser['phone'],
                            'email' => $apiUser['email'],
                            'email_verified_at' => $apiUser['email_verified_at'],
                            'profile' => $apiUser['profile'],
                            'max_score' => $apiUser['max_score'],
                            'status' => $apiUser['status'],
                            'is_changed_password' => $apiUser['is_changed_password'],
                            'agent_id' => $apiUser['agent_id'],
                            'payment_type_id' => $apiUser['payment_type_id'],
                            'agent_logo' => $apiUser['agent_logo'],
                            'account_name' => $apiUser['account_name'],
                            'account_number' => $apiUser['account_number'],
                            'line_id' => $apiUser['line_id'],
                            'commission' => $apiUser['commission'],
                            'referral_code' => $apiUser['referral_code'],
                            'password' => Hash::make('delightmyanmar'), // Set a default password if new
                            'created_at' => $apiUser['created_at'],
                            'updated_at' => $apiUser['updated_at'],
                        ]
                    );

                    // User::create([
                    //     'id' => $apiUser['id'],
                    //     'user_name' => $apiUser['user_name'],
                    //     'name' => $apiUser['name'],
                    //     'phone' => $apiUser['phone'],
                    //     'email' => $apiUser['email'],
                    //     'email_verified_at' => $apiUser['email_verified_at'],
                    //     'profile' => $apiUser['profile'],
                    //     'max_score' => $apiUser['max_score'],
                    //     'status' => $apiUser['status'],
                    //     'is_changed_password' => $apiUser['is_changed_password'],
                    //     'agent_id' => $apiUser['agent_id'],
                    //     'payment_type_id' => $apiUser['payment_type_id'],
                    //     'agent_logo' => $apiUser['agent_logo'],
                    //     'account_name' => $apiUser['account_name'],
                    //     'account_number' => $apiUser['account_number'],
                    //     'line_id' => $apiUser['line_id'],
                    //     'commission' => $apiUser['commission'],
                    //     'referral_code' => $apiUser['referral_code'],
                    //     'created_at' => $apiUser['created_at'],
                    //     'updated_at' => $apiUser['updated_at'],
                    // ]);
                }

                // Insert or update the user in the user_trees table
                UserTree::updateOrCreate(
                    ['user_id' => $user->id], // Match condition
                    [
                        'parent_id' => $apiUser['agent_id'] ?? $user->id, // Set parent_id (fallback to the user's own ID)
                        'type' => $apiUser['type'] ?? 0,                 // Default type if not provided
                        'parent_type' => $apiUser['parent_type'] ?? 0,   // Default parent_type if not provided
                    ]
                );
            }

            $this->info('Users synced successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e->getMessage());
        }
    }
}
