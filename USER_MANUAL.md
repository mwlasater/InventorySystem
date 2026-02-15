# Lasater Salvage Inventory — User Manual

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started](#2-getting-started)
3. [Dashboard](#3-dashboard)
4. [Managing Items](#4-managing-items)
5. [Categories](#5-categories)
6. [Locations](#6-locations)
7. [Tags](#7-tags)
8. [Photos and Documents](#8-photos-and-documents)
9. [Transactions](#9-transactions)
10. [Search and Filters](#10-search-and-filters)
11. [QR Codes and Barcodes](#11-qr-codes-and-barcodes)
12. [Reports](#12-reports)
13. [Import and Export](#13-import-and-export)
14. [Trash and Item Recovery](#14-trash-and-item-recovery)
15. [User Profile](#15-user-profile)
16. [Administration](#16-administration)
17. [Troubleshooting](#17-troubleshooting)

---

## 1. Introduction

Lasater Salvage Inventory is a web-based inventory management system for tracking, organizing, and valuing personal or business collections. It provides tools for cataloging items with detailed metadata, managing storage locations, recording transactions, generating reports, and maintaining a full audit trail of all changes.

### Key Features

- **Item Management** — Create and maintain detailed records for each item including condition, brand, model, valuation, and acquisition history.
- **Hierarchical Organization** — Organize items by nested categories and storage locations.
- **Tagging** — Apply flexible tags to items for cross-category grouping.
- **Photo Gallery** — Attach multiple photos to each item with thumbnail generation and reordering.
- **Document Storage** — Upload receipts, certificates, and other supporting documents.
- **Transaction Tracking** — Record sales, trades, gifts, loans, and dispositions with financial details.
- **QR Code Labels** — Generate printable QR code labels for individual items or in batch.
- **Barcode Scanning** — Look up items by scanning barcodes with a device camera.
- **Reporting** — Generate collection summaries, valuation reports, transaction histories, and more. Export to CSV or PDF.
- **Import/Export** — Bulk import items from CSV files or export your entire inventory.
- **Multi-User Support** — Admin and standard user roles with activity tracking.
- **Audit Trail** — Every change is logged with before/after values, timestamps, and user attribution.

---

## 2. Getting Started

### Logging In

1. Navigate to the application URL in your web browser.
2. Enter your **username or email address** and **password**.
3. Optionally check **Remember Me** to stay logged in across browser sessions.
4. Click **Log In**.

If your account has been newly created by an administrator, you will be prompted to change your password on first login.

### Forgot Password

1. On the login page, click **Forgot your password?**
2. Enter the email address associated with your account.
3. Check your email for a password reset link.
4. Click the link and enter a new password.

### Account Lockout

After 5 consecutive failed login attempts, your account is temporarily locked for 15 minutes. Wait for the lockout period to expire before trying again, or contact an administrator to unlock your account.

---

## 3. Dashboard

The dashboard is the first screen you see after logging in. It provides an at-a-glance overview of your collection.

### Statistics

- **Total Items** — Number of items currently in your collection.
- **Total Value** — Sum of estimated values for all items.
- **Total Cost Basis** — Sum of purchase prices for all items.
- **Gain/Loss** — Difference between total value and cost basis.

### Widgets

- **Recent Items** — The most recently added items.
- **Loaned Items** — Items currently marked as loaned out, including overdue loans highlighted for attention.
- **Charts** — Visual breakdowns of your collection by category and value distribution.
- **Status Breakdown** — Count of items in each status (in collection, sold, loaned out, etc.).

---

## 4. Managing Items

### Viewing Items

Navigate to **Items** from the main menu. The item list supports:

- **Sorting** — Click column headers to sort by name, category, value, date added, etc.
- **Pagination** — Browse through pages of items.
- **Filtering** — Use the filter controls to narrow results (see [Search and Filters](#10-search-and-filters)).

### Creating a New Item

1. Click **Add Item** (or the equivalent button on the items page).
2. Fill in the item details:

| Field | Required | Description |
|---|---|---|
| **Name** | Yes | Item name (max 255 characters) |
| **Description** | No | Detailed description of the item |
| **Category** | Yes | Select from existing categories |
| **Location** | Yes | Storage location for the item |
| **Quantity** | Yes | Number of this item (minimum 1) |
| **Status** | Yes | `In Collection` or `Damaged` |
| **Condition** | No | New, Like New, Very Good, Good, Fair, Poor, or For Parts |
| **SKU** | No | Stock keeping unit (must be unique if provided) |
| **Barcode** | No | Item barcode number |
| **Brand** | No | Manufacturer or brand name |
| **Model Number** | No | Manufacturer model number |
| **Year Manufactured** | No | Year the item was made |
| **Color** | No | Item color |
| **Dimensions** | No | Physical dimensions |
| **Acquisition Date** | No | When you acquired the item |
| **Acquisition Source** | No | Where you acquired the item from |
| **Acquisition Method** | No | Purchased, Gift, Trade, Found, Inherited, or Other |
| **Purchase Price** | No | Amount paid for the item |
| **Purchase Currency** | No | Currency of purchase price (default: USD) |
| **Estimated Value** | No | Current estimated market value |
| **Valuation Date** | No | When the valuation was determined |
| **Valuation Source** | No | Source of the valuation (e.g., eBay, appraisal) |
| **Notes** | No | Additional notes |
| **Tags** | No | One or more tags for flexible categorization |

3. Click **Save**.

**Duplicate Detection:** When creating an item, the system checks for potential duplicates based on name, barcode, and SKU. If a potential duplicate is found, you will be warned but can choose to proceed.

### Editing an Item

1. Navigate to the item's detail page.
2. Click **Edit**.
3. Modify any fields as needed.
4. Click **Save**.

All changes are recorded in the audit trail with before and after values.

### Deleting an Item

1. Navigate to the item's detail page.
2. Click **Delete**.
3. Confirm the deletion.

Deleted items are moved to the **Trash** and can be recovered within 90 days (see [Trash and Item Recovery](#14-trash-and-item-recovery)).

### Favoriting Items

Click the favorite icon (star) on any item to toggle it as a favorite. Favorites provide quick access to frequently referenced items.

### Bulk Operations

1. From the item list, select multiple items using the checkboxes.
2. Use the bulk action controls to update fields across all selected items at once (e.g., change category, location, or status).

---

## 5. Categories

Categories provide a hierarchical way to classify items. Categories can be nested (e.g., Electronics > Audio > Speakers).

### Viewing Categories

Navigate to **Categories** from the main menu to see all categories in a hierarchical tree. Each category shows the count of items it contains.

### Creating a Category

1. Click **Add Category**.
2. Enter a **Name** and optional **Description**.
3. Optionally select a **Parent Category** to nest it under an existing category.
4. Click **Save**.

### Editing a Category

1. Click the edit icon next to the category.
2. Modify the name, description, or parent.
3. Click **Save**.

### Deleting a Category

1. Click the delete icon next to the category.
2. Confirm the deletion.

A category cannot be deleted if it still contains items. Reassign or delete those items first.

---

## 6. Locations

Locations represent physical storage places. Like categories, locations support a hierarchy (e.g., Warehouse > Shelf A > Bin 3).

### Viewing Locations

Navigate to **Locations** from the main menu to see all locations arranged hierarchically. Each location displays the count of items stored at that location and all of its sub-locations.

### Creating a Location

1. Click **Add Location**.
2. Enter a **Name** and optional **Description**.
3. Optionally select a **Parent Location** to nest it within a larger space.
4. Click **Save**.

### Editing a Location

1. Click the edit icon next to the location.
2. Modify the name, description, or parent.
3. Click **Save**.

### Reordering Locations

Locations can be reordered by dragging and dropping them within the list, or by using the reorder controls. This controls the display order only.

### Deleting a Location

1. Click the delete icon next to the location.
2. Confirm the deletion.

A location cannot be deleted if it still contains items. Move those items to another location first.

---

## 7. Tags

Tags are a flexible way to label items across categories. Unlike categories, an item can have multiple tags, and tags are flat (no hierarchy).

### Viewing Tags

Navigate to **Tags** from the main menu. Tags are displayed with the number of items each tag is applied to.

### Creating a Tag

Tags can be created in two ways:

- **From the tag management page** — Click **Add Tag** and enter a name.
- **While editing an item** — Type a new tag name in the tag input field. If the tag does not exist, it will be created automatically.

A URL-friendly slug is automatically generated from the tag name.

### Editing a Tag

1. Click the edit icon next to the tag.
2. Change the tag name.
3. Click **Save**.

### Merging Tags

If you have duplicate or similar tags, you can merge them:

1. Select the tags you want to merge.
2. Choose the target tag that should remain.
3. Click **Merge**. All items tagged with the removed tags will be reassigned to the target tag.

### Deleting a Tag

1. Click the delete icon next to the tag.
2. Confirm the deletion.

Deleting a tag removes it from all items but does not delete the items themselves.

---

## 8. Photos and Documents

### Photos

Each item can have multiple photos. One photo is designated as the **primary** photo and is used as the thumbnail wherever the item is displayed.

**Uploading Photos:**

1. Navigate to the item's detail page.
2. In the Photos section, click **Upload Photo** or drag and drop image files.
3. Thumbnails are automatically generated in small and medium sizes.

**Managing Photos:**

- **Set Primary** — Click the star/primary icon on a photo to make it the main display image.
- **Reorder** — Drag and drop photos to change their display order.
- **Edit Caption** — Click on a photo's caption area to add or edit a description.
- **Delete** — Click the delete icon on a photo to remove it.

### Documents

Items can have supporting documents attached (receipts, certificates of authenticity, manuals, etc.).

**Uploading Documents:**

1. Navigate to the item's detail page.
2. In the Documents section, click **Upload Document**.
3. Select the file and optionally provide a label describing the document.

**Supported formats for in-browser viewing:** PDF, JPEG, PNG, GIF, WebP. Other file types can still be uploaded and downloaded.

**Managing Documents:**

- **Download** — Click the download icon to save a copy of the document.
- **Delete** — Click the delete icon to remove the document.

---

## 9. Transactions

Transactions record what happens to items when they leave your collection or change status. Every transaction is linked to an item and automatically updates that item's status.

### Transaction Types

| Type | Description |
|---|---|
| **Sold** | Item was sold. Records sale price, shipping cost, platform, and net proceeds. |
| **Given Away** | Item was given to someone as a gift. |
| **Traded** | Item was traded for something else. |
| **Loaned Out** | Item was temporarily loaned. Records expected return date. |
| **Returned** | A previously loaned item was returned. |
| **Lost** | Item is lost. |
| **Disposed** | Item was thrown away or recycled. |
| **Status Correction** | Correct a previous status without a physical disposition. |

### Creating a Transaction

1. Navigate to the item's detail page.
2. Click **Record Transaction** (or similar button).
3. Select the **Transaction Type**.
4. Fill in the relevant fields:
   - **Transaction Date** — When the transaction occurred.
   - **Recipient Name** — Who received the item (for sold, given, traded, loaned).
   - **Recipient Contact** — Contact information for the recipient.
   - **Sale Price** — Amount received (for sales).
   - **Shipping Cost** — Shipping expenses (for sales).
   - **Platform** — Where the sale occurred (e.g., eBay, Craigslist).
   - **Net Proceeds** — Calculated from sale price minus shipping cost.
   - **Expected Return Date** — When a loaned item should be returned.
   - **Notes** — Additional details about the transaction.
5. Click **Save**.

The item's status is automatically updated to match the transaction type.

### Loan Tracking

Items with the status **Loaned Out** appear on the dashboard. Loans past their expected return date are flagged as **overdue**.

---

## 10. Search and Filters

### Quick Search

Use the search bar at the top of the items page to perform a full-text search across item names, descriptions, and notes. Results are returned in relevance order.

### Advanced Filters

The items list supports filtering by multiple criteria:

- **Category** — Filter by one or more categories.
- **Location** — Filter by storage location.
- **Status** — Filter by item status (In Collection, Sold, Loaned Out, etc.).
- **Condition** — Filter by condition rating.
- **Tags** — Filter by assigned tags.
- **Favorites** — Show only favorited items.
- **Date Range** — Filter by acquisition date or date added.
- **Value Range** — Filter by estimated value or purchase price.

Filters can be combined. For example, you can view all items in the "Electronics" category at "Warehouse A" with condition "Good" or better.

### Saved Filters

If you frequently use the same combination of filters:

1. Set up your desired filters.
2. Click **Save Filter**.
3. Give the filter a descriptive name.
4. The filter will appear in your saved filters list for one-click access in the future.

To delete a saved filter, click the delete icon next to it.

---

## 11. QR Codes and Barcodes

### QR Code Labels

Generate printable QR code labels for your items. When scanned, the QR code links directly to the item's detail page in the application.

**Single Item QR Label:**

1. Navigate to the item's detail page.
2. Click **QR Label** (or similar button).
3. A printable label is generated with the item's QR code, name, and key details.
4. Print the label and affix it to the item or its storage location.

**Batch QR Labels:**

1. From the item list, select the items you want labels for.
2. Click **Generate QR Labels** (batch action).
3. A printable page of labels is generated for all selected items.

### Barcode Scanning

If your device has a camera:

1. Click the **Scan Barcode** option.
2. Point the camera at an item's barcode.
3. The system will look up the barcode and display the matching item, or offer to create a new item if no match is found.

---

## 12. Reports

Navigate to **Reports** from the main menu to access the following reports. All reports can be exported to **CSV** or **PDF**.

### Collection Summary

A breakdown of your collection by category showing item counts and total value per category.

### Valuation Report

A comprehensive financial report showing:

- Total estimated value of all items.
- Total cost basis (purchase prices).
- Overall gain or loss.
- Value breakdown by category.

### Transaction History

A chronological list of all transactions (sales, trades, loans, etc.) with filtering options by date range, transaction type, and item.

### Location Inventory

A report showing what items are stored at each location, organized by the location hierarchy.

### Status Breakdown

A summary of items grouped by their current status (In Collection, Sold, Loaned Out, Lost, etc.) with counts and values.

### Acquisition History

A timeline view of when items were acquired, grouped by time period. Useful for understanding collection growth patterns.

---

## 13. Import and Export

### Importing Items from CSV

1. Navigate to **Import** from the main menu.
2. Click **Upload CSV** and select your CSV file.
3. **Column Mapping** — The system attempts to automatically match your CSV columns to item fields. Review and adjust the mappings as needed. The system uses fuzzy matching to suggest likely field matches.
4. **Preview and Validate** — Review a preview of the data that will be imported. The system validates all rows and highlights any errors (missing required fields, invalid values, etc.).
5. **Execute Import** — Once validation passes, click **Import** to create the items.

**CSV Requirements:**

- The first row should contain column headers.
- UTF-8 encoding is recommended (BOM is handled automatically).
- Required columns depend on your field mapping, but at minimum you need: Name, Category, Location, and Quantity.

### Exporting Items to CSV

1. Navigate to **Export** from the main menu.
2. Configure which fields to include in the export.
3. Click **Export**.
4. A CSV file is downloaded containing your inventory data.

---

## 14. Trash and Item Recovery

When items are deleted, they are moved to the trash rather than being permanently removed.

### Viewing Deleted Items

Navigate to **Trash** from the main menu to see all deleted items.

### Restoring an Item

1. Find the item in the trash list.
2. Click **Restore**.
3. The item is returned to your collection with all its data intact.

### Permanent Deletion

1. Find the item in the trash list.
2. Click **Delete Permanently**.
3. Confirm the action. This cannot be undone.

### Automatic Purge

Items in the trash for more than **90 days** are automatically and permanently deleted by the system.

---

## 15. User Profile

### Editing Your Profile

1. Click your username or profile icon in the navigation bar.
2. Select **Profile**.
3. Update your **Display Name**, **Email**, or **Username**.
4. Click **Save**.

### Changing Your Password

1. Navigate to your **Profile** page.
2. In the password section, enter your **Current Password**.
3. Enter and confirm your **New Password**.
4. Click **Update Password**.

---

## 16. Administration

Administration features are available only to users with the **Admin** role.

### User Management

Navigate to **Admin > Users** to manage user accounts.

**Creating a User:**

1. Click **Add User**.
2. Fill in username, email, display name, and password.
3. Assign a role (**Admin** or **User**).
4. Click **Save**.

New users are required to change their password on first login.

**Editing a User:**

1. Click the edit icon next to the user.
2. Modify account details, role, or permissions.
3. Toggle the **Active** status to enable or disable the account.
4. Click **Save**.

**Resetting a User's Password:**

1. Click the reset password option for the user.
2. Enter a new temporary password.
3. The user will be required to change it on next login.

### Activity Log

Navigate to **Admin > Activity Log** to view a system-wide audit trail. This log records:

- Item creation, updates, and deletions (with before/after values).
- User login, logout, and failed login attempts.
- All actions include timestamps, the user who performed the action, and their IP address.

You can filter the log by user, entity type, action type, and date range.

**Per-User Activity:**

Click on a specific user in the user management list to view their individual activity history.

---

## 17. Troubleshooting

### I can't log in

- Verify you are entering the correct username or email and password.
- Check if your account is locked out due to too many failed attempts. Wait 15 minutes and try again.
- Contact an administrator to verify your account is active and to reset your password if needed.

### An item I deleted is gone

- Check the **Trash** — deleted items are held there for 90 days.
- If more than 90 days have passed, the item has been permanently purged and cannot be recovered.

### My CSV import is failing

- Ensure the CSV file uses UTF-8 encoding.
- Verify the first row contains column headers.
- Check that required fields (Name, Category, Location, Quantity) are present and correctly mapped.
- Review the validation errors shown during the preview step for specific row-level issues.

### Photos are not displaying

- Ensure the storage symlink has been created (this is a setup task for the system administrator).
- Check that the uploaded file is a supported image format (JPEG, PNG, GIF, WebP).

### I can't access admin features

- Admin features are restricted to accounts with the **Admin** role. Contact an existing administrator to upgrade your account.

### The dashboard charts are not loading

- Ensure JavaScript is enabled in your browser.
- Try clearing your browser cache and reloading the page.
- Check that you are using a modern browser (Chrome, Firefox, Safari, or Edge).
