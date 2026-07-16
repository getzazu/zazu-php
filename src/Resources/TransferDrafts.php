<?php

declare(strict_types=1);

namespace Zazu\Resources;

use Zazu\Client;
use Zazu\Response;

/**
 * API-initiated transfers. Creating a draft routes it into the
 * workspace's in-app approval flow — the API never executes a transfer
 * itself. A manager or legal representative approves in the Zazu app;
 * poll {@see TransferDrafts::get()} (status: requested → processing →
 * completed / failed) or subscribe to the `transfer.executed` webhook to
 * follow execution.
 */
final class TransferDrafts extends AbstractResource
{
    /**
     * POST /api/transfer_drafts
     *
     * Required: account_id, amount, and exactly one of beneficiary_id
     * (external transfer) or destination_account_id (own-account move).
     * Optional: external_account_id, currency_code, payment_reference,
     * internal_notes.
     *
     * @param array<string, mixed> $attributes snake_case keys, exactly what the API accepts
     */
    public function create(array $attributes): Response
    {
        return $this->client->request('POST', 'api/transfer_drafts', body: $attributes);
    }

    /**
     * GET /api/transfer_drafts/:id
     */
    public function get(string $id): Response
    {
        return $this->client->request('GET', Client::encodePath('api/transfer_drafts', $id));
    }
}
