<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserTree;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;



class SyncUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    try {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Fetch users from the external API
        $response = Http::get('https://agdashboard.pro/api/transferdata/getallusers');

        if ($response->failed()) {
            $this->error('Failed to fetch users from API. Status: ' . $response->status());
            return;
        }

        $apiUsers = $response->json('data');

        if (empty($apiUsers)) {
            $this->error('No users found in the API response.');
            return;
        }

        // Chunk the API data into smaller batches
        $chunkSize = 100; // Adjust based on your memory and system capacity
        $chunks = array_chunk($apiUsers, $chunkSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $apiUser) {
                try {
                    // Check if user exists in the local database
                    $user = User::where('user_name', $apiUser['user_name'])->first();

                    if (!$user) {
                        // Create the user if it doesn't exist
                        User::updateOrCreate(
                            ['user_name' => $apiUser['user_name']],
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
                                'agent_id' => $apiUser['agent_id'], // Assuming agent_id exists or null
                                'payment_type_id' => $apiUser['payment_type_id'],
                                'agent_logo' => $apiUser['agent_logo'],
                                'account_name' => $apiUser['account_name'],
                                'account_number' => $apiUser['account_number'],
                                'line_id' => $apiUser['line_id'],
                                'commission' => $apiUser['commission'],
                                'referral_code' => $apiUser['referral_code'],
                                'password' => Hash::make('delightmyanmar'),
                                'created_at' => $apiUser['created_at'],
                                'updated_at' => $apiUser['updated_at'],
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    $this->error("Error syncing user {$apiUser['user_name']}: " . $e->getMessage());
                }
            }
        }

        $this->info('Users synced successfully.');
    } catch (\Exception $e) {
        $this->error('An error occurred during sync: ' . $e->getMessage());
    } finally {
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

}