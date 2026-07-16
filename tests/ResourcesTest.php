<?php

declare(strict_types=1);

namespace Zazu\Tests;

use PHPUnit\Framework\TestCase;
use Zazu\Client;

/**
 * Mirror of zazu-ruby's spec/zazu/resources/*_spec.rb (and zazu-go's
 * resources_test.go) — same cassettes, same assertions, per the
 * cross-language SDK contract.
 */
final class ResourcesTest extends TestCase
{
    private function replayClient(string ...$cassettes): Client
    {
        return new Client(
            apiKey: 'test-api-key-for-replay',
            baseUrl: 'http://cassette-replay.test',
            httpClient: CassetteReplayHandler::client(...$cassettes),
        );
    }

    public function testEntityGet(): void
    {
        $client = $this->replayClient('entity/get');

        $resp = $client->entity->get();

        $this->assertIsString($resp->body['id'] ?? null, 'expected string id');
    }

    public function testAccounts(): void
    {
        $client = $this->replayClient(
            'accounts/list',
            'accounts/get',
            'accounts/list_transactions',
            'accounts/get_transaction',
        );

        $page = $client->accounts->list();
        $this->assertNotEmpty($page->data, 'expected data rows');

        $accountId = FixtureIds::id('ZAZU_FIXTURE_ACCOUNT_ID');
        $client->accounts->get($accountId);

        $client->accounts->listTransactions($accountId);

        $txId = FixtureIds::id('ZAZU_FIXTURE_TRANSACTION_ID');
        $client->accounts->getTransaction($accountId, $txId);
    }

    public function testCustomers(): void
    {
        $client = $this->replayClient(
            'customers/list',
            'customers/get',
            'customers/create',
            'customers/update',
            'customers/delete',
        );

        $client->customers->list();

        $customerId = FixtureIds::id('ZAZU_FIXTURE_CUSTOMER_ID');
        $resp = $client->customers->get($customerId);
        $this->assertIsString($resp->body['id'] ?? null, 'expected string id');
    }

    public function testInvoices(): void
    {
        $client = $this->replayClient('invoices/list', 'invoices/get');

        $page = $client->invoices->list();
        $this->assertNotEmpty($page->data, 'expected data rows');

        $invoiceId = FixtureIds::id('ZAZU_FIXTURE_INVOICE_ID');
        $client->invoices->get($invoiceId);
    }

    public function testPaymentLinks(): void
    {
        $client = $this->replayClient(
            'payment_links/list',
            'payment_links/get',
            'payment_links/create',
            'payment_links/cancel',
        );

        $client->paymentLinks->list();

        $resp = $client->paymentLinks->create([
            'account_id' => FixtureIds::id('ZAZU_FIXTURE_ACCOUNT_ID'),
            'amount' => '100.00',
            'title' => 'SDK fixture',
            'description' => 'Created by zazu-ruby fixture spec',
            'link_type' => 'single',
        ]);
        $this->assertSame(201, $resp->status);

        $client->paymentLinks->cancel(FixtureIds::id('ZAZU_FIXTURE_CANCELLABLE_PAYMENT_LINK_ID'));
    }

    public function testCheckoutSessions(): void
    {
        $client = $this->replayClient('checkout_sessions/get');

        $resp = $client->checkoutSessions->get(FixtureIds::id('ZAZU_FIXTURE_CHECKOUT_SESSION_ID'));

        $this->assertIsString($resp->body['id'] ?? null, 'expected string id');
    }

    public function testWebhookEndpoints(): void
    {
        $client = $this->replayClient('webhook_endpoints/list', 'webhook_endpoints/get');

        $client->webhookEndpoints->list();
        $client->webhookEndpoints->get(FixtureIds::id('ZAZU_FIXTURE_WEBHOOK_ID'));

        $this->addToAssertionCount(1); // both calls matched their cassette interactions
    }

    public function testTransferDrafts(): void
    {
        $client = $this->replayClient('transfer_drafts/create', 'transfer_drafts/get');

        $resp = $client->transferDrafts->create([
            'account_id' => FixtureIds::id('ZAZU_FIXTURE_ACCOUNT_ID'),
            'beneficiary_id' => FixtureIds::id('ZAZU_FIXTURE_BENEFICIARY_ID'),
            'amount' => '150.00',
            'payment_reference' => 'SDK fixture',
        ]);
        $this->assertSame(201, $resp->status);
        $this->assertSame(
            'requested',
            $resp->body['status'] ?? null,
            'expected requested status (awaiting in-app approval)',
        );
        $this->assertArrayHasKey('transfer', $resp->body);
        $this->assertNull($resp->body['transfer'], 'expected null transfer before approval');

        $got = $client->transferDrafts->get(FixtureIds::id('ZAZU_FIXTURE_TRANSFER_DRAFT_ID'));
        $this->assertIsString($got->body['status'] ?? null, 'expected string status');
    }

    public function testBeneficiaries(): void
    {
        $client = $this->replayClient('beneficiaries/list', 'beneficiaries/get');

        $page = $client->beneficiaries->list();
        $this->assertNotEmpty($page->data, 'expected at least one beneficiary');
        $this->assertIsArray(
            $page->data[0]['external_accounts'] ?? null,
            'expected embedded external_accounts',
        );

        $resp = $client->beneficiaries->get(FixtureIds::id('ZAZU_FIXTURE_BENEFICIARY_ID'));
        $this->assertIsString($resp->body['id'] ?? null, 'expected string id');
    }
}
