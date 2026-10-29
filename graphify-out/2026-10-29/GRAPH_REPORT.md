# Graph Report - hospital  (2026-10-23)

## Corpus Check
- Corpus is ~27,790 words - fits in a single context window. You may not need a graph.

## Summary
- 519 nodes · 839 edges · 43 communities (15 shown, 28 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 60 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Clinical Access and Routing
- Clinical Access and Routing
- Staff Accounts and Profiles
- Patient Validation and Search
- Clinical Models and Relations
- Admission Billing and Receipts
- Composer Dependencies
- Record Lists and Tables
- Hospital Workflow Documentation
- Role Dashboards and Login
- Jalali Date Input
- Active Forms and Fields
- Final Database Schema
- Admission Actions and Approval
- Cost Summaries and Snapshots
- Prescription Items and Catalog
- Persian Date Formatting
- Assets and Bootstrap Integration
- Patient Autocomplete
- Live Dashboard Refresh
- Historical Debug Toolbar Notes
- Persian Row Numbers
- Prescription Row Interaction
- Historical Date Input Explanation
- In-app Confirmation Dialog
- Medical Catalog Search
- Yii License
- Font License

## God Nodes (most connected - your core abstractions)
1. `Access` - 35 edges
2. `Admission` - 26 edges
3. `User` - 23 edges
4. `Doctor` - 17 edges
5. `render()` - 15 edges
6. `Patient` - 14 edges
7. `PrescriptionItem` - 13 edges
8. `UserForm` - 13 edges
9. `Ward` - 12 edges
10. `enhance()` - 11 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references_constant--> `User`  [EXTRACTED]
  app/models/UserForm.php → app/models/User.php
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php
- `{closure#1}()` --calls--> `Access`  [INFERRED]
  app/components/RoleController.php → app/components/Access.php
- `Historical proposal: management reporting, ward/date filters and CSV` --semantically_similar_to--> `Historical proposal: management reports and CSV`  [INFERRED] [semantically similar]
  graphify-out/memory/query_20261021_104838_bda6d418_ایده_بده_برای_بهبود_پروژه.md → graphify-out/memory/query_20261021_112042_8ceea6c7_پروژه_رو_برسی_کن_و_ایده_برای_بهبود_بده.md
- `{closure#2}()` --calls--> `Access`  [EXTRACTED]
  app/views/patient/index.php → app/components/Access.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Role-specific hospital workflow** — product_admin_oversight, product_secretary_intake, product_doctor_treatment, product_discharge_scope [EXTRACTED 1.00]

## Communities (43 total, 28 thin omitted)

### Community 0 - "Clinical Access and Routing"
Cohesion: 0.06
Nodes (5): RoleController, PatientController, AdmissionSearch, LoginForm, {closure#1}()

### Community 1 - "Clinical Access and Routing"
Cohesion: 0.06
Nodes (12): Access, AdmissionData, Billing, {closure#1}(), AdmissionServiceController, ClinicalController, DischargeController, RecordChangeController (+4 more)

### Community 2 - "Staff Accounts and Profiles"
Cohesion: 0.06
Nodes (8): FormInput, DoctorController, ServiceController, UserController, Doctor, User, UserForm, yii\db\BaseActiveRecord::save()

### Community 3 - "Patient Validation and Search"
Cohesion: 0.08
Nodes (4): MedicalItemController, Discharge, Patient, PatientSearch

### Community 4 - "Clinical Models and Relations"
Cohesion: 0.09
Nodes (4): WardController, AdmissionService, Prescription, Ward

### Community 6 - "Admission Billing and Receipts"
Cohesion: 0.11
Nodes (3): AdmissionRegistration, ReceiptSnapshot, Admission

### Community 7 - "Composer Dependencies"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 10 - "Hospital Workflow Documentation"
Cohesion: 0.12
Nodes (15): Admin manages staff and catalogs and views records, Doctor approval gates discharge, Reasoned corrections and edit conflict detection, Discharge worker views ready or discharged cases, Assigned doctor performs treatment and print, Hospital product scope, Secretary registers patient and admission, Admin record oversight without clinical write access (+7 more)

### Community 11 - "Role Dashboards and Login"
Cohesion: 0.12
Nodes (3): DashboardData, JalaliDate, SiteController

### Community 12 - "Jalali Date Input"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 14 - "Final Database Schema"
Cohesion: 0.33
Nodes (12): admission_services, admissions, discharges, doctors, medical_items, patients, prescription_items, prescriptions (+4 more)

### Community 21 - "Patient Autocomplete"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 24 - "Live Dashboard Refresh"
Cohesion: 0.70
Nodes (4): refresh(), resume(), schedule(), showFailure()

### Community 25 - "Historical Debug Toolbar Notes"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 29 - "Historical Date Input Explanation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **29 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+24 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 242 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **28 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Access` connect `Clinical Access and Routing` to `Clinical Access and Routing`, `Record Lists and Tables`, `Role Dashboards and Login`, `Admission Actions and Approval`, `Cost Summaries and Snapshots`, `Clinical and Staff Forms`, `Admission Record List`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Access` (e.g. with `.load()` and `{closure#1}()`) actually correct?**
  _`Access` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _29 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Clinical Access and Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.061367621274108705 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Staff Accounts and Profiles` to `Clinical Access and Routing`, `Record Lists and Tables`, `Clinical and Staff Forms`, `Clinical Models and Relations`?**
  _High betweenness centrality (0.070) - this node is a cross-community bridge._
- **Should `Clinical Access and Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.06390977443609022 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission Billing and Receipts` to `Clinical Access and Routing`, `Clinical Access and Routing`, `Staff Accounts and Profiles`, `Clinical Models and Relations`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
## Extraction Accounting

Code was extracted locally and changed documents were interpreted by the host agent. Agent token usage was not exposed; the zero token figures above are placeholders, not measured usage. No external model API was called.
