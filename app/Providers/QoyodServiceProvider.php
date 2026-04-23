<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Qoyod\QoyodClient;

use App\Services\Qoyod\Contracts\QoyodClientInterface;
use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use App\Services\Qoyod\Contracts\Resources\BillPaymentResourceInterface;
use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CreditNoteResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\DebitNoteResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoicePaymentResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;
use App\Services\Qoyod\Contracts\Resources\PurchaseOrdersResourceInterface;
use App\Services\Qoyod\Contracts\Resources\QuoteResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;
use App\Services\Qoyod\Contracts\Resources\TestResourceInterface;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;

use App\Services\Qoyod\Resources\Accounts\CachedAccounts;
use App\Services\Qoyod\Resources\Customers\CachedCustomers;
use App\Services\Qoyod\Resources\Inventories\CachedInventories;
use App\Services\Qoyod\Resources\Invoices\CachedInvoices;
use App\Services\Qoyod\Resources\Invoices\InvoicePayments\CachedInvoicePayments;
use App\Services\Qoyod\Resources\JournalEntries\CachedJournalEntries;
use App\Services\Qoyod\Resources\Notes\CreditNotes\CachedCreditNotes;
use App\Services\Qoyod\Resources\Notes\DebitNotes\CachedDebitNotes;
use App\Services\Qoyod\Resources\Products\CachedProducts;
use App\Services\Qoyod\Resources\Products\Categories\CachedCategories;
use App\Services\Qoyod\Resources\Products\Units\CachedUnits;
use App\Services\Qoyod\Resources\Purchases\BillPayments\CachedBillPayments;
use App\Services\Qoyod\Resources\Purchases\PurchaseOrders\CachedPurchaseOrders;
use App\Services\Qoyod\Resources\Purchases\Bills\CachedBills;
use App\Services\Qoyod\Resources\Quotes\CachedQuotes;
use App\Services\Qoyod\Resources\Receipts\CachedReceipts;
use App\Services\Qoyod\Resources\Test\Test;
use App\Services\Qoyod\Resources\Vendors\CachedVendors;

class QoyodServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QoyodClientInterface::class, function () {
            return new QoyodClient;
        });

        // 1 Cached Accounts 
        $this->app->bind(AccountResourceInterface::class, CachedAccounts::class);

        // 2 Cached Categories 
        $this->app->bind(CategoryResourceInterface::class, CachedCategories::class);

        // 3 Cached Units
        $this->app->bind(ProductUnitResourceInterface::class, CachedUnits::class);

        // 4 Cached Products 
        $this->app->bind(ProductResourceInterface::class, CachedProducts::class);

        // 5 Cached Inventories
        $this->app->bind(InventoryResourceInterface::class, CachedInventories::class);

        // 6 Cached Vendors
        $this->app->bind(VendorResourceInterface::class, CachedVendors::class);

        // 7 Cached Purchase Orders
        $this->app->bind(PurchaseOrdersResourceInterface::class, CachedPurchaseOrders::class);

        // 8 Cached Bills 
        $this->app->bind(BillResourceInterface::class, CachedBills::class);

        // 9 Cached Bill Payments 
        $this->app->bind(BillPaymentResourceInterface::class, CachedBillPayments::class);

        // 10 Cached Debit Notes 
        $this->app->bind(DebitNoteResourceInterface::class, CachedDebitNotes::class);

        // 11 Cached Customers 
        $this->app->bind(CustomerResourceInterface::class, CachedCustomers::class);

        // 12 Cached Quotes 
        $this->app->bind(QuoteResourceInterface::class, CachedQuotes::class);

        // 13 Cached Invoices 
        $this->app->bind(InvoiceResourceInterface::class, CachedInvoices::class);

        // 14 Cached Invoice Payments 
        $this->app->bind(InvoicePaymentResourceInterface::class, CachedInvoicePayments::class);

        // 15 Cached Credit Notes 
        $this->app->bind(CreditNoteResourceInterface::class, CachedCreditNotes::class);

        // 16 Cached Receipts 
        $this->app->bind(ReceiptResourceInterface::class, CachedReceipts::class);

        // 17 Cached Journal Entries 
        $this->app->bind(JournalEntrieResourceInterface::class, CachedJournalEntries::class);

        // Test Resource Bindings
        $this->app->bind(TestResourceInterface::class, Test::class);
    }

    public function boot(): void {}
}
