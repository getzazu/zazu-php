<?php

declare(strict_types=1);

namespace Zazu\Resources;

use Zazu\Client;
use Zazu\Page;
use Zazu\Response;

/**
 * Read-only directory of saved transfer recipients. Each beneficiary
 * embeds its bank accounts; the one flagged `default` is used when a
 * transfer names only the beneficiary_id. Beneficiaries are created and
 * managed in the Zazu dashboard.
 */
final class Beneficiaries extends AbstractResource
{
    /**
     * GET /api/beneficiaries
     */
    public function list(?int $limit = null, ?string $cursor = null): Page
    {
        return $this->client->listPage('api/beneficiaries', [], $limit, $cursor);
    }

    /**
     * GET /api/beneficiaries/:id
     */
    public function get(string $id): Response
    {
        return $this->client->request('GET', Client::encodePath('api/beneficiaries', $id));
    }
}
