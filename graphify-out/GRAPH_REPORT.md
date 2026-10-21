# Graph Report - hospital  (2026-10-21)

## Corpus Check
- 121 files · ~30,897 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 563 nodes · 922 edges · 44 communities (15 shown, 29 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 70 edges (avg confidence: 0.87)
- Agent token usage: not exposed by this environment; no external LLM API was called.

## Community Hubs (Navigation)
- Request Routing and Control
- Hospital Workflow Documentation
- Admission Billing and Receipts
- Staff Accounts and Profiles
- Clinical Access and Corrections
- Patient Validation and Search
- Role Dashboards and Login
- Clinical Models and Relations
- Composer Dependencies
- Jalali Calendar Interaction
- Final Database Schema
- Admission Actions and Approval
- Clinical Forms and Selection
- Prescription Items and Catalog
- Persian Date Formatting
- Assets and Bootstrap Integration
- Patient Autocomplete
- Live Dashboard Refresh
- Debug Toolbar Configuration
- Persian Row Numbers
- Prescription Row Interaction
- Historical Date Input Explanation
- In-app Confirmation Dialog
- Medical Catalog Search
- Yii License
- Font License

## God Nodes (most connected - your core abstractions)
1. `Admission` - 36 edges
2. `Access` - 32 edges
3. `User` - 28 edges
4. `Doctor` - 20 edges
5. `Patient` - 15 edges
6. `render()` - 15 edges
7. `PrescriptionItem` - 13 edges
8. `UserForm` - 13 edges
9. `FormInput` - 12 edges
10. `AdmissionController` - 12 edges

## Surprising Connections (you probably didn't know these)
- `Visible-page dashboard and discharge queue refresh every 30 seconds` --semantically_similar_to--> `app/web/js/live-panel.js non-overlapping refresh requests`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Historical proposal: visible-page discharge queue refresh` --semantically_similar_to--> `app/web/js/live-panel.js non-overlapping refresh requests`  [INFERRED] [semantically similar]
  graphify-out/memory/query_20261021_112042_8ceea6c7_پروژه_رو_برسی_کن_و_ایده_برای_بهبود_بده.md → docs/staff-guide.md
- `Optional unique doctors.user_id foreign key to users.id` --semantically_similar_to--> `User.doctor and computed User.doctorId use doctors.user_id`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md
- `Transactional corrections with reason, actor, time and before/after audit` --semantically_similar_to--> `record_changes correction audit and HTTP 409 conflicts`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Admission, clinical approval and final receipt integrity** — product_admission_lock, product_treatment_approval, readme_concurrency, readme_receipt_snapshot [EXTRACTED 1.00]

## Communities (44 total, 29 thin omitted)

### Community 0 - "Request Routing and Control"
Cohesion: 0.07
Nodes (5): RoleController, DoctorController, MedicalItemController, PatientController, MedicalItem

### Community 1 - "Hospital Workflow Documentation"
Cohesion: 0.06
Nodes (29): SiteController::actionIndex passes prepared data to the dashboard view, Administrator-only account creation and no public registration, GET /site/dashboard returns authorized uncached JSON fragments, DashboardData prepares role-specific dashboard data, Shared DischargeQueue ready-admission queue, User.doctor and computed User.doctorId use doctors.user_id, Single database/install.sql for empty databases; no separate upgrade script, Admission::invalidateTreatmentApproval() shared treatment-change logic (+21 more)

### Community 2 - "Admission Billing and Receipts"
Cohesion: 0.06
Nodes (8): AdmissionData, AdmissionRegistration, Billing, DischargeQueue, ReceiptSnapshot, ServiceAmount, DischargeController, Admission

### Community 3 - "Staff Accounts and Profiles"
Cohesion: 0.06
Nodes (7): FormInput, UserController, Doctor, User, {closure#1}(), UserForm, yii\db\BaseActiveRecord::save()

### Community 4 - "Clinical Access and Corrections"
Cohesion: 0.08
Nodes (8): Access, {closure#1}(), AdmissionServiceController, ClinicalController, RecordChangeController, ServiceController, RecordChange, Service

### Community 5 - "Patient Validation and Search"
Cohesion: 0.07
Nodes (5): AdmissionSearch, CorrectionForm, Discharge, Patient, PatientSearch

### Community 6 - "Role Dashboards and Login"
Cohesion: 0.08
Nodes (4): DashboardData, JalaliDate, SiteController, LoginForm

### Community 7 - "Clinical Models and Relations"
Cohesion: 0.09
Nodes (4): WardController, AdmissionService, Prescription, Ward

### Community 9 - "Composer Dependencies"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 12 - "Jalali Calendar Interaction"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 13 - "Final Database Schema"
Cohesion: 0.33
Nodes (12): admission_services, admissions, discharges, doctors, medical_items, patients, prescription_items, prescriptions (+4 more)

### Community 21 - "Patient Autocomplete"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 24 - "Live Dashboard Refresh"
Cohesion: 0.70
Nodes (4): refresh(), resume(), schedule(), showFailure()

### Community 25 - "Debug Toolbar Configuration"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 29 - "Historical Date Input Explanation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **32 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+27 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 254 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **29 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Access` connect `Clinical Access and Corrections` to `Request Routing and Control`, `Admission Billing and Receipts`, `Record Lists and Tables`, `Admission Actions and Approval`, `Clinical Forms and Selection`, `Cost Summaries and Snapshots`, `Shared Navigation`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `Access` (e.g. with `.load()` and `.query()`) actually correct?**
  _`Access` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _32 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Request Routing and Control` be split into smaller, more focused modules?**
  _Cohesion score 0.0653061224489796 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Staff Accounts and Profiles` to `Request Routing and Control`, `Admission Billing and Receipts`, `Role Dashboards and Login`, `Clinical Models and Relations`, `Clinical Forms and Selection`, `Staff Account List`?**
  _High betweenness centrality (0.077) - this node is a cross-community bridge._
- **Should `Hospital Workflow Documentation` be split into smaller, more focused modules?**
  _Cohesion score 0.05551020408163265 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission Billing and Receipts` to `Request Routing and Control`, `Staff Accounts and Profiles`, `Clinical Access and Corrections`, `Clinical Models and Relations`, `Admission Actions and Approval`?**
  _High betweenness centrality (0.056) - this node is a cross-community bridge._

## Refresh validation

- Updated current code, final database schema and current documentation. Historical review notes are labeled as historical.
- Removed sources for 13 deleted files and 7 unused external-reference nodes.
- No missing source files or dangling graph endpoints.
- Corrected six name-based false self-calls using verified receiver types and installed Yii declarations.
- One intentional calendar self-edge remains: event handlers inside `render()` call `render()` again (`app/web/js/shamsi.js:126`). This is documented behavior, not immediate infinite recursion.
