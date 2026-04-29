<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        Log::info('[FCM] Starting sendToTokens', [
            'raw_tokens_count' => count($tokens),
            'title' => $title,
            'body' => $body,
            'data_keys' => array_keys($data)
        ]);

        // Validate and clean tokens
        $tokens = array_values(array_unique(array_filter(array_map(function ($t) {
            $t = is_string($t) ? trim($t) : '';
            return $t !== '' ? $t : null;
        }, $tokens))));

        Log::debug('[FCM] Cleaned tokens', [
            'valid_tokens_count' => count($tokens),
            'tokens_sample' => array_slice($tokens, 0, 3)
        ]);

        if (empty($tokens)) {
            Log::warning('[FCM] No valid tokens to send');
            return;
        }

        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('[FCM] Failed to get access token');
                return;
            }

            $projectId = (string) config('services.fcm.project_id');
            if (!$projectId) {
                Log::error('[FCM] Missing project ID');
                return;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            Log::info('[FCM] Sending to FCM endpoint', [
                'project_id' => $projectId,
                'tokens_count' => count($tokens)
            ]);

            $successCount = 0;
            $failureCount = 0;

            // Send to each token individually
            foreach ($tokens as $index => $token) {
                $tokenId = $index + 1;
                $tokenMask = substr($token, 0, 15) . '...';
                
                Log::debug("[FCM] Sending to token {$tokenId}/" . count($tokens), [
                    'token' => $tokenMask
                ]);

                try {
                    $payload = [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body'  => $body,
                            ],
                            'data' => array_map('strval', $data),
                            'android' => [
                                'priority' => 'high',
                            ],
                        ],
                    ];

                    $response = Http::withToken($accessToken)
                        ->acceptJson()
                        ->timeout(10)
                        ->post($url, $payload);

                    if ($response->successful()) {
                        $successCount++;
                        Log::info("[FCM] Token {$tokenId} sent successfully", [
                            'token' => $tokenMask,
                            'response' => $response->json()
                        ]);
                    } else {
                        $failureCount++;
                        $errorMsg = $response->json()['error']['message'] ?? $response->body();
                        Log::warning("[FCM] Token {$tokenId} failed", [
                            'token' => $tokenMask,
                            'status' => $response->status(),
                            'error' => $errorMsg
                        ]);
                    }
                } catch (\Throwable $e) {
                    $failureCount++;
                    Log::error("[FCM] Token {$tokenId} exception", [
                        'token' => $tokenMask,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('[FCM] Send completed', [
                'total_tokens' => count($tokens),
                'success' => $successCount,
                'failure' => $failureCount
            ]);

        } catch (\Throwable $e) {
            Log::error('[FCM] FCM send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getAccessToken(): ?string
    {
        Log::debug('[FCM] Getting access token');
        
        $clientEmail = (string) config('services.fcm.client_email');
        $privateKey = (string) config('services.fcm.private_key');
        
        // Fix private key format
        $privateKey = str_replace("\\n", "\n", $privateKey);
        
        Log::debug('[FCM] Credentials check', [
            'has_client_email' => !empty($clientEmail),
            'has_private_key' => !empty($privateKey)
        ]);

        if (!$clientEmail || !$privateKey) {
            Log::error('[FCM] Missing Firebase credentials');
            return null;
        }

        $now = time();
        $jwtHeader = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtClaim = $this->base64UrlEncode(json_encode([
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $unsigned = $jwtHeader . '.' . $jwtClaim;

        // Sign the JWT
        $signature = '';
        $signSuccess = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$signSuccess) {
            Log::error('[FCM] JWT signing failed', [
                'openssl_error' => openssl_error_string()
            ]);
            return null;
        }

        $jwt = $unsigned . '.' . $this->base64UrlEncode($signature);

        try {
            Log::debug('[FCM] Requesting OAuth2 token');
            
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

            if (!$response->successful()) {
                Log::error('[FCM] OAuth2 token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $responseData = $response->json();
            $accessToken = $responseData['access_token'] ?? null;

            if (!$accessToken) {
                Log::error('[FCM] No access token in response');
                return null;
            }

            Log::info('[FCM] Access token obtained successfully');
            return $accessToken;

        } catch (\Throwable $e) {
            Log::error('[FCM] OAuth2 token request exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}