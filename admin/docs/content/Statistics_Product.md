# Product Statistics

Detailed sales data for an individual product.

## Header

The page header shows the **product image** (if available), the **product name and code**, and an **Edit Product** button linking to the product editor.

## Overview

| Field | Description |
| --- | --- |
| Date Added | When the product was created. |
| Last Updated | When the product was last modified. |
| First Sale | Date of the earliest order containing this product. |
| Last Sale | Date of the most recent order containing this product. |
| Total Sales | Total units sold. Click the number to view the matching orders. If the product averages more than one unit per order, the average per order is shown in parentheses. |
| Sale Interval | Average time between sales, displayed as days, hours, minutes, and seconds. Calculated from the product's creation date to now, divided by the number of sales. |

## Date Range Filter

Use the **From** and **To** dropdown selectors (day, month, year) to narrow the statistics to a specific period, then click **Go**. The From date defaults to the first sale date and the To date defaults to today.

When a date filter is active, a **Reset** link appears to clear the filter and return to the full date range.

## Customers Who Purchased

A paginated list of customers who have bought this product (from Processing and Completed orders), showing:

| Column | Description |
| --- | --- |
| Customer Name | First and last name, linked to the customer account editor. |
| Email | Customer email address, displayed as a `mailto:` link. |
| Purchases | Total quantity of this product purchased by the customer. |
