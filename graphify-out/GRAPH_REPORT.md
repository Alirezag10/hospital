# Graph Report - hospital  (2026-10-29)

## Corpus Check
- 113 files · ~98,415 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 7 file(s) not represented in the graph (top: .css 3, (none) 2, .woff2 1)

## Summary
- 514 nodes · 888 edges · 38 communities (16 shown, 22 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 32 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `60766ad9`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- FormInput
- Service
- User
- Patient
- Prescription
- Admission
- composer.json
- Access
- Hospital product scope
- SiteController
- shamsi.js
- install.sql
- Billing
- PrescriptionItem
- admission/_form.php
- Persian Date Formatting
- Assets and Bootstrap Integration
- Patient Autocomplete
- live-panel.js
- Yii2 Debug Toolbar Configuration
- PersianSerialColumn
- clinical.js
- Historical Gregorian Date Input Explanation
- close
- medical-search.js
- Yii Software License
- Font Redistribution Conditions

## God Nodes (most connected - your core abstractions)
1. `Access` - 36 edges
2. `Admission` - 35 edges
3. `User` - 30 edges
4. `FormInput` - 28 edges
5. `RoleController` - 27 edges
6. `Patient` - 26 edges
7. `Discharge` - 23 edges
8. `Doctor` - 20 edges
9. `Prescription` - 17 edges
10. `PrescriptionItem` - 17 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `Access`  [INFERRED]
  app/components/RoleController.php → app/components/Access.php
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php
- `Historical proposal: management reporting, ward/date filters and CSV` --semantically_similar_to--> `Historical proposal: management reports and CSV`  [INFERRED] [semantically similar]
  graphify-out/memory/query_20261021_104838_bda6d418_ایده_بده_برای_بهبود_پروژه.md → graphify-out/memory/query_20261021_112042_8ceea6c7_پروژه_رو_برسی_کن_و_ایده_برای_بهبود_بده.md
- `Admin manages staff and catalogs and views records` --semantically_similar_to--> `Admin record oversight without clinical write access`  [INFERRED] [semantically similar]
  PRODUCT.md → README.md
- `Discharge worker views ready or discharged cases` --semantically_similar_to--> `Ready and discharged admission visibility`  [INFERRED] [semantically similar]
  PRODUCT.md → README.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Role-specific hospital workflow** — product_admin_oversight, product_secretary_intake, product_doctor_treatment, product_discharge_scope [EXTRACTED 1.00]

## Communities (38 total, 22 thin omitted)

### Community 0 - "FormInput"
Cohesion: 0.09
Nodes (5): FormInput, RoleController, DoctorController, RecordChangeController, RecordChange

### Community 1 - "Service"
Cohesion: 0.11
Nodes (5): AdmissionServiceController, ServiceController, AdmissionService, CorrectionForm, Service

### Community 2 - "User"
Cohesion: 0.06
Nodes (7): UserController, WardController, Doctor, User, {closure#1}(), UserForm, Ward

### Community 3 - "Patient"
Cohesion: 0.06
Nodes (6): AdmissionController, PatientController, AdmissionSearch, LoginForm, Patient, PatientSearch

### Community 4 - "Prescription"
Cohesion: 0.12
Nodes (4): ClinicalController, MedicalItemController, MedicalItem, Prescription

### Community 6 - "Admission"
Cohesion: 0.07
Nodes (6): AdmissionData, AdmissionRegistration, ReceiptSnapshot, DischargeController, Admission, Discharge

### Community 7 - "composer.json"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 8 - "Access"
Cohesion: 0.05
Nodes (3): Access, {closure#1}(), {closure#2}()

### Community 10 - "Hospital product scope"
Cohesion: 0.12
Nodes (15): Admin manages staff and catalogs and views records, Doctor approval gates discharge, Reasoned corrections and edit conflict detection, Discharge worker views ready or discharged cases, Assigned doctor performs treatment and print, Hospital product scope, Secretary registers patient and admission, Admin record oversight without clinical write access (+7 more)

### Community 11 - "SiteController"
Cohesion: 0.12
Nodes (3): DashboardData, JalaliDate, SiteController

### Community 12 - "shamsi.js"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 14 - "install.sql"
Cohesion: 0.33
Nodes (12): admission_services, admissions, discharges, doctors, medical_items, patients, prescription_items, prescriptions (+4 more)

### Community 21 - "Patient Autocomplete"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 24 - "live-panel.js"
Cohesion: 0.70
Nodes (4): refresh(), resume(), schedule(), showFailure()

### Community 25 - "Yii2 Debug Toolbar Configuration"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 29 - "Historical Gregorian Date Input Explanation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **29 isolated node(s):** `name`, `description`, `type`, `license`, `php` (+24 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 238 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **22 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Access` connect `Access` to `FormInput`, `Service`, `Patient`, `Prescription`, `Admission`, `SiteController`, `Billing`, `admission/_form.php`?**
  _High betweenness centrality (0.100) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Access` (e.g. with `.load()` and `{closure#1}()`) actually correct?**
  _`Access` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _29 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `FormInput` be split into smaller, more focused modules?**
  _Cohesion score 0.09302325581395349 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User` to `FormInput`, `Patient`, `Access`, `SiteController`, `yii\widgets\ActiveForm`, `admission/_form.php`?**
  _High betweenness centrality (0.087) - this node is a cross-community bridge._
- **Should `Service` be split into smaller, more focused modules?**
  _Cohesion score 0.10582010582010581 - nodes in this community are weakly interconnected._
- **Why does `RoleController` connect `FormInput` to `Service`, `User`, `Patient`, `Prescription`, `Admission`, `Access`, `SiteController`?**
  _High betweenness centrality (0.056) - this node is a cross-community bridge._