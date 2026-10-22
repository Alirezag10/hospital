# Graph Report - hospital  (2026-10-22)

## Corpus Check
- 116 files · ~29,172 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 538 nodes · 879 edges · 46 communities (13 shown, 33 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 66 edges (avg confidence: 0.87)
- Agent token usage: not exposed by this environment; no external LLM API was called.
- Graph health: 0 missing endpoints, 0 dangling edges; 1 intentional `shamsi.js` render self-loop for date-picker controls.

## Community Hubs (Navigation)
- Clinical Access and Routing
- Staff Accounts and Profiles
- Patient Validation and Search
- Hospital Workflow Documentation
- Clinical Models and Relations
- Composer Dependencies
- Role Dashboards and Login
- Jalali Date Input
- Admission Billing and Receipts
- Active Forms and Fields
- Final Database Schema
- Admission Actions and Approval
- Medical Item Management
- Cost Summaries and Snapshots
- Prescription Items and Catalog
- Persian Date Formatting
- Assets and Bootstrap Integration
- Patient Autocomplete
- Patient Controller Actions
- Service Controller Actions
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
1. `Access` - 31 edges
2. `Admission` - 26 edges
3. `User` - 23 edges
4. `Doctor` - 17 edges
5. `Hospital system overview` - 16 edges
6. `render()` - 15 edges
7. `Patient` - 14 edges
8. `PrescriptionItem` - 13 edges
9. `UserForm` - 13 edges
10. `Ward` - 12 edges

## Surprising Connections (you probably didn't know these)
- `Role based server access` --semantically_similar_to--> `Doctor admission scope`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Automatic dashboard refresh` --semantically_similar_to--> `Live dashboard updates`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Medical item and service catalogs` --semantically_similar_to--> `Medical item catalog and tariffs`  [INFERRED] [semantically similar]
  PRODUCT.md → docs/staff-guide.md
- `Discharge workflow` --semantically_similar_to--> `Financial discharge receipt`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md
- `Record change history` --semantically_similar_to--> `Treatment edits and audit`  [INFERRED] [semantically similar]
  README.md → docs/staff-guide.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Role based hospital workflow** — readme_admission_workflow, readme_treatment_workflow, readme_discharge_workflow, docs_staff_guide_staff_permissions [EXTRACTED 1.00]
- **Financial integrity across treatment and discharge** — readme_price_snapshot, readme_concurrent_transactions, readme_receipt_data, docs_staff_guide_discharge_receipt [INFERRED 0.85]

## Communities (46 total, 33 thin omitted)

### Community 0 - "Clinical Access and Routing"
Cohesion: 0.05
Nodes (12): Access, AdmissionData, Billing, ReceiptSnapshot, {closure#1}(), RoleController, AdmissionServiceController, ClinicalController (+4 more)

### Community 1 - "Staff Accounts and Profiles"
Cohesion: 0.06
Nodes (9): AdmissionRegistration, FormInput, DoctorController, UserController, Doctor, User, {closure#1}(), UserForm (+1 more)

### Community 2 - "Patient Validation and Search"
Cohesion: 0.06
Nodes (6): AdmissionSearch, CorrectionForm, Discharge, LoginForm, Patient, PatientSearch

### Community 3 - "Hospital Workflow Documentation"
Cohesion: 0.10
Nodes (25): Account lifecycle and session invalidation, Active admission rule, Live dashboard updates, Financial discharge receipt, Doctor profile and user account, Medical item catalog and tariffs, Staff guide, Staff permissions matrix (+17 more)

### Community 4 - "Clinical Models and Relations"
Cohesion: 0.09
Nodes (4): WardController, AdmissionService, Prescription, Ward

### Community 6 - "Composer Dependencies"
Cohesion: 0.09
Nodes (21): yiisoft/yii2-composer, autoload, psr-4, config, allow-plugins, fxp-asset, process-timeout, description (+13 more)

### Community 7 - "Role Dashboards and Login"
Cohesion: 0.10
Nodes (3): DashboardData, JalaliDate, SiteController

### Community 10 - "Jalali Date Input"
Cohesion: 0.34
Nodes (16): bounds(), button(), choose(), close(), display(), enhance(), gregorian(), iconButton() (+8 more)

### Community 13 - "Final Database Schema"
Cohesion: 0.33
Nodes (12): admission_services, admissions, discharges, doctors, medical_items, patients, prescription_items, prescriptions (+4 more)

### Community 21 - "Patient Autocomplete"
Cohesion: 0.53
Nodes (5): enhance(), clearResults(), renderResults(), updateClearButton(), init()

### Community 26 - "Live Dashboard Refresh"
Cohesion: 0.70
Nodes (4): refresh(), resume(), schedule(), showFailure()

### Community 27 - "Historical Debug Toolbar Notes"
Cohesion: 0.40
Nodes (4): Debug Toolbar Allowed IPs, Development Environment Flags, How to Enable Yii2 Debug Toolbar on Every Page, Yii2 Debug Toolbar Configuration

### Community 31 - "Historical Date Input Explanation"
Cohesion: 0.67
Nodes (3): Patient Form Native Date Input, Patient Model Gregorian Date Validation, Why Patient Birth Date Used Gregorian Input

## Knowledge Gaps
- **24 isolated node(s):** `yiisoft/yii2-composer`, `process-timeout`, `description`, `enabled`, `license` (+19 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 236 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **33 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Access` connect `Clinical Access and Routing` to `Shared Navigation`, `Role Dashboards and Login`, `Admission Actions and Approval`, `Cost Summaries and Snapshots`, `Clinical and Staff Forms`, `Admission Record List`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Access` (e.g. with `.load()` and `{closure#1}()`) actually correct?**
  _`Access` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `yiisoft/yii2-composer`, `process-timeout`, `description` to the rest of the system?**
  _24 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Clinical Access and Routing` be split into smaller, more focused modules?**
  _Cohesion score 0.05239240844693932 - nodes in this community are weakly interconnected._
- **Why does `User` connect `Staff Accounts and Profiles` to `Clinical Access and Routing`, `Patient Validation and Search`, `Clinical Models and Relations`, `Record Lists and Tables`, `Clinical and Staff Forms`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Should `Staff Accounts and Profiles` be split into smaller, more focused modules?**
  _Cohesion score 0.06086956521739131 - nodes in this community are weakly interconnected._
- **Why does `Admission` connect `Admission Billing and Receipts` to `Clinical Access and Routing`, `Staff Accounts and Profiles`, `Clinical Models and Relations`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._