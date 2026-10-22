# Graph Report - hospital  (2026-10-22)

## Corpus Check
- Corpus is ~29,010 words - fits in a single context window. You may not need a graph.

## Summary
- 531 nodes · 846 edges · 45 communities (14 shown, 31 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 61 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Clinical Access and Routing
- Clinical Access and Routing
- Staff Accounts and Profiles
- Patient Validation and Search
- Hospital Workflow Documentation
- Clinical Models and Relations
- Composer Dependencies
- Role Dashboards and Login
- Admission Billing and Receipts
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
- Community 43

## God Nodes (most connected - your core abstractions)
1. `Access` - 28 edges
2. `Admission` - 26 edges
3. `User` - 23 edges
4. `Doctor` - 17 edges
5. `render()` - 15 edges
6. `Patient` - 14 edges
7. `UserForm` - 13 edges
8. `PrescriptionItem` - 13 edges
9. `Ward` - 12 edges
10. `AdmissionController` - 11 edges

## Surprising Connections (you probably didn't know these)
- `Role permission matrix` --semantically_similar_to--> `Four hospital roles and route access`  [INFERRED] [semantically similar]
  docs/staff-guide.md → README.md
- `First administrator must be created separately` --semantically_similar_to--> `Manual first-administrator creation`  [INFERRED] [semantically similar]
  docs/staff-guide.md → README.md
- `{closure#1}()` --calls--> `Access`  [INFERRED]
  app/components/RoleController.php → app/components/Access.php
- `{closure#1}()` --references--> `Ward`  [EXTRACTED]
  app/views/admission/_form.php → app/models/Ward.php
- `Historical proposal: management reporting, ward/date filters and CSV` --semantically_similar_to--> `Historical proposal: management reports and CSV`  [INFERRED] [semantically similar]
  graphify-out/memory/query_20261021_104838_bda6d418_ایده_بده_برای_بهبود_پروژه.md → graphify-out/memory/query_20261021_112042_8ceea6c7_پروژه_رو_برسی_کن_و_ایده_برای_بهبود_بده.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Treatment approval, historic costs and financial receipt** — readme_treatment_approval, readme_historical_prices, readme_financial_receipt, readme_transaction_locks [EXTRACTED 1.00]
- **Role access, assigned doctor cases and account administration** — docs_staff_guide_role_matrix, docs_staff_guide_server_authorization, docs_staff_guide_user_management, readme_doctor_scope [INFERRED 0.85]

## Communities (45 total, 31 thin omitted)

### Community 0 - "Clinical Access and Routing"
Cohesion: 0.07
Nodes (10): Access, AdmissionData, Billing, {closure#1}(), AdmissionServiceController, ClinicalController, RecordChangeController, MedicalItem (+2 more)

### Community 1 - "Clinical Access and Routing"
Cohesion: 0.08
Nodes (4): ReceiptSnapshot, RoleController, DischargeController, PatientController

### Community 2 - "Staff Accounts and Profiles"
Cohesion: 0.06
Nodes (9): FormInput, DoctorController, ServiceController, UserController, Doctor, User, {closure#1}(), UserForm (+1 more)

### Community 3 - "Patient Validation and Search"
Cohesion: 0.05
Nodes (7): MedicalItemController, AdmissionSearch, CorrectionForm, Discharge, LoginForm, Patient, PatientSearch

### Community 4 - "Hospital Workflow Documentation"
Cohesion: 0.08
Nodes (18): Staff access and operations guide, Thirty-second background dashboard refresh, First administrator must be created separately, Role permission matrix, Administrator staff account management, Automatic dashboard refresh, Hospital management system, Local Persian desktop interface (+10 more)

### Community 5 - "Clinical Models and Relations"
Cohesion: 0.09
Nodes (4): WardController, AdmissionService, Prescription, Ward

### Community 7 - "Composer Dependencies"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 10 - "Role Dashboards and Login"
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
- **28 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+23 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 246 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **31 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Staff Accounts and Profiles` to `Clinical Access and Routing`, `Patient Validation and Search`, `Clinical Models and Relations`, `Record Lists and Tables`, `Clinical and Staff Forms`?**
  _High betweenness centrality (0.069) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Access` (e.g. with `.load()` and `{closure#1}()`) actually correct?**
  _`Access` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _28 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Clinical Access and Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.07088989441930618 - nodes in this community are weakly interconnected._
- **Why does `Access` connect `Clinical Access and Routing` to `Clinical Access and Routing`, `Role Dashboards and Login`, `Admission Actions and Approval`, `Cost Summaries and Snapshots`, `Clinical and Staff Forms`, `Admission Record List`?**
  _High betweenness centrality (0.068) - this node is a cross-community bridge._
- **Should `Clinical Access and Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.08 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission Billing and Receipts` to `Clinical Access and Routing`, `Clinical Access and Routing`, `Staff Accounts and Profiles`, `Clinical Models and Relations`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._