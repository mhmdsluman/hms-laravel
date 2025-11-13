<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClinicalDecisionSupportService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.drugbank.com/v1/';

    public function __construct()
    {
        $this->apiKey = config('services.drugbank.api_key');
    }

    /**
     * Check for drug-drug interactions.
     *
     * @param  array  $drugIdentifiers
     * @return \Illuminate\Http\Client\Response
     */
    public function checkDrugInteractions(array $drugIdentifiers)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->apiUrl . 'ddi', [
            'drugs' => $drugIdentifiers,
        ]);
    }
}
