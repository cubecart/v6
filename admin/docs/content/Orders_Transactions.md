# Transaction Logs

A global view of all payment transactions across your store, grouped by order.

## Transaction List

| Column | Description |
| --- | --- |
| Order ID | Click to view all transactions for that order. Sortable. |
| Amount | Transaction amount in store currency. Sortable. |
| Gateway | The payment processor that handled the transaction. Sortable. |
| Date | When the transaction was recorded. Sortable. |

### Clear Log

A **Clear Log** button appears in the top-right corner when transactions exist. This permanently deletes all transaction log entries and redirects back to the transactions page.

## Search

A search box is provided above the transaction list. Search behaviour depends on the input:

- **Order number** -- If the search term matches a valid order ID format, it performs an exact match on the order ID column.
- **Transaction ID, amount, or gateway** -- Otherwise, the term is matched as a partial (contains) search across the transaction ID, amount, and gateway fields.

## Order Transaction Detail

Click an order in the list to see all its transactions:

| Column | Description |
| --- | --- |
| Transaction ID | The reference from the payment gateway. |
| Status | Transaction status (e.g. Pending, Success, Failed). |
| Amount | The transaction amount. |
| Gateway | Which payment processor handled it. |
| Date/Time | When the transaction occurred. |
| Notes | Any messages from the payment gateway. |

> [!NOTE]
> The order transaction detail view is read-only. Refund and capture actions are available on the Transaction Logs tab within the individual order edit page, not on this global view.
