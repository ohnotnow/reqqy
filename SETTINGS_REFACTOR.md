# Settings UI Refactor - Phase 4

## Overview

The Settings page currently displays all applications in a single flat list. With the three-category application system (Internal, External, Proposed), we need a more intuitive UX that:
- Visually separates the three categories
- Shows category-appropriate fields in forms
- Provides promote/reject actions for Proposed applications
- Filters the "New Feature" dropdown to only show Internal applications

## Current State

### Existing Implementation
- **SettingsPage component**: Single list showing all applications
- **CRUD operations**: Create, Read, Update, Delete via flyout modals
- **Application categories**: Internal, External, Proposed (added in Phase 1-3)
- **No visual separation**: All categories mixed together in one list

### What Works Well
- ✅ Full CRUD functionality
- ✅ Flyout modal pattern is clean and consistent
- ✅ Form validation in place
- ✅ Good test coverage (18 tests)

### What Needs Improvement
- ❌ No visual separation between categories
- ❌ All fields shown for all categories (confusing - External apps don't need repos)
- ❌ No way to promote Proposed apps to Internal
- ❌ No way to reject Proposed apps
- ❌ "New Feature" dropdown shows all applications (should only show Internal)

## Application Categories Recap

### Internal
- Apps owned/managed by the organization
- Can have feature requests
- **Fields needed**: name, short_description, url, repo, status, is_automated
- **User actions**: Edit all fields, Delete

### External
- Third-party SaaS/tools for reference
- Cannot have feature requests (for LLM context only)
- **Fields needed**: name, short_description, url
- **User actions**: Edit these fields only, Delete

### Proposed
- Ideas from conversations awaiting admin approval
- Staging area before becoming Internal
- **Fields needed**: name, short_description (auto-populated from conversation)
- **User actions**: Promote to Internal, Reject (delete), Edit name/description

## Proposed Solution

### UI Design: Tab-Based Layout

```
┌─────────────────────────────────────────────────────────┐
│ Settings                                                 │
├─────────────────────────────────────────────────────────┤
│ ┌──────────┬──────────┬──────────┐                      │
│ │ Internal │ External │ Proposed │                      │
│ └──────────┴──────────┴──────────┘                      │
│                                                          │
│ [+ Add Internal Application]                            │
│                                                          │
│ ┌───────────────────────────────────┐                   │
│ │ Application Name                  │   [Edit] [Delete] │
│ │ Short description here...         │                   │
│ │ Status: Active | Automated: Yes   │                   │
│ └───────────────────────────────────┘                   │
│                                                          │
│ ┌───────────────────────────────────┐                   │
│ │ Another App                       │   [Edit] [Delete] │
│ │ Description...                    │                   │
│ └───────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────┘
```

### Key Features

1. **Flux Tabs Component**
   - Three tabs: "Internal Apps (5)", "External Apps (3)", "Proposed Apps (2)"
   - Tab badges show count of applications in each category
   - Active tab state tracked in Livewire component

2. **Category-Specific Forms**
   - "Add Application" button changes label based on active tab
   - Forms show/hide fields based on category
   - Validation rules adjusted per category

3. **Proposed Application Actions**
   - "Promote to Internal" button → opens modal to fill in remaining fields (repo, status, url)
   - "Reject" button → confirmation dialog, then soft delete (or hard delete?)
   - Success messages and redirect to appropriate tab

4. **HomePage Dropdown Filter**
   - Only show applications where `canHaveFeaturesRequested()` returns true
   - This means only Internal applications

## Implementation Plan

### Step 1: Add Tab Functionality to SettingsPage Component
- [X] Add `$activeTab` property to track current tab (default: 'internal')
- [X] Add `setActiveTab(string $tab)` method to switch tabs
- [X] Add computed properties: `internalApplications()`, `externalApplications()`, `proposedApplications()`
- [X] Filter applications by category for each tab
- [X] Add `$formCategory` property to track which category form is being used
- [X] Add `getValidationRules()` method for category-specific validation

### Step 2: Update Settings Blade View with Tabs
- [X] Add Flux tabs component
- [X] Three tabs: Internal, External, Proposed
- [X] Add count badges to each tab label
- [X] Show active tab content with filtered applications
- [X] Update "Add Application" button to pre-set category based on active tab
- [X] Show source conversation link on Proposed applications

### Step 3: Category-Specific Form Fields
- [X] Create separate form sections for each category in the modal
- [X] Use conditional rendering (`@if($formCategory === 'internal')`) to show/hide fields
- [X] Internal: all fields visible
- [X] External: only name, short_description, url
- [X] Proposed: only name, short_description (when editing)
- [X] Update validation rules to match category requirements

### Step 4: Promote/Reject Functionality for Proposed Apps
- [X] Add "Promote to Internal" button to Proposed app cards
- [X] Create `promoteToInternal(Application $application)` method
- [X] Show modal to collect: status, repo, url, is_automated
- [X] Update application category and fields
- [X] Redirect to Internal tab after promotion
- [X] Add "Reject" button with confirmation dialog (uses existing delete functionality)
- [X] Hard delete confirmed (no soft deletes)
- [X] Name and description locked (readonly/disabled) in promotion modal

### Step 5: Update HomePage Application Dropdown
- [X] Filter applications to only show Internal category
- [X] Update query: `Application::where('category', ApplicationCategory::Internal)->get()`
- [X] External and Proposed apps will not appear in dropdown

### Step 6: Update Tests
- [X] Test tab switching and filtering (updated existing tests)
- [X] Test "Add Application" pre-fills correct category (formCategory property tested)
- [X] Test category-specific form fields show/hide correctly (validation rules tested per category)
- [X] Test validation rules per category (Internal requires status, External/Proposed don't)
- [X] Test Promote to Internal workflow (tested via model method)
- [X] Test Reject workflow (uses existing delete functionality - already tested)
- [X] Test HomePage dropdown only shows Internal apps (filtered query tested)
- [X] Ensure existing tests still pass - all 124 tests passing with 329 assertions!

### Step 7: Polish and Refinements
- [X] Empty state per tab with generic messaging (consistent across all tabs)
- [X] Sort Proposed apps by creation date (newest first)
- [X] Show source conversation link on Proposed applications
- [X] Badge/icon to indicate automated status (already present in Internal tab)
- [X] Format with Pint
- [X] Fix Flux tabs structure (was using incorrect components causing invisible tabs)
- [X] Final manual testing in browser - confirmed working perfectly!

## Technical Decisions Made

### 1. Reject vs Delete
**Decision: Hard Delete** ✅
- Permanently removes the Proposed application via existing delete functionality
- Simpler implementation (no new code needed)
- Confirmation dialog prevents accidental deletions
- Can add soft deletes later if audit trail is needed

### 2. Promote Modal Flow
**Decision: Pre-fill with locked Proposal Data** ✅
- Name and short_description shown as readonly/disabled inputs
- Only collect new fields: status, repo, url, is_automated, overview
- Clean UX with `flux:separator` dividing locked fields from editable fields
- Prevents confusion about what can be changed

### 3. Tab State Persistence
**Decision: Use #[Url] Attribute** ✅
- Active tab in query string: `/settings?tab=proposed`
- Shareable links to specific tabs (admin can send `/settings?tab=proposed` to colleague)
- Persistent across page refreshes
- Better UX for admins working with Proposed apps

## Success Criteria

- ✅ **Three-category system is visually clear and intuitive** - Tabs with count badges, category-specific fields
- ✅ **Admins can easily review and promote Proposed applications** - Promote button, locked name/description, fillable Internal fields
- ✅ **Form validation matches category requirements** - Internal requires status, External/Proposed don't
- ✅ **Feature request dropdown only shows Internal applications** - HomePage filtered to `ApplicationCategory::Internal`
- ✅ **All tests passing with refactored UI** - 124 tests, 329 assertions, all green
- ✅ **No regressions in existing functionality** - All existing tests updated and passing
- ✅ **Tab state persists in URL for shareable links** - `#[Url]` attribute on `$activeTab`

## Implementation Summary

**Completed in:** ~2.5 hours (faster than estimated 3.5-5 hours!)

**Files Changed:**
- `app/Livewire/SettingsPage.php` - Added tab system, category-aware validation, promote functionality
- `resources/views/livewire/settings-page.blade.php` - Complete rewrite with Flux tabs and category-specific forms
- `app/Livewire/HomePage.php` - Filtered applications to Internal category only
- `tests/Feature/Livewire/SettingsPageTest.php` - Updated all tests for new tab-based system

**Key Features:**
1. Three tabs with application counts: Internal (5), External (3), Proposed (2)
2. Category-specific "Add Application" buttons per tab
3. Dynamic form fields based on category
4. Promote to Internal modal with locked name/description
5. Reject button with confirmation (hard delete)
6. Source conversation link on Proposed apps
7. Automated badge on Internal apps
8. URL-based tab state (`?tab=proposed`)

**Testing:**
- All 124 tests passing (18 SettingsPage, 106 others)
- 329 total assertions
- Zero regressions

## Estimated Effort

- **Step 1-2 (Tabs)**: 30-45 minutes
- **Step 3 (Forms)**: 30-45 minutes
- **Step 4 (Promote/Reject)**: 45-60 minutes
- **Step 5 (HomePage Filter)**: 15 minutes
- **Step 6 (Tests)**: 60-90 minutes
- **Step 7 (Polish)**: 30 minutes

**Total**: 3.5-5 hours

## Questions to Answer Before Starting

1. Hard delete or soft delete for rejected Proposed apps?
2. Should we allow editing name/description when promoting, or lock those fields?
3. Any specific empty state messages you want for each tab?

---

Ready to start when you are! Let me know if you want to adjust the plan or if you have answers to the questions above.
