<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EbillingService
{
    private string $baseUrl;
    private string $username;
    private string $sharedKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('ebilling.base_url');
        $this->username = config('ebilling.username');
        $this->sharedKey = config('ebilling.shared_key');
        $this->timeout = config('ebilling.timeout', 30);

        // Vérifier que les credentials sont configurés
        if (empty($this->username) || empty($this->sharedKey)) {
            Log::warning('EBILLING: Credentials non configurés', [
                'username_set' => !empty($this->username),
                'shared_key_set' => !empty($this->sharedKey),
            ]);
        }
    }

    /**
     * Créer un paiement EBILLING pour une réservation de billet
     */
    public function createPayment(array $paymentData): array
    {
        try {
            // Vérifier que les credentials sont configurés
            if (empty($this->username) || empty($this->sharedKey)) {
                Log::error('EBILLING: Credentials manquants', [
                    'username' => $this->username ? 'défini' : 'manquant',
                    'shared_key' => $this->sharedKey ? 'défini' : 'manquant',
                ]);

                return [
                    'success' => false,
                    'error' => 'Configuration EBILLING incomplète. Veuillez configurer EBILLING_USERNAME et EBILLING_SHARED_KEY dans le fichier .env',
                ];
            }

            // Préparer le payload au format EBILLING
            $payload = [
                'payer_name' => $paymentData['customer']['name'],
                'payer_email' => $paymentData['customer']['email'],
                'payer_msisdn' => $this->formatPhoneNumber($paymentData['customer']['phone'] ?? ''),
                'amount' => (float) $paymentData['amount'],
                'short_description' => $paymentData['description'] ?? 'Réservation billet SETRAG',
                'external_reference' => $paymentData['reference'],
                'expiry_period' => (int) config('ebilling.expiry_period', 60),
                'callback_url' => config('ebilling.callback_url'),
                'redirect_url' => config('ebilling.redirect_url_success'),
            ];

            // Ajouter les métadonnées si disponibles (format string JSON)
            if (isset($paymentData['metadata'])) {
                $payload['metadata'] = json_encode($paymentData['metadata']);
            }

            // S'assurer que payer_msisdn n'est pas vide (requis par EBILLING)
            if (empty($payload['payer_msisdn'])) {
                // Utiliser un numéro par défaut si non fourni (format Gabon)
                $payload['payer_msisdn'] = '241076000000';
            }

            Log::info('EBILLING: Création de paiement', [
                'reference' => $paymentData['reference'],
                'amount' => $paymentData['amount'],
                'payload' => $payload,
            ]);

            // Envoyer la requête à EBILLING avec les bons headers
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->username, $this->sharedKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/merchant/e_bills", $payload);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Erreur inconnue';
                $responseBody = $response->body();
                
                Log::error('EBILLING: Erreur lors de la création du paiement', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'body' => $responseBody,
                    'base_url' => $this->baseUrl,
                    'username' => $this->username,
                    'endpoint' => "{$this->baseUrl}/api/v1/merchant/e_bills",
                    'payload' => $payload,
                ]);

                // Messages d'erreur plus explicites selon le code HTTP
                $userFriendlyMessage = match($response->status()) {
                    401 => 'Identifiants EBILLING invalides. Veuillez vérifier EBILLING_USERNAME et EBILLING_SHARED_KEY dans le fichier .env',
                    404 => 'Endpoint EBILLING introuvable. Vérifiez EBILLING_BASE_URL',
                    406 => 'Format de requête non accepté par EBILLING. Vérifiez les logs pour plus de détails.',
                    500 => 'Erreur serveur EBILLING. Veuillez réessayer plus tard',
                    default => 'Erreur lors de la création du paiement EBILLING: ' . ($errorMessage ?: 'Code HTTP ' . $response->status()),
                };

                return [
                    'success' => false,
                    'error' => $userFriendlyMessage,
                    'details' => $errorBody,
                    'http_status' => $response->status(),
                ];
            }

            $responseData = $response->json();

            // Extraire le bill_id et construire le lien de paiement
            $billId = $responseData['e_bill']['bill_id'] ?? null;
            
            if (!$billId) {
                Log::error('EBILLING: Bill ID manquant dans la réponse', ['response' => $responseData]);
                return [
                    'success' => false,
                    'error' => 'Bill ID manquant dans la réponse EBILLING',
                ];
            }

            $paymentLink = "{$this->baseUrl}/pay/{$billId}";

            Log::info('EBILLING: Paiement créé avec succès', [
                'bill_id' => $billId,
                'reference' => $paymentData['reference'],
            ]);

            return [
                'success' => true,
                'bill_id' => $billId,
                'payment_link' => $paymentLink,
                'reference' => $paymentData['reference'],
            ];

        } catch (\Exception $e) {
            Log::error('EBILLING: Exception lors de la création du paiement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la communication avec EBILLING: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function getBillStatus(string $billId): array
    {
        try {
            // Essayer l'endpoint standard
            $endpoints = [
                "{$this->baseUrl}/api/v1/merchant/e_bills/{$billId}/status",
                "{$this->baseUrl}/api/v1/merchant/e_bills/{$billId}",
            ];

            foreach ($endpoints as $endpoint) {
                $response = Http::timeout($this->timeout)
                    ->withBasicAuth($this->username, $this->sharedKey)
                    ->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'status' => $data['e_bill']['status'] ?? $data['status'] ?? 'unknown',
                        'amount' => $data['e_bill']['amount'] ?? $data['amount'] ?? null,
                        'transaction_ref' => $data['e_bill']['transaction_id'] ?? $data['transaction_id'] ?? null,
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Impossible de récupérer le statut du paiement',
            ];

        } catch (\Exception $e) {
            Log::error('EBILLING: Erreur lors de la vérification du statut', [
                'bill_id' => $billId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Valider les données d'un callback
     */
    public function validateCallback(array $callbackData): bool
    {
        // Vérifier la présence des champs obligatoires
        $requiredFields = ['billingid', 'bill_id'];
        $hasBillId = false;
        
        foreach ($requiredFields as $field) {
            if (isset($callbackData[$field]) && !empty($callbackData[$field])) {
                $hasBillId = true;
                break;
            }
        }

        if (!$hasBillId) {
            Log::warning('EBILLING: Bill ID manquant dans le callback', ['data' => $callbackData]);
            return false;
        }

        // Le champ 'state' ou 'status' est obligatoire
        if (!isset($callbackData['state']) && !isset($callbackData['status'])) {
            Log::warning('EBILLING: Champ state/status manquant dans le callback', ['data' => $callbackData]);
            return false;
        }

        // Vérifier la présence du montant
        if (!isset($callbackData['amount']) || empty($callbackData['amount'])) {
            Log::warning('EBILLING: Montant manquant dans le callback', ['data' => $callbackData]);
            return false;
        }

        return true;
    }

    /**
     * Normaliser les données du callback
     */
    public function processCallbackData(array $callbackData): array
    {
        // Normaliser les noms de champs
        $normalized = [
            'bill_id' => $callbackData['billingid'] ?? $callbackData['bill_id'] ?? null,
            'transaction_id' => $callbackData['transactionid'] ?? $callbackData['transaction_id'] ?? null,
            'status' => $callbackData['state'] ?? $callbackData['status'] ?? null,
            'amount' => (float) ($callbackData['amount'] ?? 0),
            'reference' => $callbackData['reference'] ?? null,
            'payment_system' => $callbackData['paymentsystem'] ?? $callbackData['payment_system'] ?? null,
            'payer_name' => $callbackData['payername'] ?? $callbackData['payer_name'] ?? null,
            'payer_email' => $callbackData['payeremail'] ?? $callbackData['payer_email'] ?? null,
            'payer_phone' => $callbackData['payermsisdn'] ?? $callbackData['payer_phone'] ?? null,
        ];

        // Mapper les statuts EBILLING vers nos statuts
        $statusMapping = [
            'paid' => 'completed',
            'processed' => 'completed',
            'pending' => 'pending',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            'expired' => 'expired',
        ];

        $normalized['mapped_status'] = $statusMapping[$normalized['status']] ?? 'unknown';

        return $normalized;
    }

    /**
     * Formater le numéro de téléphone au format international
     */
    private function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Supprimer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si le numéro commence par 0, remplacer par 241 (code pays Gabon)
        if (strpos($phone, '0') === 0) {
            $phone = '241' . substr($phone, 1);
        }

        // Si le numéro ne commence pas par 241, l'ajouter
        if (strpos($phone, '241') !== 0) {
            $phone = '241' . $phone;
        }

        return $phone;
    }
}

