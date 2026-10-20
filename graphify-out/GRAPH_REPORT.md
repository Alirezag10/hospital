# Graph Report - hospital  (2026-10-20)

## Corpus Check
- 102 files · ~26,080 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 438 nodes · 697 edges · 46 communities (12 shown, 34 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 30 edges (avg confidence: 0.87)
- Token cost: local AST uses 0 API tokens; semantic extraction used this Codex session. Exact session token counts are unavailable.

## Graph Freshness
- Built from commit: `a25eeacf`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Authorization and clinical access
- User accounts and doctor profiles
- Patient and discharge controllers
- Staff workflow documentation
- Admission queries and calendar
- Composer and autoloading
- Form validation and searches
- Jalali date picker
- ActiveForm treatment inputs
- Hospital database schema
- Staff workflow integration tests
- Prescription items and forms
- Jalali output formatting
- Frontend asset loading
- Searchable record selectors
- Service catalog model
- Development debug configuration
- Persian grid numbering
- Historical date-input documentation
- Clinical cost calculations
- Yii software licensing
- Font redistribution license
- In-page confirmations

## God Nodes (most connected - your core abstractions)
1. `User` - 31 edges
2. `Admission` - 26 edges
3. `Access` - 24 edges
4. `Patient` - 24 edges
5. `FormInput` - 21 edges
6. `RoleController` - 21 edges
7. `Doctor` - 19 edges
8. `Discharge` - 16 edges
9. `Staff login and account administration guide` - 16 edges
10. `render()` - 15 edges

## Surprising Connections (you probably didn't know these)
- `Access.php permission definitions and controller AccessControl` --references--> `Access`  [EXTRACTED]
  docs/staff-guide.md → app/components/Access.php
- `Original single-operator workflow (outdated README)` --conceptually_related_to--> `Login interface and disabled public registration`  [AMBIGUOUS]
  README.md → docs/staff-guide.md
- `doctor_id isolation across lists, search, direct URLs, and POST` --implements--> `Doctor access restricted to assigned admissions`  [INFERRED]
  docs/staff-guide.md → PRODUCT.md
- `Login interface and disabled public registration` --implements--> `Administrator-only account creation and server authorization`  [INFERRED]
  docs/staff-guide.md → PRODUCT.md
- `Retain staff accounts instead of deleting them` --rationale_for--> `Preserve existing costs and data`  [INFERRED]
  docs/staff-guide.md → PRODUCT.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Role-based admission, treatment, billing, and discharge flow** — docs_staff_guide_role_permissions, readme_admission_workflow, docs_staff_guide_prescriptions, readme_discharge_billing, docs_staff_guide_treatment_print [INFERRED 0.95]

## Communities (46 total, 34 thin omitted)

### Community 0 - "Authorization and clinical access"
Cohesion: 0.05
Nodes (10): Access, Billing, {closure#1}(), RoleController, ServiceAmount, AdmissionServiceController, ClinicalController, DischargeController (+2 more)

### Community 1 - "User accounts and doctor profiles"
Cohesion: 0.06
Nodes (9): demoUser(), FormInput, DoctorController, UserController, WardController, Doctor, User, UserForm (+1 more)

### Community 2 - "Patient and discharge controllers"
Cohesion: 0.06
Nodes (5): AdmissionController, PatientController, SiteController, Discharge, Patient

### Community 3 - "Staff workflow documentation"
Cohesion: 0.08
Nodes (23): Access.php permission definitions and controller AccessControl, CLI first-administrator bootstrap, User account linked to an existing or new doctor profile, 130-check staff workflow tests on an empty _test database, Login interface and disabled public registration, Prescription services, medicines, and consumables, Four-role capability matrix, Account and credential changes invalidate existing sessions (+15 more)

### Community 5 - "Composer and autoloading"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 7 - "Form validation and searches"
Cohesion: 0.11
Nodes (4): AdmissionSearch, LoginForm, PatientSearch, {closure#1}()

### Community 8 - "Jalali date picker"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 10 - "Hospital database schema"
Cohesion: 0.28
Nodes (13): admission_services, admissions, discharges, doctors, `patients`, prescription_items, prescriptions, services (+5 more)

### Community 12 - "Staff workflow integration tests"
Cohesion: 0.36
Nodes (3): Browser, check(), createStaff()

### Community 16 - "Searchable record selectors"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 19 - "Development debug configuration"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 22 - "Historical date-input documentation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Ambiguous Edges - Review These
- `Original single-operator workflow (outdated README)` → `Login interface and disabled public registration`  [AMBIGUOUS]
  README.md · relation: conceptually_related_to

## Knowledge Gaps
- **26 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+21 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 205 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **34 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **What is the exact relationship between `Original single-operator workflow (outdated README)` and `Login interface and disabled public registration`?**
  _Edge tagged AMBIGUOUS (relation: conceptually_related_to) - confidence is low._
- **Why does `Access` connect `Authorization and clinical access` to `Staff workflow documentation`, `Patient and discharge controllers`, `Admission list actions`, `Admission queries and calendar`?**
  _High betweenness centrality (0.121) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Patient` (e.g. with `.search()` and `.search()`) actually correct?**
  _`Patient` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _26 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Authorization and clinical access` be split into smaller, more focused modules?**
  _Cohesion score 0.05182443151771549 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User accounts and doctor profiles` to `Authorization and clinical access`, `Patient and discharge controllers`, `Admission queries and calendar`, `Form validation and searches`, `ActiveForm treatment inputs`, `Staff and patient grids`, `Admission list actions`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Should `User accounts and doctor profiles` be split into smaller, more focused modules?**
  _Cohesion score 0.06431372549019608 - nodes in this community are weakly interconnected._
## Update Integrity Notes
- Indexed the current working tree, including uncommitted staff/login/clinical changes; source rows and credentials in the live database are not part of this source-code graph.
- AST limitation: 36 changed code files yielded no symbols (mostly entry/configuration/view boilerplate). Shared views remain represented where the extractor emits file or reference nodes.
- Graph health warning: raw extraction has 6 self-loops and 25 parallel relations sharing undirected endpoint pairs. Distinct original relation records are preserved in graph.json (722 links); clustering uses 697 topology edges.
- No missing or dangling endpoints were found in fresh extraction.
- Documentation drift: README.md still describes no login and seven tables. PRODUCT.md, docs/staff-guide.md and the current code describe four roles and ten tables. The outdated claims are labeled as historical rather than treated as current application behavior.
- Exact semantic token usage was not exposed by the subagent tool; no estimate is reported as a measured zero.
